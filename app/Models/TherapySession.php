<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapySession extends Model
{
    protected $table = 'sessions';

    protected $fillable = [
        'patient_id',
        'therapist_id',
        'therapy_id',
        'slot_id',
        'session_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'therapist_id' => 'integer',
        'therapy_id' => 'integer',
        'slot_id' => 'integer',
        'session_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function therapist()
    {
        return $this->belongsTo(Therapist::class);
    }

    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }

    public function slot()
    {
        return $this->belongsTo(TimeSlot::class, 'slot_id');
    }
}

