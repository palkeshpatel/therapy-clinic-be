<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = Invoice::query()->with(['patient', 'items', 'payments']);

        if ($patientId = $request->input('patient_id')) {
            $query->where('patient_id', $patientId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('invoice_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('invoice_date', '<=', $to);
        }

        $query->orderByDesc('invoice_date')->orderByDesc('id');

        return ApiResponse::paginate($query->paginate($perPage), 'OK');
    }

    public function store(Request $request, InvoiceService $service)
    {
        try {
            $this->validate($request, [
                'patient_id' => ['required', 'integer', 'exists:patients,id'],
                'invoice_date' => ['required', 'date'],
                'due_date' => ['nullable', 'date'],
                'notes' => ['nullable', 'string'],
                'items' => ['required', 'array', 'min:1'],
                'items.*.therapy_id' => ['nullable', 'integer', 'exists:therapies,id'],
                'items.*.description' => ['required', 'string', 'max:255'],
                'items.*.quantity' => ['nullable', 'integer', 'min:1'],
                'items.*.amount' => ['required', 'numeric', 'min:0'],
            ]);

            // Prevent duplicate invoice generation for the same patient and month
            $billableMonths = [];
            foreach ($request->input('items', []) as $item) {
                if (preg_match('/(\d{4}-\d{2})/', $item['description'], $matches)) {
                    $billableMonths[] = $matches[1];
                }
            }
            $billableMonths = array_unique($billableMonths);

            foreach ($billableMonths as $month) {
                $exists = Invoice::where('patient_id', $request->input('patient_id'))
                    ->whereHas('items', function ($q) use ($month) {
                        $q->where('description', 'like', "%{$month}%");
                    })
                    ->exists();

                if ($exists) {
                    $monthLabel = \Illuminate\Support\Carbon::parse($month . '-01')->format('F Y');
                    return ApiResponse::error("An invoice for this patient has already been generated for {$monthLabel}.", 422);
                }
            }

            $invoice = $service->create($request->all());

            return ApiResponse::success($invoice, 'Invoice created', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function show($id)
    {
        $invoice = Invoice::with(['patient', 'items', 'payments'])->find($id);
        if (! $invoice) {
            return ApiResponse::error('Invoice not found', 404);
        }
        return ApiResponse::success($invoice, 'OK');
    }

    public function update(Request $request, $id)
    {
        $invoice = Invoice::find($id);
        if (! $invoice) {
            return ApiResponse::error('Invoice not found', 404);
        }

        try {
            $this->validate($request, [
                'invoice_date' => ['sometimes', 'required', 'date'],
                'due_date' => ['sometimes', 'nullable', 'date'],
                'notes' => ['sometimes', 'nullable', 'string'],
                'status' => ['sometimes', 'required', 'in:paid,partial,pending'],
            ]);

            $invoice->fill($request->only(['invoice_date', 'due_date', 'notes', 'status']));
            $invoice->save();

            return ApiResponse::success($invoice->fresh(['patient', 'items', 'payments']), 'Invoice updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $invoice = Invoice::find($id);
        if (! $invoice) {
            return ApiResponse::error('Invoice not found', 404);
        }

        // For now delete invoice (hard delete). Later we can add status=cancelled if needed.
        $invoice->delete();
        return ApiResponse::success(null, 'Invoice deleted');
    }

    public function downloadPdf($id)
    {
        $invoice = Invoice::with(['patient', 'items', 'payments'])->find($id);

        if (! $invoice) {
            return ApiResponse::error('Invoice not found', 404);
        }

        try {
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', true);
            $options->set('isPhpEnabled', false);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new \Dompdf\Dompdf($options);

            $logoData = '';
            $logoPath = public_path('logo.png');
            if (file_exists($logoPath)) {
                $logoData = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
            }

            $pdfService = new \App\Services\InvoicePdfService($invoice, $logoData);
            $html = $pdfService->buildHtml();

            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $pdfOutput = $dompdf->output();

            $filename = 'invoice_' . str_replace(' ', '_', $invoice->invoice_no) . '_' . date('Ymd') . '.pdf';

            return response($pdfOutput, 200, [
                'Content-Type'                => 'application/pdf',
                'Content-Disposition'         => 'attachment; filename="' . $filename . '"',
                'Access-Control-Expose-Headers' => 'Content-Disposition',
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'PDF generation failed: ' . $e->getMessage()], 500);
        }
    }
}

