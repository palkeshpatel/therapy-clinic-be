<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\TherapistLeave;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = TherapistLeave::query()->with('therapist');

        if ($therapistId = $request->input('therapist_id')) {
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
            $this->validate($request, [
                'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
                'leave_date' => ['required', 'date'],
                'leave_type' => ['required', 'string', 'max:50'],
                'reason' => ['nullable', 'string'],
            ]);

            $leave = TherapistLeave::create([
                'therapist_id' => (int) $request->input('therapist_id'),
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

        $leave->delete();
        return ApiResponse::success(null, 'Leave deleted');
    }
}

