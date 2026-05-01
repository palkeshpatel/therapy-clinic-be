<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PatientDocumentController extends Controller
{
    public function index($id)
    {
        $patient = Patient::find($id);
        if (! $patient) {
            return ApiResponse::error('Patient not found', 404);
        }

        $docs = PatientDocument::query()
            ->where('patient_id', $patient->id)
            ->orderByDesc('id')
            ->get();

        return ApiResponse::success($docs, 'OK');
    }

    public function store(Request $request, $id)
    {
        $patient = Patient::find($id);
        if (! $patient) {
            return ApiResponse::error('Patient not found', 404);
        }

        try {
            $this->validate($request, [
                'document_type' => ['required', 'string', 'max:80'],
                'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            ]);

            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $safeName = Str::uuid()->toString() . '.' . $ext;

            $relativeDir = 'uploads/patients/' . $patient->id;
            $targetDir = public_path($relativeDir);

            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0775, true);
            }

            $file->move($targetDir, $safeName);
            $relativePath = $relativeDir . '/' . $safeName;

            $doc = PatientDocument::create([
                'patient_id' => $patient->id,
                'document_type' => (string) $request->input('document_type'),
                'file_path' => $relativePath,
                'uploaded_at' => Carbon::now(),
            ]);

            return ApiResponse::success($doc, 'Document uploaded', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id, $docId)
    {
        $patient = Patient::find($id);
        if (! $patient) {
            return ApiResponse::error('Patient not found', 404);
        }

        $doc = PatientDocument::query()
            ->where('patient_id', $patient->id)
            ->where('id', $docId)
            ->first();

        if (! $doc) {
            return ApiResponse::error('Document not found', 404);
        }

        $fullPath = public_path($doc->file_path);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        $doc->delete();
        return ApiResponse::success(null, 'Document deleted');
    }
}

