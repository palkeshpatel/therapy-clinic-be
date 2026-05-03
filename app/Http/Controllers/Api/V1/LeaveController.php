<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Therapist;
use App\Models\TherapistLeave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = TherapistLeave::query()->with('therapist');
        $myTherapistId = $this->myTherapistIdIfTherapist();
        if ($myTherapistId) {
            $query->where('therapist_id', $myTherapistId);
        }

        if ($month = $request->input('month')) {
            $start = \Illuminate\Support\Carbon::createFromFormat('Y-m', (string) $month)->startOfMonth()->toDateString();
            $end = \Illuminate\Support\Carbon::createFromFormat('Y-m', (string) $month)->endOfMonth()->toDateString();
            $query->whereBetween('leave_date', [$start, $end]);
        }

        if (! $myTherapistId && ($therapistId = $request->input('therapist_id'))) {
            $query->where('therapist_id', $therapistId);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('leave_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('leave_date', '<=', $to);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $query->orderByDesc('leave_date')->orderByDesc('id');

        return ApiResponse::paginate($query->paginate($perPage), 'OK');
    }

    public function store(Request $request)
    {
        try {
            $myTherapistId = $this->myTherapistIdIfTherapist();
            if ($myTherapistId) {
                $this->validate($request, [
                    'leave_date' => ['required', 'date'],
                    'leave_type' => ['required', 'string', 'max:50'],
                    'reason' => ['nullable', 'string'],
                ]);
            } else {
                $this->validate($request, [
                    'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
                    'leave_date' => ['required', 'date'],
                    'leave_type' => ['required', 'string', 'max:50'],
                    'reason' => ['nullable', 'string'],
                ]);
            }

            $therapistId = $myTherapistId ?: (int) $request->input('therapist_id');

            $leave = TherapistLeave::create([
                'therapist_id' => $therapistId,
                'leave_date' => $request->input('leave_date'),
                'leave_type' => (string) $request->input('leave_type'),
                'reason' => $request->input('reason'),
                'status' => 'pending',
            ]);
            $leave->load('therapist');

            return ApiResponse::success($leave, 'Leave applied', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function update(Request $request, $id)
    {
        $leave = TherapistLeave::find($id);
        if (! $leave) {
            return ApiResponse::error('Leave not found', 404);
        }

        try {
            $this->validate($request, [
                'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            ]);

            $leave->status = (string) $request->input('status');
            $leave->save();
            $leave->load('therapist');

            return ApiResponse::success($leave, 'Leave updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $leave = TherapistLeave::find($id);
        if (! $leave) {
            return ApiResponse::error('Leave not found', 404);
        }

        $myTherapistId = $this->myTherapistIdIfTherapist();
        if ($myTherapistId) {
            if ((int) $leave->therapist_id !== (int) $myTherapistId) {
                return ApiResponse::error('Forbidden', 403);
            }
            if ((string) $leave->status !== 'pending') {
                return ApiResponse::error('Only pending leave can be cancelled', 422);
            }
        }

        $leave->delete();
        return ApiResponse::success(null, 'Leave deleted');
    }

    private function myTherapistIdIfTherapist(): ?int
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }
        $user->loadMissing('role');
        if (($user->role?->role_type ?? null) !== 'therapist') {
            return null;
        }

        return Therapist::query()->where('user_id', $user->id)->value('id');
    }
}

