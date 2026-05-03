<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\TherapistPayroll;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = TherapistPayroll::query()->with('therapist');

        if ($month = $request->input('month')) {
            $start = Carbon::createFromFormat('Y-m', (string) $month)->startOfMonth()->toDateString();
            $query->whereDate('month', $start);
        }

        if ($therapistId = $request->input('therapist_id')) {
            $query->where('therapist_id', $therapistId);
        }

        $query->orderByDesc('month')->orderBy('therapist_id');

        return ApiResponse::success($query->get(), 'OK');
    }

    public function generate(Request $request, PayrollService $service)
    {
        try {
            $this->validate($request, [
                'month' => ['required', 'date_format:Y-m'],
            ]);

            $rows = $service->generateForMonth((string) $request->input('month'));

            return ApiResponse::success($rows, 'Payroll generated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function update(Request $request, $id)
    {
        $row = TherapistPayroll::find($id);
        if (! $row) {
            return ApiResponse::error('Payroll not found', 404);
        }

        try {
            $this->validate($request, [
                'present_days' => ['sometimes', 'required', 'integer', 'min:0'],
                'leave_days' => ['sometimes', 'required', 'integer', 'min:0'],
                'holiday_days' => ['sometimes', 'required', 'integer', 'min:0'],
                'absent_days' => ['sometimes', 'required', 'integer', 'min:0'],
                'total_sessions' => ['sometimes', 'required', 'integer', 'min:0'],
                'overtime_sessions' => ['sometimes', 'required', 'integer', 'min:0'],
                'salary_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
                'deduction_amount' => ['sometimes', 'required', 'numeric', 'min:0'],
                'net_salary' => ['sometimes', 'required', 'numeric', 'min:0'],
            ]);

            $row->fill($request->only([
                'present_days',
                'leave_days',
                'holiday_days',
                'absent_days',
                'total_sessions',
                'overtime_sessions',
                'salary_amount',
                'deduction_amount',
                'net_salary',
            ]));
            $row->save();
            $row->load('therapist');

            return ApiResponse::success($row, 'Payroll updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function pay($id)
    {
        $row = TherapistPayroll::find($id);
        if (! $row) {
            return ApiResponse::error('Payroll not found', 404);
        }

        $row->paid_at = Carbon::now();
        $row->save();
        $row->load('therapist');

        return ApiResponse::success($row, 'Marked as paid');
    }
}

