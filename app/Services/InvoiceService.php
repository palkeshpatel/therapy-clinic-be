<?php

namespace App\Services;

use App\Helpers\Money;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public function create(array $payload): Invoice
    {
        return DB::transaction(function () use ($payload) {
            $invoice = new Invoice();
            $invoice->invoice_no = $this->generateInvoiceNo();
            $invoice->patient_id = (int) $payload['patient_id'];
            $invoice->invoice_date = $payload['invoice_date'];
            $invoice->due_date = $payload['due_date'] ?? null;
            $invoice->notes = $payload['notes'] ?? null;
            $invoice->status = 'pending';
            $invoice->total_amount = '0.00';
            $invoice->paid_amount = '0.00';
            $invoice->save();

            $total = '0.00';
            foreach (($payload['items'] ?? []) as $item) {
                $qty = (int) ($item['quantity'] ?? 1);
                $amount = (string) ($item['amount'] ?? '0.00');
                $lineTotal = Money::mul($amount, $qty);
                $total = Money::add($total, $lineTotal);

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'therapy_id' => $item['therapy_id'] ?? null,
                    'description' => (string) $item['description'],
                    'quantity' => $qty,
                    'amount' => $amount,
                ]);
            }

            $invoice->total_amount = $total;
            $invoice->save();

            return $invoice->fresh(['patient', 'items', 'payments']);
        });
    }

    public function addPayment(Invoice $invoice, array $payload): Payment
    {
        return DB::transaction(function () use ($invoice, $payload) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'amount' => (string) $payload['amount'],
                'payment_method' => (string) $payload['payment_method'],
                'payment_date' => $payload['payment_date'],
                'reference_no' => $payload['reference_no'] ?? null,
            ]);

            $paid = Money::add((string) $invoice->paid_amount, (string) $payment->amount);
            $invoice->paid_amount = $paid;

            $total = (string) $invoice->total_amount;
            if (Money::cmp($paid, $total) >= 0) {
                $invoice->status = 'paid';
            } elseif (Money::cmp($paid, '0.00') > 0) {
                $invoice->status = 'partial';
            } else {
                $invoice->status = 'pending';
            }

            $invoice->save();

            return $payment;
        });
    }

    private function generateInvoiceNo(): string
    {
        // Simple sequential-ish number: INV-YYYYMMDD-<lastId+1>
        $date = Carbon::now()->format('Ymd');
        $next = (int) (DB::table('invoices')->max('id') ?? 0) + 1;
        return sprintf('INV-%s-%05d', $date, $next);
    }
}

