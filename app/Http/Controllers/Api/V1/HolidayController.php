<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Services\HolidayCalendarService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HolidayController extends Controller
{
    public function __construct(private HolidayCalendarService $calendar)
    {
    }

    public function index(Request $request)
    {
        $query = Holiday::query();

        if ($year = $request->input('year')) {
            $start = \Illuminate\Support\Carbon::create((int) $year, 1, 1)->startOfYear()->toDateString();
            $end = \Illuminate\Support\Carbon::create((int) $year, 12, 31)->endOfYear()->toDateString();
            $query->whereBetween('holiday_date', [$start, $end]);
        }

        if ($month = $request->input('month')) {
            $start = \Illuminate\Support\Carbon::createFromFormat('Y-m', (string) $month)->startOfMonth()->toDateString();
            $end = \Illuminate\Support\Carbon::createFromFormat('Y-m', (string) $month)->endOfMonth()->toDateString();
            $query->whereBetween('holiday_date', [$start, $end]);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return ApiResponse::success(
            $query->orderBy('holiday_date')->get(),
            'OK'
        );
    }

    public function generateYear(Request $request)
    {
        try {
            $this->validate($request, [
                'year' => ['required', 'integer', 'min:2026', 'max:2100'],
                'include_special_dates' => ['nullable', 'boolean'],
            ]);

            $year = (int) $request->input('year');
            $includeSpecialDates = (bool) $request->input('include_special_dates', false);
            $rows = $this->calendar->generateForYear($year, $includeSpecialDates);

            $saved = [];
            foreach ($rows as $row) {
                $saved[] = Holiday::updateOrCreate(
                    ['holiday_date' => $row['holiday_date']],
                    $row
                );
            }

            return ApiResponse::success([
                'year' => $year,
                'count' => count($saved),
                'holidays' => $saved,
            ], 'Holiday year generated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'holiday_date' => ['required', 'date'],
                'holiday_name' => ['required', 'string', 'max:150'],
                'holiday_type' => ['nullable', 'string', 'max:30'],
                'applicable' => ['nullable', 'string', 'max:100'],
                'description' => ['nullable', 'string'],
                'rule_type' => ['nullable', 'string', 'max:30'],
                'is_recurring' => ['nullable', 'boolean'],
                'status' => ['nullable', Rule::in(['active', 'inactive'])],
            ]);

            $holiday = Holiday::create([
                'holiday_date' => $request->input('holiday_date'),
                'holiday_name' => $request->input('holiday_name'),
                'holiday_type' => $request->input('holiday_type', 'National'),
                'applicable' => $request->input('applicable', 'All India'),
                'description' => $request->input('description'),
                'rule_type' => $request->input('rule_type', 'one_time'),
                'is_recurring' => (bool) $request->input('is_recurring', false),
                'status' => (string) $request->input('status', 'active'),
            ]);

            return ApiResponse::success($holiday, 'Holiday created', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::find($id);
        if (! $holiday) {
            return ApiResponse::error('Holiday not found', 404);
        }

        try {
            $this->validate($request, [
                'holiday_date' => ['sometimes', 'required', 'date'],
                'holiday_name' => ['sometimes', 'required', 'string', 'max:150'],
                'holiday_type' => ['sometimes', 'nullable', 'string', 'max:30'],
                'applicable' => ['sometimes', 'nullable', 'string', 'max:100'],
                'description' => ['sometimes', 'nullable', 'string'],
                'rule_type' => ['sometimes', 'nullable', 'string', 'max:30'],
                'is_recurring' => ['sometimes', 'boolean'],
                'status' => ['sometimes', Rule::in(['active', 'inactive'])],
            ]);

            $holiday->fill($request->only(['holiday_date', 'holiday_name', 'holiday_type', 'applicable', 'description', 'rule_type', 'is_recurring', 'status']));
            $holiday->save();

            return ApiResponse::success($holiday, 'Holiday updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $holiday = Holiday::find($id);
        if (! $holiday) {
            return ApiResponse::error('Holiday not found', 404);
        }

        $holiday->delete();
        return ApiResponse::success(null, 'Holiday deleted');
    }
}
