import re

file_path = 'app/Http/Controllers/Api/V1/PatientController.php'
with open(file_path, 'r') as f:
    content = f.read()

# Update validation rules in store and update
rule_str = "'referral_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],"
new_rule_str = "'referral_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],\n                'schedule_type' => ['nullable', 'string'],\n                'selected_days' => ['nullable', 'array'],"
content = content.replace(rule_str, new_rule_str)

# Update payload assignment in store and update
create_str = "$patient = Patient::create([\n                'patient_name' => $request->input('patient_name'),"
new_create_str = "$patient = Patient::create([\n                'patient_name' => $request->input('patient_name'),\n                'schedule_type' => $request->input('schedule_type'),\n                'selected_days' => $request->input('selected_days'),"
content = content.replace(create_str, new_create_str)

update_str = "$patient->update([\n                'patient_name' => $request->input('patient_name', $patient->patient_name),"
new_update_str = "$patient->update([\n                'patient_name' => $request->input('patient_name', $patient->patient_name),\n                'schedule_type' => $request->input('schedule_type', $patient->schedule_type),\n                'selected_days' => $request->input('selected_days', $patient->selected_days),"
content = content.replace(update_str, new_update_str)

with open(file_path, 'w') as f:
    f.write(content)
print("PatientController updated")
