<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientMedicalRecord extends Model
{
    protected $table = 'patient_medical_records';

    protected $fillable = [
        'patient_id',
        'diagnosis',
        'notes',
    ];

    protected $casts = [
        'patient_id' => 'integer',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}

