<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\TherapistAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = TherapistAttendance::query()->with('therapist');

        if ($therapistId = $request->input('therapist_id')) {
            $query->where('therapist_id', $therapistId);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('date', '<=', $to);
        }

        $query->orderByDesc('date')->orderByDesc('id');

        return ApiResponse::success($query->get(), 'OK');
    }

    public function today()
    {
        $today = Carbon::today()->toDateString();

        $rows = TherapistAttendance::query()
            ->with('therapist')
            ->whereDate('date', $today)
            ->orderBy('therapist_id')
            ->get();

        return ApiResponse::success($rows, 'OK');
    }

    public function checkIn(Request $request)
    {
        try {
            $this->validate($request, [
                'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
            ]);

            $today = Carbon::today()->toDateString();

            $row = TherapistAttendance::query()->firstOrNew([
                'therapist_id' => (int) $request->input('therapist_id'),
                'date' => $today,
            ]);

            if (! $row->check_in) {
                $row->check_in = Carbon::now();
            }
            $row->save();
            $row->load('therapist');

            return ApiResponse::success($row, 'Checked in');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function checkOut(Request $request)
    {
        try {
            $this->validate($request, [
                'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
            ]);

            $today = Carbon::today()->toDateString();

            $row = TherapistAttendance::query()->firstOrNew([
                'therapist_id' => (int) $request->input('therapist_id'),
                'date' => $today,
            ]);

            if (! $row->check_in) {
                $row->check_in = Carbon::now();
            }
            $row->check_out = Carbon::now();
            $row->save();
            $row->load('therapist');

            return ApiResponse::success($row, 'Checked out');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }
}

