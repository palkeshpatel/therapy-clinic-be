<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::query()->with('invoice');

        if ($invoiceId = $request->input('invoice_id')) {
            $query->where('invoice_id', $invoiceId);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('payment_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('payment_date', '<=', $to);
        }

        $query->orderByDesc('payment_date')->orderByDesc('id');

        return ApiResponse::success($query->get(), 'OK');
    }

    public function store(Request $request, $invoiceId, InvoiceService $service)
    {
        $invoice = Invoice::find($invoiceId);
        if (! $invoice) {
            return ApiResponse::error('Invoice not found', 404);
        }

        try {
            $this->validate($request, [
                'amount' => ['required', 'numeric', 'min:0.01'],
                'payment_method' => ['required', Rule::in(['cash', 'upi', 'card', 'bank_transfer'])],
                'payment_date' => ['required', 'date'],
                'reference_no' => ['nullable', 'string', 'max:100'],
            ]);

            $payment = $service->addPayment($invoice, $request->all());

            return ApiResponse::success($payment, 'Payment recorded', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $payment = Payment::find($id);
        if (! $payment) {
            return ApiResponse::error('Payment not found', 404);
        }

        // For now, delete payment (void). Later we can implement adjustments.
        $payment->delete();

        return ApiResponse::success(null, 'Payment deleted');
    }
}

