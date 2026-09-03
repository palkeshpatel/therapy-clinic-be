import re

file_path = 'app/Http/Controllers/Api/V1/ReportController.php'
with open(file_path, 'r') as f:
    content = f.read()

# For Patient Wise
content = content.replace(
    "'session_amounts' => [],",
    "'session_amounts' => [],\n                        'raw_sessions' => [],"
)

content = content.replace(
    "$grouped[$patientId]['session_amounts'][$amt] = true;\n                }",
    "$grouped[$patientId]['session_amounts'][$amt] = true;\n                }\n                $grouped[$patientId]['raw_sessions'][] = [\n                    'date' => $schedule->date,\n                    'status' => $schedule->status,\n                    'therapy' => $schedule->therapy ? $schedule->therapy->name : '-',\n                    'therapist' => $schedule->therapist ? $schedule->therapist->therapist_name : '-',\n                    'amount' => round($row['session_amount'], 2)\n                ];"
)

# For Therapist Wise
content = content.replace(
    "$grouped[$therapistId]['session_amounts'][$amt] = true;\n                }",
    "$grouped[$therapistId]['session_amounts'][$amt] = true;\n                }\n                $grouped[$therapistId]['raw_sessions'][] = [\n                    'date' => $schedule->date,\n                    'status' => $schedule->status,\n                    'patient' => $schedule->patient ? $schedule->patient->patient_name : '-',\n                    'therapy' => $schedule->therapy ? $schedule->therapy->name : '-',\n                    'amount' => round($row['session_amount'], 2)\n                ];"
)

# For Therapy Wise
content = content.replace(
    "$grouped[$therapyId]['session_amounts'][$amt] = true;\n                }",
    "$grouped[$therapyId]['session_amounts'][$amt] = true;\n                }\n                $grouped[$therapyId]['raw_sessions'][] = [\n                    'date' => $schedule->date,\n                    'status' => $schedule->status,\n                    'patient' => $schedule->patient ? $schedule->patient->patient_name : '-',\n                    'therapist' => $schedule->therapist ? $schedule->therapist->therapist_name : '-',\n                    'amount' => round($row['session_amount'], 2)\n                ];"
)

with open(file_path, 'w') as f:
    f.write(content)
print("Updated ReportController.php with raw_sessions")
