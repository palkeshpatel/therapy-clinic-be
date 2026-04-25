<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapistAttendance extends Model
{
    protected $table = 'therapist_attendance';

    protected $fillable = [
        'therapist_id',
        'date',
        'check_in',
        'check_out',
    ];

    protected $casts = [
        'therapist_id' => 'integer',
        'date' => 'date',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function therapist()
    {
        return $this->belongsTo(Therapist::class);
    }
}

