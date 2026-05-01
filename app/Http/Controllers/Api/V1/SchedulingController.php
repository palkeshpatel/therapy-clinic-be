<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DailySchedule;
use App\Models\Therapist;
use App\Models\TherapistSchedule;
use App\Models\TimeSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SchedulingController extends Controller
{
    public function daily(Request $request)
    {
        $date = (string) $request->input('date', Carbon::today()->toDateString());

        $query = DailySchedule::query()->with(['slot', 'patient', 'therapist', 'therapy']);
        $query->whereDate('date', $date)->orderBy('slot_id');

        if ($therapistId = $request->input('therapist_id')) {
            $query->where('therapist_id', $therapistId);
        }

        return ApiResponse::success($query->get(), 'OK');
    }

    public function book(Request $request)
    {
        try {
            $this->validate($request, [
                'date' => ['required', 'date'],
                'slot_id' => ['required', 'integer', 'exists:time_slots,id'],
                'patient_id' => ['required', 'integer', 'exists:patients,id'],
                'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
                'therapy_id' => ['nullable', 'integer', 'exists:therapies,id'],
            ]);

            $row = DailySchedule::create([
                'date' => $request->input('date'),
                'slot_id' => (int) $request->input('slot_id'),
                'patient_id' => (int) $request->input('patient_id'),
                'therapist_id' => (int) $request->input('therapist_id'),
                'therapy_id' => $request->input('therapy_id'),
                'status' => 'scheduled',
                'created_by' => Auth::id(),
            ]);

            $row->load(['slot', 'patient', 'therapist', 'therapy']);
            return ApiResponse::success($row, 'Booked', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Throwable $e) {
            // unique constraint hit -> slot already taken
            return ApiResponse::error('Slot already booked', 409);
        }
    }

    public function update(Request $request, $id)
    {
        $row = DailySchedule::find($id);
        if (! $row) {
            return ApiResponse::error('Booking not found', 404);
        }

        try {
            $user = Auth::user();
            $user?->loadMissing('role');
            $roleName = $user?->role?->role_name;

            // Therapist: only own rows, only status → in_progress | completed
            if ($roleName === 'Therapist') {
                $myTherapistId = Therapist::query()->where('user_id', $user->id)->value('id');
                if (! $myTherapistId || (int) $row->therapist_id !== (int) $myTherapistId) {
                    return ApiResponse::error('Forbidden', 403);
                }

                $this->validate($request, [
                    'status' => ['required', Rule::in(['in_progress', 'completed'])],
                ]);
                $next = (string) $request->input('status');

                if ($next === 'in_progress') {
                    if ($row->status !== 'scheduled') {
                        return ApiResponse::error('Only scheduled bookings can be started', 422);
                    }
                }
                if ($next === 'completed') {
                    if ($row->status === 'completed') {
                        $row->load(['slot', 'patient', 'therapist', 'therapy']);

                        return ApiResponse::success($row, 'Updated');
                    }
                    if (! in_array($row->status, ['scheduled', 'in_progress'], true)) {
                        return ApiResponse::error('Invalid status transition', 422);
                    }
                }

                $row->status = $next;
                $row->save();
                $row->load(['slot', 'patient', 'therapist', 'therapy']);

                return ApiResponse::success($row, 'Updated');
            }

            $this->validate($request, [
                'date' => ['sometimes', 'required', 'date'],
                'slot_id' => ['sometimes', 'required', 'integer', 'exists:time_slots,id'],
                'patient_id' => ['sometimes', 'required', 'integer', 'exists:patients,id'],
                'therapist_id' => ['sometimes', 'required', 'integer', 'exists:therapists,id'],
                'therapy_id' => ['sometimes', 'nullable', 'integer', 'exists:therapies,id'],
                'status' => ['sometimes', 'required', Rule::in(['scheduled', 'in_progress', 'completed', 'cancelled'])],
            ]);

            $row->fill($request->only(['date', 'slot_id', 'patient_id', 'therapist_id', 'therapy_id', 'status']));
            $row->save();
            $row->load(['slot', 'patient', 'therapist', 'therapy']);

            return ApiResponse::success($row, 'Updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Throwable $e) {
            return ApiResponse::error('Slot already booked', 409);
        }
    }

    public function cancel($id)
    {
        $row = DailySchedule::find($id);
        if (! $row) {
            return ApiResponse::error('Booking not found', 404);
        }

        $row->status = 'cancelled';
        $row->save();

        return ApiResponse::success(null, 'Cancelled');
    }

    public function availability(Request $request)
    {
        try {
            $this->validate($request, [
                'date' => ['required', 'date'],
                'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
            ]);

            $date = (string) $request->input('date');
            $therapistId = (int) $request->input('therapist_id');

            $allSlots = TimeSlot::query()->where('is_active', true)->orderBy('start_time')->get();

            // Busy slots from therapist_schedule
            $blocked = TherapistSchedule::query()
                ->where('therapist_id', $therapistId)
                ->whereDate('date', $date)
                ->whereIn('status', ['busy', 'leave'])
                ->pluck('slot_id')
                ->all();

            // Already booked slots from daily_schedule
            $booked = DailySchedule::query()
                ->where('therapist_id', $therapistId)
                ->whereDate('date', $date)
                ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
                ->pluck('slot_id')
                ->all();

            $unavailable = array_unique(array_merge($blocked, $booked));

            $freeSlots = $allSlots->filter(fn ($s) => ! in_array($s->id, $unavailable, true))->values();

            return ApiResponse::success($freeSlots, 'OK');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }
}

