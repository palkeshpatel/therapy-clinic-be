import glob

files = glob.glob('database/migrations/*add_schedule_fields_to_patients_table.php')
if files:
    with open(files[0], 'r') as f:
        content = f.read()
    
    content = content.replace(
        "Schema::table('patients', function (Blueprint $table) {\n            //\n        });",
        "Schema::table('patients', function (Blueprint $table) {\n            $table->string('schedule_type')->nullable();\n            $table->json('selected_days')->nullable();\n        });",
        1
    )
    
    content = content.replace(
        "Schema::table('patients', function (Blueprint $table) {\n            //\n        });",
        "Schema::table('patients', function (Blueprint $table) {\n            $table->dropColumn(['schedule_type', 'selected_days']);\n        });",
        1
    )
    
    with open(files[0], 'w') as f:
        f.write(content)
    
    print("Migration updated successfully.")
