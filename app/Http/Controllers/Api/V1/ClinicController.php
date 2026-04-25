<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClinicController extends Controller
{
    public function show()
    {
        $clinic = Clinic::query()->orderBy('id')->first();
        return ApiResponse::success($clinic, 'OK');
    }

    public function update(Request $request)
    {
        try {
            $this->validate($request, [
                'clinic_name' => ['sometimes', 'required', 'string', 'max:150'],
                'address' => ['sometimes', 'nullable', 'string'],
                'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
                'email' => ['sometimes', 'nullable', 'email', 'max:150'],
                'gst_number' => ['sometimes', 'nullable', 'string', 'max:30'],
            ]);

            $clinic = Clinic::query()->orderBy('id')->first();
            if (! $clinic) {
                $clinic = new Clinic();
            }

            $clinic->fill($request->only(['clinic_name', 'address', 'phone', 'email', 'gst_number']));
            $clinic->save();

            return ApiResponse::success($clinic, 'Clinic updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }
}

