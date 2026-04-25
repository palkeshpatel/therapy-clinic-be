<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Therapist;
use App\Models\TherapistDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TherapistDocumentController extends Controller
{
    public function index($id)
    {
        $therapist = Therapist::find($id);
        if (! $therapist) {
            return ApiResponse::error('Therapist not found', 404);
        }

        $docs = TherapistDocument::query()
            ->where('therapist_id', $therapist->id)
            ->orderByDesc('id')
            ->get();

        return ApiResponse::success($docs, 'OK');
    }

    public function store(Request $request, $id)
    {
        $therapist = Therapist::find($id);
        if (! $therapist) {
            return ApiResponse::error('Therapist not found', 404);
        }

        try {
            $this->validate($request, [
                'document_type' => ['required', 'string', 'max:80'],
                'file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            ]);

            $file = $request->file('file');
            $ext = strtolower($file->getClientOriginalExtension() ?: 'bin');
            $safeName = Str::uuid()->toString() . '.' . $ext;

            $relativeDir = 'uploads/therapists/' . $therapist->id;
            $targetDir = public_path($relativeDir);

            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0775, true);
            }

            $file->move($targetDir, $safeName);
            $relativePath = $relativeDir . '/' . $safeName;

            $doc = TherapistDocument::create([
                'therapist_id' => $therapist->id,
                'document_type' => (string) $request->input('document_type'),
                'file_path' => $relativePath,
            ]);

            return ApiResponse::success($doc, 'Document uploaded', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id, $docId)
    {
        $therapist = Therapist::find($id);
        if (! $therapist) {
            return ApiResponse::error('Therapist not found', 404);
        }

        $doc = TherapistDocument::query()
            ->where('therapist_id', $therapist->id)
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

