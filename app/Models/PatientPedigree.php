<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientPedigree extends Model
{
    protected $fillable = [
        'patient_intake_id',
        'family_data'
    ];

    protected $casts = [
        'family_data' => 'array'
    ];

    public function patientIntake()
    {
        return $this->belongsTo(PatientIntake::class);
    }
}
