<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\DailySchedule;
use App\Models\Therapist;
use App\Models\TherapistSchedule;
use App\Models\TherapySession;
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
        $myTherapistId = $this->myTherapistIdIfTherapist();

        if ($myTherapistId) {
            $query->where('therapist_id', $myTherapistId);
        } elseif ($therapistId = $request->input('therapist_id')) {
            $query->where('therapist_id', $therapistId);
        }

        $rows = $query->get();

        return ApiResponse::success($this->attachSessionTimes($rows, $date), 'OK');
    }

    public function book(Request $request)
    {
        try {
            $this->validate($request, [
                'date' => ['required', 'date'],
                'slot_id' => ['required', 'integer', 'exists:time_slots,id'],
                'patient_ids' => ['required', 'array', 'min:1'],
                'patient_ids.*' => ['integer', 'exists:patients,id'],
                'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
                'therapy_id' => ['nullable', 'integer', 'exists:therapies,id'],
            ]);

            $date = $request->input('date');
            $slot_id = (int) $request->input('slot_id');
            $therapist_id = (int) $request->input('therapist_id');
            $therapy_id = $request->input('therapy_id');
            $patient_ids = $request->input('patient_ids');

            $createdRows = [];

            \DB::beginTransaction();
            foreach ($patient_ids as $patient_id) {
                // Check if this exact booking already exists to prevent duplicate
                $existing = DailySchedule::where([
                    'date' => $date,
                    'slot_id' => $slot_id,
                    'therapist_id' => $therapist_id,
                    'patient_id' => $patient_id,
                ])->first();

                if ($existing) {
                    if ($existing->status === 'cancelled') {
                        $existing->status = 'scheduled';
                        $existing->therapy_id = $therapy_id;
                        $existing->save();
                        $existing->load(['slot', 'patient', 'therapist', 'therapy']);
                        $createdRows[] = $existing;
                    }
                } else {
                    $row = DailySchedule::create([
                        'date' => $date,
                        'slot_id' => $slot_id,
                        'patient_id' => (int) $patient_id,
                        'therapist_id' => $therapist_id,
                        'therapy_id' => $therapy_id,
                        'status' => 'scheduled',
                        'created_by' => Auth::id(),
                    ]);
                    $row->load(['slot', 'patient', 'therapist', 'therapy']);
                    $createdRows[] = $row;
                }
            }
            \DB::commit();

            if (empty($createdRows)) {
                return ApiResponse::error('All selected patients are already booked for this slot', 409);
            }

            return ApiResponse::success($createdRows, 'Booked successfully', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        } catch (\Throwable $e) {
            \DB::rollBack();
            return ApiResponse::error('Booking failed: ' . $e->getMessage(), 500);
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
            $roleType = $user?->role?->role_type;

            // Therapist: only own rows, only status → in_progress | completed
            if ($roleType === 'therapist') {
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
            $myTherapistId = $this->myTherapistIdIfTherapist();
            if ($myTherapistId) {
                $this->validate($request, [
                    'date' => ['required', 'date'],
                ]);
            } else {
                $this->validate($request, [
                    'date' => ['required', 'date'],
                    'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
                ]);
            }

            $date = (string) $request->input('date');
            $therapistId = $myTherapistId ?: (int) $request->input('therapist_id');

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

    /**
     * Check therapist availability for every day in a month for a given slot.
     * GET /scheduling/bulk-availability?therapist_id=1&month=2026-07&slot_id=3
     */
    public function bulkAvailability(Request $request)
    {
        try {
            $this->validate($request, [
                'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
                'month'        => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
                'slot_id'      => ['nullable', 'integer'],
            ]);

            $therapistId = (int) $request->input('therapist_id');
            $month       = (string) $request->input('month');
            $slotId      = $request->input('slot_id') ? (int) $request->input('slot_id') : null;

            [$year, $mon] = explode('-', $month);
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int)$mon, (int)$year);


            $totalSlots = TimeSlot::where('is_active', true)->count();
            $today      = Carbon::now()->toDateString();
            $result     = [];

            // If a specific slot is requested, load its time range for overlap detection
            $requestedSlot = null;
            if ($slotId) {
                $requestedSlot = TimeSlot::find($slotId);
            }

            // Load all booked slot time ranges (join with time_slots) so we can check overlaps
            // We load slot start_time/end_time via the TimeSlot model for all booked records in this month
            $bookedWithTimes = DailySchedule::query()
                ->where('therapist_id', $therapistId)
                ->whereYear('date', $year)
                ->whereMonth('date', (int)$mon)
                ->whereIn('status', ['scheduled', 'in_progress', 'completed'])
                ->join('time_slots', 'daily_schedule.slot_id', '=', 'time_slots.id')
                ->get(['daily_schedule.date', 'time_slots.start_time', 'time_slots.end_time'])
                ->groupBy(fn ($r) => substr($r->date, 0, 10));

            $blockedWithTimes = TherapistSchedule::query()
                ->where('therapist_id', $therapistId)
                ->whereYear('date', $year)
                ->whereMonth('date', (int)$mon)
                ->whereIn('status', ['busy', 'leave'])
                ->join('time_slots', 'therapist_schedule.slot_id', '=', 'time_slots.id')
                ->get(['therapist_schedule.date', 'time_slots.start_time', 'time_slots.end_time'])
                ->groupBy(fn ($r) => substr($r->date, 0, 10));

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dateStr = $year . '-' . str_pad($mon, 2, '0', STR_PAD_LEFT) . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);

                if ($dateStr <= $today) {
                    $result[$dateStr] = 'past';
                    continue;
                }

                // Merge all occupied time ranges for this date
                $occupiedRanges = collect($bookedWithTimes->get($dateStr, collect()))
                    ->merge(collect($blockedWithTimes->get($dateStr, collect())));

                if ($requestedSlot) {
                    // Check if any booked/blocked slot overlaps with the requested time slot
                    $reqStart = $requestedSlot->start_time; // e.g. "08:00:00"
                    $reqEnd   = $requestedSlot->end_time;   // e.g. "08:45:00"

                    $hasOverlap = $occupiedRanges->contains(function ($r) use ($reqStart, $reqEnd) {
                        // Overlap: existing.start < req.end  AND  existing.end > req.start
                        return $r->start_time < $reqEnd && $r->end_time > $reqStart;
                    });

                    $result[$dateStr] = $hasOverlap ? 'unavailable' : 'available';
                } else {
                    // No specific slot: unavailable if ALL slots are occupied
                    $result[$dateStr] = ($occupiedRanges->count() >= $totalSlots) ? 'unavailable' : 'available';
                }
            }

            return ApiResponse::success($result, 'OK');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    private function myTherapistIdIfTherapist(): ?int
    {
        $user = Auth::user();
        if (! $user) {
            return null;
        }
        $user->loadMissing('role');
        if (($user->role?->role_type ?? null) !== 'therapist') {
            return null;
        }

        return Therapist::query()->where('user_id', $user->id)->value('id');
    }

    private function attachSessionTimes($rows, string $date)
    {
        $sessions = TherapySession::query()
            ->whereDate('session_date', $date)
            ->get()
            ->groupBy(fn ($session) => $session->therapist_id.'-'.$session->slot_id);

        return $rows->map(function ($row) use ($sessions) {
            $session = $sessions->get($row->therapist_id.'-'.$row->slot_id)?->first();
            $row->setAttribute('start_time', $session->start_time ?? null);
            $row->setAttribute('end_time', $session->end_time ?? null);
            $row->setAttribute('duration', $session->duration ?? null);
            return $row;
        });
    }
}

