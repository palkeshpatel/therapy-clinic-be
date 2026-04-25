<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientTherapy extends Model
{
    protected $table = 'patient_therapies';

    protected $fillable = [
        'patient_id',
        'therapy_id',
        'therapist_id',
        'billing_type',
        'fee',
        'start_date',
        'end_date',
        'status',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'therapy_id' => 'integer',
        'therapist_id' => 'integer',
        'fee' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    public function therapist()
    {
        return $this->belongsTo(Therapist::class);
    }
}

