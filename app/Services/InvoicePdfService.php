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
        return '&#8377; ' . number_format((float) ($val ?? 0), 2);
    }

    private function numberToWords(float $number): string
    {
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            '0' => '', '1' => 'One', '2' => 'Two',
            '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
            '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
            '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
            '13' => 'Thirteen', '14' => 'Fourteen',
            '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
            '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty',
            '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
            '60' => 'Sixty', '70' => 'Seventy',
            '80' => 'Eighty', '90' => 'Ninety'
        );
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = '';
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number] .
                    " " . $digits[$counter] . $plural . " " . $hundred
                    :
                    $words[floor($number / 10) * 10]
                    . " " . $words[$number % 10] . " "
                    . $digits[$counter] . $plural . " " . $hundred;
            } else $str[] = null;
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($point) ?
            " and " . $words[floor($point / 10) * 10] . " " . 
                $words[$point % 10] . " Paise" : '';
        
        $output = trim($result);
        return $output ? "Rupees " . $output . $points . " Only" : "Zero Rupees Only";
    }

    public function buildHtml(): string
    {
        $patient = $this->invoice->patient;
        $items = $this->invoice->items ?? [];
        $dueAmount = max(0, (float) $this->invoice->total_amount - (float) $this->invoice->paid_amount);

        // Fetch primary payment if exists
        $payment = $this->invoice->payments->first();

        // Detect Billing Month from items description
        $billingMonthLabel = '';
        $billingMonthShort = '';
        foreach ($items as $item) {
            if (preg_match('/\((\d{4}-\d{2})\)/', $item->description, $matches)) {
                $time = strtotime($matches[1] . '-01');
                $billingMonthLabel = date('F Y', $time);
                $billingMonthShort = date('M Y', $time);
                break;
            }
        }
        if (!$billingMonthLabel) {
            $time = strtotime($this->invoice->invoice_date);
            $billingMonthLabel = date('F Y', $time);
            $billingMonthShort = date('M Y', $time);
        }

        // Logo image binary conversion or standard text
        $logoHtml = '';
        if ($this->logoData) {
            $logoHtml = '<img src="' . $this->logoData . '" style="height: 64px; width: 64px;" />';
        } else {
            $logoHtml = '<div style="height: 64px; width: 64px; border-radius: 50%; background-color: #1a3c5e; color: #ffffff; text-align: center; line-height: 64px; font-weight: bold; font-size: 20px;">HH</div>';
        }

        // Status coloring
        $statusColor = '#dc2626'; // pending
        if ($this->invoice->status === 'paid') {
            $statusColor = '#16a34a';
        } elseif ($this->invoice->status === 'partial') {
            $statusColor = '#d97706';
        }

        // Generate Patient ID padded
        $patientIdStr = 'PT-' . str_pad($patient->id, 5, '0', STR_PAD_LEFT);

        // Build items rows
        $itemsHtml = '';
        foreach ($items as $index => $item) {
            $rowBg = ($index % 2 === 0) ? '#ffffff' : '#f8fafc';
            
            // Format description: Split main text and subtitle if it has parenthesis month
            $descText = $item->description;
            $subText = 'Therapy Session Dues';
            if (preg_match('/^(.*?)\s*\((\d{4}-\d{2})\)$/', $item->description, $matches)) {
                $descText = $matches[1];
                $subText = 'ABA Therapy - Session Dues for ' . date('F Y', strtotime($matches[2] . '-01'));
            }

            $itemsHtml .= '
            <tr style="background-color: ' . $rowBg . ';">
                <td style="padding: 12px 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px; text-align: center; color: #64748b;">' . ($index + 1) . '</td>
                <td style="padding: 12px 10px; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #0f172a; font-weight: bold;">
                    ' . $this->e($descText) . '
                    <div style="font-size: 11px; color: #64748b; font-weight: normal; margin-top: 3px;">' . $this->e($subText) . '</div>
                </td>
                <td style="padding: 12px 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px; text-align: center; color: #334155;">' . $this->e($item->quantity) . '</td>
                <td style="padding: 12px 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px; text-align: right; color: #334155;">' . number_format($item->amount, 2) . '</td>
                <td style="padding: 12px 10px; border-bottom: 1px solid #e2e8f0; font-size: 12px; text-align: right; color: #0f172a; font-weight: bold;">' . number_format((float) $item->amount * (int) $item->quantity, 2) . '</td>
            </tr>';
        }

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Invoice ' . $this->e($this->invoice->invoice_no) . '</title>
            <style>
                body {
                    font-family: "DejaVu Sans", sans-serif;
                    color: #334155;
                    margin: 0;
                    padding: 0;
                    background-color: #ffffff;
                }
                .invoice-box {
                    max-width: 800px;
                    margin: auto;
                    padding: 15px;
                }
                .text-navy { color: #1a3c5e; }
                .text-blue { color: #2563a8; }
                .text-muted { color: #64748b; }
                .text-dark { color: #0f172a; }
                .font-bold { font-weight: bold; }
                .w-100 { width: 100%; }
                .border-collapse { border-collapse: collapse; }
                .valign-top { vertical-align: top; }
            </style>
        </head>
        <body>
            <div class="invoice-box">
                <!-- 1. Header Section -->
                <table class="w-100 border-collapse" style="margin-bottom: 25px;">
                    <tr>
                        <td class="valign-top" style="width: 60%;">
                            <table class="border-collapse">
                                <tr>
                                    <td class="valign-top" style="padding-right: 15px;">
                                        ' . $logoHtml . '
                                    </td>
                                    <td class="valign-top">
                                        <div style="font-size: 22px; font-weight: bold; color: #1a3c5e; line-height: 1.1;">Helping Hands</div>
                                        <div style="font-size: 13px; font-weight: bold; color: #2563a8; margin-top: 2px; margin-bottom: 8px;">Child Development & Education Center</div>
                                        
                                        <!-- Address Details -->
                                        <div style="font-size: 11px; color: #475569; line-height: 1.5;">
                                            Address: 123, Green Street, Ambadod, Gujarat - 380001<br/>
                                            Phone: +91 98765 43210<br/>
                                            Email: info@helpinghands.com
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                        <td class="valign-top" style="width: 40%; text-align: right;">
                            <h1 style="font-size: 32px; color: #1a3c5e; text-transform: uppercase; margin: 0 0 10px 0; letter-spacing: 1px; font-weight: bold;">INVOICE</h1>
                            <table class="border-collapse" style="float: right; font-size: 12px; line-height: 1.6;">
                                <tr>
                                    <td class="text-muted" style="padding-right: 10px; text-align: right;">Invoice No</td>
                                    <td class="text-dark font-bold" style="text-align: left;">: &nbsp;' . $this->e($this->invoice->invoice_no) . '</td>
                                </tr>
                                <tr>
                                    <td class="text-muted" style="padding-right: 10px; text-align: right;">Invoice Date</td>
                                    <td class="text-dark" style="text-align: left;">: &nbsp;' . date('d-M-Y', strtotime($this->invoice->invoice_date)) . '</td>
                                </tr>
                                <tr>
                                    <td class="text-muted" style="padding-right: 10px; text-align: right;">Billing Month</td>
                                    <td class="text-dark" style="text-align: left;">: &nbsp;' . $this->e($billingMonthLabel) . '</td>
                                </tr>
                                <tr>
                                    <td class="text-muted" style="padding-right: 10px; text-align: right;">Status</td>
                                    <td class="font-bold" style="color: ' . $statusColor . '; text-align: left; text-transform: uppercase;">: &nbsp;' . $this->e($this->invoice->status) . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <hr style="border: 0; border-top: 1px solid #cbd5e1; margin-bottom: 25px;" />

                <!-- 2. Bill To & Grid Cards Box -->
                <table class="w-100 border-collapse" style="margin-bottom: 30px;">
                    <tr>
                        <!-- Bill To Card (Left) -->
                        <td class="valign-top" style="width: 45%; padding-right: 15px;">
                            <h3 style="color: #1a3c5e; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 0; margin-bottom: 8px; font-weight: bold;">Bill To</h3>
                            <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; background-color: #ffffff; min-height: 100px;">
                                <div style="font-size: 12px; line-height: 1.6; color: #475569;">
                                    <strong style="font-size: 15px; color: #0f172a; display: block; margin-bottom: 5px;">' . $this->e($patient->patient_name) . '</strong>
                                    <strong>Patient ID :</strong> ' . $patientIdStr . '<br/>
                                    <strong>Phone :</strong> ' . $this->e($patient->phone ?? $patient->guardian_phone ?? 'N/A') . '
                                </div>
                            </div>
                        </td>
                        
                        <!-- Grid Cards (Right) -->
                        <td class="valign-top" style="width: 55%;">
                            <div style="border: 1px solid #dbeafe; border-radius: 8px; background-color: #f0f7ff; padding: 12px; margin-top: 23px;">
                                <table class="w-100 border-collapse" style="text-align: center; font-size: 10px; line-height: 1.4;">
                                    <tr>
                                        <!-- Card 1 -->
                                        <td style="width: 25%; border-right: 1px solid #bfdbfe; padding: 5px 0;">
                                            <div style="color: #64748b; font-weight: bold; text-transform: uppercase; font-size: 8px; margin-bottom: 4px;">Invoice Date</div>
                                            <div class="text-dark font-bold" style="font-size: 9.5px;">' . date('d-M-Y', strtotime($this->invoice->invoice_date)) . '</div>
                                        </td>
                                        <!-- Card 2 -->
                                        <td style="width: 25%; border-right: 1px solid #bfdbfe; padding: 5px 0;">
                                            <div style="color: #64748b; font-weight: bold; text-transform: uppercase; font-size: 8px; margin-bottom: 4px;">Billing Month</div>
                                            <div class="text-dark font-bold" style="font-size: 9.5px;">' . $this->e($billingMonthShort) . '</div>
                                        </td>
                                        <!-- Card 3 -->
                                        <td style="width: 25%; border-right: 1px solid #bfdbfe; padding: 5px 0;">
                                            <div style="color: #64748b; font-weight: bold; text-transform: uppercase; font-size: 8px; margin-bottom: 4px;">Invoice No</div>
                                            <div class="text-dark font-bold" style="font-size: 8.5px;">' . $this->e($this->invoice->invoice_no) . '</div>
                                        </td>
                                        <!-- Card 4 -->
                                        <td style="width: 25%; padding: 5px 0;">
                                            <div style="color: #64748b; font-weight: bold; text-transform: uppercase; font-size: 8px; margin-bottom: 4px;">Status</div>
                                            <div class="font-bold" style="font-size: 9.5px; color: ' . $statusColor . '; text-transform: uppercase;">' . $this->e($this->invoice->status) . '</div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- 3. Items Table -->
                <table class="w-100 border-collapse" style="margin-bottom: 25px;">
                    <thead>
                        <tr style="background-color: #1a3c5e; color: #ffffff;">
                            <th style="padding: 12px 10px; text-align: center; font-size: 11px; font-weight: bold; border-top-left-radius: 6px; border-bottom-left-radius: 6px; width: 40px;">#</th>
                            <th style="padding: 12px 10px; text-align: left; font-size: 11px; font-weight: bold;">DESCRIPTION</th>
                            <th style="padding: 12px 10px; text-align: center; font-size: 11px; font-weight: bold; width: 60px;">QTY</th>
                            <th style="padding: 12px 10px; text-align: right; font-size: 11px; font-weight: bold; width: 110px;">RATE (&#8377;)</th>
                            <th style="padding: 12px 10px; text-align: right; font-size: 11px; font-weight: bold; width: 120px; border-top-right-radius: 6px; border-bottom-right-radius: 6px;">AMOUNT (&#8377;)</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . $itemsHtml . '
                    </tbody>
                </table>

                <!-- 4. Totals & Payment Grid -->
                <table class="w-100 border-collapse" style="margin-bottom: 20px;">
                    <tr>
                        <!-- Left Side: Wordings + Payments -->
                        <td class="valign-top" style="width: 50%; padding-right: 25px;">
                            <!-- Amount in Words -->
                            <div style="margin-bottom: 20px;">
                                <div style="font-size: 11px; color: #1a3c5e; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Amount in Words</div>
                                <div style="font-size: 13px; color: #0f172a; font-weight: bold; font-style: italic;">' . $this->numberToWords($dueAmount) . '</div>
                                <hr style="border: 0; border-top: 1px solid #cbd5e1; margin-top: 8px; margin-bottom: 0;" />
                            </div>

                            <!-- Payment Details -->
                            <div>
                                <div style="font-size: 11px; color: #1a3c5e; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">Payment Details</div>
                                <table class="border-collapse" style="font-size: 11px; line-height: 1.7; color: #475569;">
                                    <tr>
                                        <td style="padding-right: 15px; color: #64748b;">Payment Method</td>
                                        <td class="text-dark font-bold">: &nbsp;' . $this->e($payment ? $payment->payment_method : 'N/A') . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding-right: 15px; color: #64748b;">Paid Amount</td>
                                        <td class="text-dark font-bold">: &nbsp;' . $this->formatCurrency($payment ? $payment->amount : 0) . '</td>
                                    </tr>
                                    <tr>
                                        <td style="padding-right: 15px; color: #64748b;">Payment Date</td>
                                        <td class="text-dark font-bold">: &nbsp;' . $this->e($payment ? date('d-M-Y', strtotime($payment->payment_date)) : 'N/A') . '</td>
                                    </tr>
                                </table>
                            </div>
                        </td>

                        <!-- Right Side: Totals Card Box -->
                        <td class="valign-top" style="width: 50%;">
                            <table class="w-100 border-collapse" style="font-size: 12px; line-height: 1.8;">
                                <tr>
                                    <td class="text-muted" style="text-align: right; padding: 4px 10px;">Subtotal</td>
                                    <td class="text-dark font-bold" style="text-align: right; padding: 4px 10px; width: 130px;">' . $this->formatCurrency($this->invoice->total_amount) . '</td>
                                </tr>
                                <tr>
                                    <td class="text-muted" style="text-align: right; padding: 4px 10px;">Discount</td>
                                    <td class="text-dark font-bold" style="text-align: right; padding: 4px 10px; width: 130px;">' . $this->formatCurrency(0) . '</td>
                                </tr>
                                <tr>
                                    <td class="text-dark font-bold" style="text-align: right; padding: 6px 10px; font-size: 13px; border-top: 1px solid #cbd5e1;">Total Bill</td>
                                    <td class="text-dark font-bold" style="text-align: right; padding: 6px 10px; font-size: 14px; border-top: 1px solid #cbd5e1; width: 130px;">' . $this->formatCurrency($this->invoice->total_amount) . '</td>
                                </tr>
                                <tr>
                                    <td style="text-align: right; padding: 4px 10px; color: #16a34a; font-weight: bold;">Paid So Far</td>
                                    <td style="text-align: right; padding: 4px 10px; color: #16a34a; font-weight: bold; width: 130px;">' . $this->formatCurrency($this->invoice->paid_amount) . '</td>
                                </tr>
                                
                                <!-- Outstanding highlighted bar -->
                                <tr style="background-color: #fff7ed; border: 1px solid #fed7aa;">
                                    <td style="text-align: right; padding: 10px; color: #ea580c; font-weight: bold; font-size: 14px; border-top-left-radius: 6px; border-bottom-left-radius: 6px;">Outstanding Due</td>
                                    <td style="text-align: right; padding: 10px; color: #ea580c; font-weight: bold; font-size: 16px; border-top-right-radius: 6px; border-bottom-right-radius: 6px; width: 130px;">' . $this->formatCurrency($dueAmount) . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- 5. Notes & Warning Callout -->
                <table class="w-100 border-collapse" style="margin-top: 15px; margin-bottom: 25px;">
                    <tr>
                        <td class="valign-top" style="width: 100%;">
                            <!-- Notes Section -->
                            <div style="margin-bottom: 20px;">
                                <div style="font-size: 11px; color: #1a3c5e; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Notes</div>
                                <div style="font-size: 11px; line-height: 1.5; color: #475569;">
                                    ' . ($this->invoice->notes ? nl2br($this->e($this->invoice->notes)) : 'Thank you for trusting us with your child\'s care. Please settle the outstanding amount at your earliest convenience.') . '
                                </div>
                            </div>

                            <!-- Callout notification bar -->
                            <div style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 10px 15px; color: #1e40af; font-size: 11px; line-height: 1.4;">
                                Please make the payment of the outstanding amount to avoid interruption in services.
                            </div>
                        </td>
                    </tr>
                </table>

                <!-- 6. Footer bar -->
                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin-bottom: 12px;" />
                <div style="text-align: center; font-size: 10px; color: #64748b; line-height: 1.6; padding-bottom: 10px;">
                    www.helpinghands.com &nbsp;&nbsp;|&nbsp;&nbsp; 
                    +91 98765 43210 &nbsp;&nbsp;|&nbsp;&nbsp; 
                    info@helpinghands.com
                </div>
            </div>
        </body>
        </html>';
    }
}
