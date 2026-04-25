<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientMedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PatientMedicalRecordController extends Controller
{
    public function show($id)
    {
        $patient = Patient::find($id);
        if (! $patient) {
            return ApiResponse::error('Patient not found', 404);
        }

        $record = PatientMedicalRecord::query()
            ->where('patient_id', $patient->id)
            ->orderByDesc('id')
            ->first();

        return ApiResponse::success($record, 'OK');
    }

    public function upsert(Request $request, $id)
    {
        $patient = Patient::find($id);
        if (! $patient) {
            return ApiResponse::error('Patient not found', 404);
        }

        try {
            $this->validate($request, [
                'diagnosis' => ['nullable', 'string'],
                'notes' => ['nullable', 'string'],
            ]);

            $record = PatientMedicalRecord::query()
                ->where('patient_id', $patient->id)
                ->orderByDesc('id')
                ->first();

            if (! $record) {
                $record = new PatientMedicalRecord();
                $record->patient_id = $patient->id;
            }

            $record->fill($request->only(['diagnosis', 'notes']));
            $record->save();

            return ApiResponse::success($record, 'Medical record updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }
}

