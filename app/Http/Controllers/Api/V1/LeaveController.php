<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Helpers\MailHelper;
use App\Http\Controllers\Controller;
use App\Models\Therapist;
use App\Models\TherapistLeave;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeaveController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->input('per_page', 15));
        $perPage = max(1, min(100, $perPage));

        $query = TherapistLeave::query()->with('therapist');
        $myTherapistId = $this->myTherapistIdIfTherapist();
        if ($myTherapistId) {
            $query->where('therapist_id', $myTherapistId);
        }

        if ($month = $request->input('month')) {
            $start = \Illuminate\Support\Carbon::createFromFormat('Y-m', (string) $month)->startOfMonth()->toDateString();
            $end = \Illuminate\Support\Carbon::createFromFormat('Y-m', (string) $month)->endOfMonth()->toDateString();
            $query->whereBetween('leave_date', [$start, $end]);
        }

        if (! $myTherapistId && ($therapistId = $request->input('therapist_id'))) {
            $query->where('therapist_id', $therapistId);
        }
        if ($from = $request->input('from')) {
            $query->whereDate('leave_date', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->whereDate('leave_date', '<=', $to);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $query->orderByDesc('leave_date')->orderByDesc('id');

        return ApiResponse::paginate($query->paginate($perPage), 'OK');
    }

    public function store(Request $request)
    {
        try {
            $myTherapistId = $this->myTherapistIdIfTherapist();
            if ($myTherapistId) {
                $this->validate($request, [
                    'leave_date' => ['required', 'date', 'after_or_equal:today'],
                    'leave_type' => ['required', 'string', 'max:50'],
                    'reason'     => ['nullable', 'string'],
                ]);
            } else {
                $this->validate($request, [
                    'therapist_id' => ['required', 'integer', 'exists:therapists,id'],
                    'leave_date'   => ['required', 'date', 'after_or_equal:today'],
                    'leave_type'   => ['required', 'string', 'max:50'],
                    'reason'       => ['nullable', 'string'],
                ]);
            }

            $therapistId = $myTherapistId ?: (int) $request->input('therapist_id');

            $leave = TherapistLeave::create([
                'therapist_id' => $therapistId,
                'leave_date'   => $request->input('leave_date'),
                'leave_type'   => (string) $request->input('leave_type'),
                'reason'       => $request->input('reason'),
                'status'       => 'pending',
            ]);
            $leave->load('therapist');

            // ── Email admin: new leave request ──────────────────────────
            $therapistName = $leave->therapist?->name ?? 'A Therapist';
            $leaveDate     = $leave->leave_date;
            $leaveType     = ucwords(str_replace('_', ' ', $leave->leave_type));
            $reason        = $leave->reason ?? 'No reason provided';

            $adminEmail = env('MAIL_FROM_ADDRESS');
            $adminName  = 'Admin';

            $html = MailHelper::template("
                <p style='color:#111827;font-size:15px;margin-bottom:16px;'>
                    Hello <strong>{$adminName}</strong>,
                </p>
                <p style='color:#374151;'>
                    A new <strong>leave request</strong> has been submitted and is waiting for your approval.
                </p>
                <table style='width:100%;border-collapse:collapse;margin:20px 0;'>
                    <tr style='background:#f5f3ff;'>
                        <td style='padding:10px 14px;font-weight:600;color:#7c3aed;border-radius:6px 0 0 6px;width:130px;'>Therapist</td>
                        <td style='padding:10px 14px;color:#111827;'>{$therapistName}</td>
                    </tr>
                    <tr>
                        <td style='padding:10px 14px;font-weight:600;color:#374151;background:#f9fafb;'>Leave Date</td>
                        <td style='padding:10px 14px;color:#111827;background:#f9fafb;'>{$leaveDate}</td>
                    </tr>
                    <tr style='background:#f5f3ff;'>
                        <td style='padding:10px 14px;font-weight:600;color:#7c3aed;'>Leave Type</td>
                        <td style='padding:10px 14px;color:#111827;'>{$leaveType}</td>
                    </tr>
                    <tr>
                        <td style='padding:10px 14px;font-weight:600;color:#374151;background:#f9fafb;'>Reason</td>
                        <td style='padding:10px 14px;color:#111827;background:#f9fafb;'>{$reason}</td>
                    </tr>
                </table>
                <p style='color:#6b7280;font-size:13px;'>Please log in to the admin panel to approve or reject this request.</p>
            ");

            MailHelper::sendAsync(
                $adminEmail,
                $adminName,
                "Leave Request from {$therapistName} — {$leaveDate}",
                $html,
                "New leave request from {$therapistName} for {$leaveDate} ({$leaveType}). Reason: {$reason}"
            );
            // ────────────────────────────────────────────────────────────

            return ApiResponse::success($leave, 'Leave applied', 201);
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function update(Request $request, $id)
    {
        $leave = TherapistLeave::with('therapist')->find($id);
        if (! $leave) {
            return ApiResponse::error('Leave not found', 404);
        }

        try {
            $this->validate($request, [
                'status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            ]);

            $newStatus = (string) $request->input('status');
            $leave->status = $newStatus;
            $leave->save();
            $leave->load('therapist');

            // ── Email therapist: leave approved or rejected ─────────────
            if (in_array($newStatus, ['approved', 'rejected'])) {
                $therapist     = $leave->therapist;
                $therapistEmail = $therapist?->email;
                $therapistName  = $therapist?->name ?? 'Therapist';
                $leaveDate      = $leave->leave_date;
                $leaveType      = ucwords(str_replace('_', ' ', $leave->leave_type));

                if ($therapistEmail) {
                    $isApproved  = $newStatus === 'approved';
                    $statusLabel = $isApproved ? 'Approved ✅' : 'Rejected ❌';
                    $statusColor = $isApproved ? '#16a34a' : '#dc2626';
                    $statusBg    = $isApproved ? '#f0fdf4' : '#fef2f2';
                    $message     = $isApproved
                        ? 'Your leave request has been <strong>approved</strong>. Enjoy your time off!'
                        : 'Your leave request has been <strong>rejected</strong>. Please contact the admin for more details.';

                    $html = MailHelper::template("
                        <p style='color:#111827;font-size:15px;margin-bottom:16px;'>
                            Hello <strong>{$therapistName}</strong>,
                        </p>
                        <p style='color:#374151;'>{$message}</p>
                        <div style='background:{$statusBg};border-left:4px solid {$statusColor};border-radius:6px;padding:16px 20px;margin:20px 0;'>
                            <p style='color:{$statusColor};font-weight:700;font-size:16px;margin:0 0 8px;'>{$statusLabel}</p>
                            <p style='color:#374151;margin:4px 0;'><strong>Date:</strong> {$leaveDate}</p>
                            <p style='color:#374151;margin:4px 0;'><strong>Type:</strong> {$leaveType}</p>
                        </div>
                        <p style='color:#6b7280;font-size:13px;'>If you have any questions, please contact the administration.</p>
                    ");

                    MailHelper::sendAsync(
                        $therapistEmail,
                        $therapistName,
                        "Leave Request {$statusLabel} — {$leaveDate}",
                        $html,
                        "Your leave request for {$leaveDate} ({$leaveType}) has been {$newStatus}."
                    );
                }
            }
            // ────────────────────────────────────────────────────────────

            return ApiResponse::success($leave, 'Leave updated');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function destroy($id)
    {
        $leave = TherapistLeave::find($id);
        if (! $leave) {
            return ApiResponse::error('Leave not found', 404);
        }

        $myTherapistId = $this->myTherapistIdIfTherapist();
        if ($myTherapistId) {
            if ((int) $leave->therapist_id !== (int) $myTherapistId) {
                return ApiResponse::error('Forbidden', 403);
            }
            if ((string) $leave->status !== 'pending') {
                return ApiResponse::error('Only pending leave can be cancelled', 422);
            }
        }

        $leave->delete();
        return ApiResponse::success(null, 'Leave deleted');
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
}
