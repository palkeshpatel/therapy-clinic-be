<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapistSchedule extends Model
{
    protected $table = 'therapist_schedule';

    protected $fillable = [
        'therapist_id',
        'date',
        'slot_id',
        'status',
    ];

    protected $casts = [
        'therapist_id' => 'integer',
        'slot_id' => 'integer',
        'date' => 'date',
    ];

    public function therapist()
    {
        return $this->belongsTo(Therapist::class);
    }

    public function slot()
    {
        return $this->belongsTo(TimeSlot::class, 'slot_id');
    }
}

