<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailySchedule extends Model
{
    protected $table = 'daily_schedule';

    protected $fillable = [
        'date',
        'slot_id',
        'patient_id',
        'therapist_id',
        'therapy_id',
        'status',
        'created_by',
    ];

    protected $casts = [
        'date' => 'date',
        'slot_id' => 'integer',
        'patient_id' => 'integer',
        'therapist_id' => 'integer',
        'therapy_id' => 'integer',
        'created_by' => 'integer',
    ];

    public function slot()
    {
        return $this->belongsTo(TimeSlot::class, 'slot_id');
    }

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

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

