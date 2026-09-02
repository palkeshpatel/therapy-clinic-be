import re

file_path = 'app/Models/Patient.php'
with open(file_path, 'r') as f:
    content = f.read()

# Add to fillable
content = content.replace(
    "'referral_percentage',\n    ];",
    "'referral_percentage',\n        'schedule_type',\n        'selected_days',\n    ];"
)

# Add to casts
content = content.replace(
    "'joining_date' => 'date',\n    ];",
    "'joining_date' => 'date',\n        'selected_days' => 'array',\n    ];"
)

with open(file_path, 'w') as f:
    f.write(content)
print("Patient model updated")
