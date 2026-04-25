<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\WaitingList;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class WaitingListController extends Controller
{
    public function index(Request $request)
    {
        $query = WaitingList::query()->with(['patient', 'therapy']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('requested_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('requested_date', '<=', $to);
        }

        $query->orderByDesc('priority')->orderByDesc('requested_date')->orderByDesc('id');

        return ApiResponse::success($query->get(), 'OK');
    }

    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'patient_id' => ['required', 'integer', 'exists:patients,id'],
                'therapy_id' => ['required', 'integer', 'exists:therapies,id'],
                'requested_date' => ['required', 'date'],
                'priority' => ['nullable', 'integer', 'min:0'],
                'status' => ['nullable', Rule::in(['waiting', 'scheduled', 'cancelled'])],
            ]);

            $row = WaitingList::create($request->only([
                'patient_id', 'therapy_id', 'requested_date', 'priority', 'status',
            ]));
            $row->load(['patient', 'therapy']);

            return ApiResponse::success($row, 'Added to waiting list', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function update(Request $request, $id)
    {
        $row = WaitingList::find($id);
        if (! $row) {
            return ApiResponse::error('Waiting list item not found', 404);
        }

        try {
            $this->validate($request, [
                'requested_date' => ['sometimes', 'required', 'date'],
                'priority' => ['sometimes', 'required', 'integer', 'min:0'],
                'status' => ['sometimes', 'required', Rule::in(['waiting', 'scheduled', 'cancelled'])],
            ]);

            $row->fill($request->only(['requested_date', 'priority', 'status']));
            $row->save();
            $row->load(['patient', 'therapy']);

            return ApiResponse::success($row, 'Updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $row = WaitingList::find($id);
        if (! $row) {
            return ApiResponse::error('Waiting list item not found', 404);
        }

        $row->delete();
        return ApiResponse::success(null, 'Removed');
    }
}

