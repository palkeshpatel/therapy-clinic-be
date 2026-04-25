<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\ClinicSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClinicSettingController extends Controller
{
    public function index()
    {
        $clinic = Clinic::query()->orderBy('id')->first();
        if (! $clinic) {
            return ApiResponse::success([], 'OK');
        }

        $settings = ClinicSetting::query()
            ->where('clinic_id', $clinic->id)
            ->orderBy('setting_key')
            ->get();

        return ApiResponse::success($settings, 'OK');
    }

    public function bulkUpsert(Request $request)
    {
        try {
            $this->validate($request, [
                'items' => ['required', 'array', 'min:1'],
                'items.*.setting_key' => ['required', 'string', 'max:100'],
                'items.*.setting_value' => ['nullable'],
            ]);

            $clinic = Clinic::query()->orderBy('id')->first();
            if (! $clinic) {
                return ApiResponse::error('Clinic not found', 404);
            }

            $items = $request->input('items', []);

            DB::transaction(function () use ($clinic, $items) {
                foreach ($items as $item) {
                    ClinicSetting::query()->updateOrCreate(
                        ['clinic_id' => $clinic->id, 'setting_key' => $item['setting_key']],
                        ['setting_value' => $item['setting_value'] ?? null]
                    );
                }
            });

            $settings = ClinicSetting::query()
                ->where('clinic_id', $clinic->id)
                ->orderBy('setting_key')
                ->get();

            return ApiResponse::success($settings, 'Settings updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }
}

