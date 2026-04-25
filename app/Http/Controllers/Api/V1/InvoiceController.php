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

        $query = Invoice::query()->with('patient');

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
}

