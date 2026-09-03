import re

file_path = 'app/Http/Controllers/Api/V1/ReportController.php'
with open(file_path, 'r') as f:
    content = f.read()

# Add use statement for DailySchedule if missing
if 'use App\Models\DailySchedule;' not in content:
    content = content.replace(
        'use App\\Models\\TherapySession;',
        'use App\\Models\\TherapySession;\nuse App\\Models\\DailySchedule;\nuse App\\Models\\PatientTherapy;'
    )

methods = """
    private function getReportData($from, $to)
    {
        $schedules = DailySchedule::query()
            ->with(['patient', 'therapist', 'therapy', 'patient.therapies'])
            ->whereBetween('date', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->get();
            
        $data = [];
        
        foreach ($schedules as $schedule) {
            $sessionAmount = 0;
            $billingType = 'session';
            
            // Find the matching patient therapy
            $patientTherapy = null;
            if ($schedule->patient && $schedule->therapy_id) {
                $patientTherapy = $schedule->patient->therapies->firstWhere('therapy_id', $schedule->therapy_id);
            }
            
            if ($patientTherapy) {
                $billingType = $patientTherapy->billing_type;
                if ($billingType === 'monthly') {
                    $days = $patientTherapy->number_of_days ?: 26;
                    $sessionAmount = $patientTherapy->fee / $days;
                } else {
                    $sessionAmount = $patientTherapy->fee;
                }
            }
            
            $data[] = [
                'schedule' => $schedule,
                'session_amount' => $sessionAmount,
                'billing_type' => $billingType
            ];
        }
        
        return $data;
    }

    public function patientWise(Request $request)
    {
        try {
            $this->validate($request, [
                'from' => ['required', 'date'],
                'to' => ['required', 'date'],
            ]);

            $data = $this->getReportData($request->input('from'), $request->input('to'));
            
            $grouped = [];
            foreach ($data as $row) {
                $schedule = $row['schedule'];
                if (!$schedule->patient) continue;
                
                $patientId = $schedule->patient_id;
                if (!isset($grouped[$patientId])) {
                    $grouped[$patientId] = [
                        'patient_id' => $patientId,
                        'patient_name' => $schedule->patient->patient_name,
                        'total_sessions' => 0,
                        'total_amount' => 0,
                        'therapies' => [],
                        'session_amounts' => [],
                    ];
                }
                
                $grouped[$patientId]['total_sessions']++;
                $grouped[$patientId]['total_amount'] += $row['session_amount'];
                
                if ($schedule->therapy) {
                    $grouped[$patientId]['therapies'][$schedule->therapy->name] = true;
                }
                
                if ($row['session_amount'] > 0) {
                    $amt = round($row['session_amount'], 2);
                    $grouped[$patientId]['session_amounts'][$amt] = true;
                }
            }
            
            $result = array_values(array_map(function($g) {
                $g['therapies'] = array_keys($g['therapies']);
                $g['session_amounts'] = array_keys($g['session_amounts']);
                $g['total_amount'] = round($g['total_amount'], 2);
                return $g;
            }, $grouped));

            return ApiResponse::success($result, 'OK');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function therapistWise(Request $request)
    {
        try {
            $this->validate($request, [
                'from' => ['required', 'date'],
                'to' => ['required', 'date'],
            ]);

            $data = $this->getReportData($request->input('from'), $request->input('to'));
            
            $grouped = [];
            foreach ($data as $row) {
                $schedule = $row['schedule'];
                if (!$schedule->therapist) continue;
                
                $therapistId = $schedule->therapist_id;
                if (!isset($grouped[$therapistId])) {
                    $grouped[$therapistId] = [
                        'therapist_id' => $therapistId,
                        'therapist_name' => $schedule->therapist->therapist_name,
                        'total_sessions' => 0,
                        'total_amount' => 0,
                        'therapies' => [],
                        'patients' => [],
                        'session_amounts' => [],
                    ];
                }
                
                $grouped[$therapistId]['total_sessions']++;
                $grouped[$therapistId]['total_amount'] += $row['session_amount'];
                
                if ($schedule->therapy) {
                    $grouped[$therapistId]['therapies'][$schedule->therapy->name] = true;
                }
                if ($schedule->patient) {
                    $grouped[$therapistId]['patients'][$schedule->patient->patient_name] = true;
                }
                
                if ($row['session_amount'] > 0) {
                    $amt = round($row['session_amount'], 2);
                    $grouped[$therapistId]['session_amounts'][$amt] = true;
                }
            }
            
            $result = array_values(array_map(function($g) {
                $g['therapies'] = array_keys($g['therapies']);
                $g['patients'] = count(array_keys($g['patients']));
                $g['session_amounts'] = array_keys($g['session_amounts']);
                $g['total_amount'] = round($g['total_amount'], 2);
                return $g;
            }, $grouped));

            return ApiResponse::success($result, 'OK');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }

    public function therapyWise(Request $request)
    {
        try {
            $this->validate($request, [
                'from' => ['required', 'date'],
                'to' => ['required', 'date'],
            ]);

            $data = $this->getReportData($request->input('from'), $request->input('to'));
            
            $grouped = [];
            foreach ($data as $row) {
                $schedule = $row['schedule'];
                if (!$schedule->therapy) continue;
                
                $therapyId = $schedule->therapy_id;
                if (!isset($grouped[$therapyId])) {
                    $grouped[$therapyId] = [
                        'therapy_id' => $therapyId,
                        'therapy_name' => $schedule->therapy->name,
                        'total_sessions' => 0,
                        'total_amount' => 0,
                        'patients' => [],
                        'session_amounts' => [],
                    ];
                }
                
                $grouped[$therapyId]['total_sessions']++;
                $grouped[$therapyId]['total_amount'] += $row['session_amount'];
                
                if ($schedule->patient) {
                    $grouped[$therapyId]['patients'][$schedule->patient_id] = true;
                }
                
                if ($row['session_amount'] > 0) {
                    $amt = round($row['session_amount'], 2);
                    $grouped[$therapyId]['session_amounts'][$amt] = true;
                }
            }
            
            $result = array_values(array_map(function($g) {
                $g['patients'] = count(array_keys($g['patients']));
                $g['session_amounts'] = array_keys($g['session_amounts']);
                $g['total_amount'] = round($g['total_amount'], 2);
                return $g;
            }, $grouped));

            return ApiResponse::success($result, 'OK');
        } catch (ValidationException $e) {
            return ApiResponse::error('Validation failed', 422, $e->errors());
        }
    }
}
"""

content = content.replace("}\n", methods, 1)

with open(file_path, 'w') as f:
    f.write(content)
print("Updated ReportController.php")
