<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TimeSlotController extends Controller
{
    public function index()
    {
        $slots = TimeSlot::query()->orderBy('start_time')->get();
        return ApiResponse::success($slots, 'OK');
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'start_time' => ['required', 'date_format:H:i'],
                'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
                'duration_minutes' => ['required', 'integer', 'min:1'],
                'is_active' => ['nullable', 'boolean'],
            ]);

            $slot = TimeSlot::create([
                'start_time' => $request->input('start_time') . ':00',
                'end_time' => $request->input('end_time') . ':00',
                'duration_minutes' => (int) $request->input('duration_minutes'),
                'is_active' => (bool) ($request->input('is_active', true)),
            ]);

            return ApiResponse::success($slot, 'Time slot created', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function update(Request $request, $id)
    {
        $slot = TimeSlot::find($id);
        if (! $slot) {
            return ApiResponse::error('Time slot not found', 404);
        }

        try {
            $this->validate($request, [
                'start_time' => ['sometimes', 'required', 'date_format:H:i'],
                'end_time' => ['sometimes', 'required', 'date_format:H:i'],
                'duration_minutes' => ['sometimes', 'required', 'integer', 'min:1'],
                'is_active' => ['sometimes', 'required', 'boolean'],
            ]);

            if ($request->has('start_time')) {
                $slot->start_time = $request->input('start_time') . ':00';
            }
            if ($request->has('end_time')) {
                $slot->end_time = $request->input('end_time') . ':00';
            }
            if ($request->has('duration_minutes')) {
                $slot->duration_minutes = (int) $request->input('duration_minutes');
            }
            if ($request->has('is_active')) {
                $slot->is_active = (bool) $request->input('is_active');
            }

            $slot->save();
            return ApiResponse::success($slot, 'Time slot updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $slot = TimeSlot::find($id);
        if (! $slot) {
            return ApiResponse::error('Time slot not found', 404);
        }

        $slot->delete();
        return ApiResponse::success(null, 'Time slot deleted');
    }
}

