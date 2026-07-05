<?php

namespace App\Services;

use App\Models\Invoice;

class InvoicePdfService
{
    private Invoice $invoice;
    private string $logoData = '';

    public function __construct(Invoice $invoice, string $logoData = '')
    {
        $this->invoice = $invoice;
        $this->logoData = $logoData;
    }

    private function e(mixed $val, string $fallback = 'N/A'): string
    {
        if ($val === null || $val === '') return htmlspecialchars($fallback, ENT_QUOTES, 'UTF-8');
        return htmlspecialchars((string) $val, ENT_QUOTES, 'UTF-8');
    }

    private function formatCurrency(mixed $val): string
    {
        return 'Rs. ' . number_format((float) ($val ?? 0), 2);
    }

    public function buildHtml(): string
    {
        $patient = $this->invoice->patient;
        $items = $this->invoice->items ?? [];
        $dueAmount = max(0, (float) $this->invoice->total_amount - (float) $this->invoice->paid_amount);

        // Logo HTML
        $logoHtml = '';
        if ($this->logoData) {
            $logoHtml = '<img src="' . $this->logoData . '" style="max-height: 80px;" />';
        } else {
            $logoHtml = '<h2 style="color: #1a3c5e; margin: 0; font-family: sans-serif;">Helping Hands</h2>';
        }

        $itemsHtml = '';
        foreach ($items as $index => $item) {
            $rowBg = ($index % 2 === 0) ? '#ffffff' : '#f8fafc';
            $itemsHtml .= '
            <tr style="background-color: ' . $rowBg . ';">
                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-family: sans-serif; color: #334155;">' . $this->e($item->description) . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-family: sans-serif; color: #334155; text-align: center;">' . $this->e($item->quantity) . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-family: sans-serif; color: #334155; text-align: right;">' . $this->formatCurrency($item->amount) . '</td>
                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-family: sans-serif; color: #0f172a; text-align: right; font-weight: bold;">' . $this->formatCurrency((float) $item->amount * (int) $item->quantity) . '</td>
            </tr>';
        }

        // Totals rows aligned inside the main items table
        $itemsHtml .= '
        <tr>
            <td colspan="2" style="border: none; background: transparent;"></td>
            <td style="padding: 10px; font-size: 13px; color: #475569; border-bottom: 1px solid #e2e8f0; text-align: right; font-family: sans-serif;">Total Billed:</td>
            <td style="padding: 10px; font-size: 13px; color: #0f172a; font-weight: bold; border-bottom: 1px solid #e2e8f0; text-align: right; font-family: sans-serif;">' . $this->formatCurrency($this->invoice->total_amount) . '</td>
        </tr>
        <tr>
            <td colspan="2" style="border: none; background: transparent;"></td>
            <td style="padding: 10px; font-size: 13px; color: #16a34a; border-bottom: 1px solid #e2e8f0; text-align: right; font-family: sans-serif;">Collected:</td>
            <td style="padding: 10px; font-size: 13px; color: #16a34a; font-weight: bold; border-bottom: 1px solid #e2e8f0; text-align: right; font-family: sans-serif;">' . $this->formatCurrency($this->invoice->paid_amount) . '</td>
        </tr>
        <tr style="background-color: #fef3c7;">
            <td colspan="2" style="border: none; background: transparent;"></td>
            <td style="padding: 10px; font-size: 13px; color: #b45309; font-weight: bold; text-align: right; font-family: sans-serif; border-bottom: 1px solid #f59e0b;">Outstanding Due:</td>
            <td style="padding: 10px; font-size: 13px; color: #b45309; font-weight: bold; text-align: right; font-family: sans-serif; border-bottom: 1px solid #f59e0b;">' . $this->formatCurrency($dueAmount) . '</td>
        </tr>';

        $notesHtml = '';
        if ($this->invoice->notes) {
            $notesHtml = '
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; font-size: 12px; line-height: 1.5; color: #475569; font-family: sans-serif; margin-top: 25px;">
                <strong style="color: #334155; display: block; margin-bottom: 5px;">Notes:</strong>
                ' . nl2br($this->e($this->invoice->notes)) . '
            </div>';
        }

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Invoice ' . $this->e($this->invoice->invoice_no) . '</title>
            <style>
                body {
                    font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                    color: #334155;
                    margin: 0;
                    padding: 0;
                }
                .invoice-box {
                    max-width: 800px;
                    margin: auto;
                    padding: 30px;
                    font-size: 16px;
                    line-height: 24px;
                }
            </style>
        </head>
        <body>
            <div class="invoice-box">
                <!-- Header -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                    <tr>
                        <td style="vertical-align: top;">
                            ' . $logoHtml . '
                            <div style="font-size: 11px; color: #64748b; margin-top: 5px; font-family: sans-serif; line-height: 1.4;">
                                <strong>Helping Hands Child Development & Education Center</strong><br/>
                                Clinic Billing & Accounts Dept.
                            </div>
                        </td>
                        <td style="text-align: right; vertical-align: top;">
                            <h1 style="color: #1a3c5e; font-size: 28px; margin: 0; font-family: sans-serif; text-transform: uppercase; letter-spacing: 1px;">Invoice</h1>
                            <div style="font-size: 12px; color: #334155; margin-top: 5px; font-family: sans-serif; line-height: 1.6;">
                                <strong>Invoice No:</strong> ' . $this->e($this->invoice->invoice_no) . '<br/>
                                <strong>Date:</strong> ' . date('d-M-Y', strtotime($this->invoice->invoice_date)) . '<br/>
                                <strong>Status:</strong> <span style="text-transform: uppercase; font-weight: bold; color: ' . ($this->invoice->status === 'paid' ? '#16a34a' : '#d97706') . ';">' . $this->e($this->invoice->status) . '</span>
                            </div>
                        </td>
                    </tr>
                </table>

                <hr style="border: 0; border-top: 1px solid #cbd5e1; margin-bottom: 25px;" />

                <!-- Billing Details -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 40px;">
                    <tr>
                        <td style="width: 50%; vertical-align: top; padding-right: 20px;">
                            <h3 style="color: #1a3c5e; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0; margin-bottom: 10px; font-family: sans-serif;">Bill To:</h3>
                            <div style="font-size: 13px; line-height: 1.5; color: #334155; font-family: sans-serif;">
                                <strong style="font-size: 15px; color: #0f172a;">' . $this->e($patient->patient_name) . '</strong><br/>
                                ' . ($patient->guardian_name ? 'Guardian: ' . $this->e($patient->guardian_name) . '<br/>' : '') . '
                                ' . ($patient->address ? $this->e($patient->address) . '<br/>' : '') . '
                                ' . ($patient->phone ? 'Phone: ' . $this->e($patient->phone) . '<br/>' : '') . '
                                ' . ($patient->email ? 'Email: ' . $this->e($patient->email) : '') . '
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- Items Table -->
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                    <thead>
                        <tr style="background-color: #1a3c5e; color: #ffffff;">
                            <th style="padding: 10px; text-align: left; font-size: 12px; font-family: sans-serif; font-weight: bold; border-top-left-radius: 4px; border-bottom-left-radius: 4px;">Description</th>
                            <th style="padding: 10px; text-align: center; font-size: 12px; font-family: sans-serif; font-weight: bold; width: 80px;">Qty</th>
                            <th style="padding: 10px; text-align: right; font-size: 12px; font-family: sans-serif; font-weight: bold; width: 120px;">Rate</th>
                            <th style="padding: 10px; text-align: right; font-size: 12px; font-family: sans-serif; font-weight: bold; width: 120px; border-top-right-radius: 4px; border-bottom-right-radius: 4px;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $itemsHtml . '
                    </tbody>
                </table>

                <!-- Notes Section -->
                ' . $notesHtml . '

                <!-- Footer / Thank you -->
                <div style="text-align: center; border-top: 1px solid #e2e8f0; padding-top: 20px; margin-top: 30px; font-size: 11px; color: #94a3b8; font-family: sans-serif; line-height: 1.5;">
                    Thank you for choosing Helping Hands. For billing queries, support, or details, please contact us.<br/>
                    <em>This is a system generated document. No signature required.</em>
                </div>
            </div>
        </body>
        </html>';
    }
}
