<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\TherapySession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReportController extends Controller
{
    public function revenue(Request $request)
    {
        try {
            $this->validate($request, [
                'from' => ['required', 'date'],
                'to' => ['required', 'date'],
                'group_by' => ['nullable', Rule::in(['day', 'month'])],
            ]);

            $from = (string) $request->input('from');
            $to = (string) $request->input('to');
            $groupBy = (string) ($request->input('group_by') ?? 'day');

            $format = $groupBy === 'month' ? '%Y-%m' : '%Y-%m-%d';

            $rows = Invoice::query()
                ->selectRaw("DATE_FORMAT(invoice_date, '{$format}') as period, SUM(paid_amount) as revenue")
                ->whereBetween('invoice_date', [$from, $to])
                ->groupBy('period')
                ->orderBy('period')
                ->get();

            return ApiResponse::success($rows, 'OK');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function sessions(Request $request)
    {
        try {
            $this->validate($request, [
                'from' => ['required', 'date'],
                'to' => ['required', 'date'],
            ]);

            $from = (string) $request->input('from');
            $to = (string) $request->input('to');

            $rows = TherapySession::query()
                ->with(['patient', 'therapist', 'therapy'])
                ->whereBetween('session_date', [$from, $to])
                ->orderByDesc('session_date')
                ->get();

            return ApiResponse::success($rows, 'OK');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function outstandingInvoices()
    {
        $rows = Invoice::query()
            ->with('patient')
            ->whereIn('status', ['pending', 'partial'])
            ->orderByDesc('invoice_date')
            ->get();

        return ApiResponse::success($rows, 'OK');
    }

    public function therapistPerformance(Request $request)
    {
        try {
            $this->validate($request, [
                'from' => ['required', 'date'],
                'to' => ['required', 'date'],
            ]);

            $from = (string) $request->input('from');
            $to = (string) $request->input('to');

            $rows = TherapySession::query()
                ->selectRaw('therapist_id, COUNT(*) as total_sessions')
                ->whereBetween('session_date', [$from, $to])
                ->groupBy('therapist_id')
                ->orderByDesc('total_sessions')
                ->with('therapist')
                ->get();

            return ApiResponse::success($rows, 'OK');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }
}

