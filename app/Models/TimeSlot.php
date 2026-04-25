<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeSlot extends Model
{
    protected $table = 'time_slots';

    protected $fillable = [
        'start_time',
        'end_time',
        'duration_minutes',
        'is_active',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
    ];

    public function therapistSchedules()
    {
        return $this->hasMany(TherapistSchedule::class, 'slot_id');
    }

    public function dailySchedules()
    {
        return $this->hasMany(DailySchedule::class, 'slot_id');
    }

    public function sessions()
    {
        return $this->hasMany(TherapySession::class, 'slot_id');
    }
}

