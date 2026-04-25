<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\TherapySession;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        $today = Carbon::today()->toDateString();

        $patients = Patient::query()->count();
        $todaySessions = TherapySession::query()->whereDate('session_date', $today)->count();
        $pendingInvoices = Invoice::query()->whereIn('status', ['pending', 'partial'])->count();

        // Revenue: sum of payments for current month
        $monthStart = Carbon::today()->startOfMonth()->toDateString();
        $monthEnd = Carbon::today()->endOfMonth()->toDateString();
        $revenue = Invoice::query()
            ->whereBetween('invoice_date', [$monthStart, $monthEnd])
            ->sum('paid_amount');

        return ApiResponse::success([
            'patients' => $patients,
            'today_sessions' => $todaySessions,
            'pending_invoices' => $pendingInvoices,
            'month_revenue' => (string) $revenue,
        ], 'OK');
    }
}

