<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapistLeave extends Model
{
    protected $table = 'therapist_leaves';

    protected $fillable = [
        'therapist_id',
        'leave_date',
        'leave_type',
        'reason',
        'status',
    ];

    protected $casts = [
        'therapist_id' => 'integer',
        'leave_date' => 'date',
    ];

    public function therapist()
    {
        return $this->belongsTo(Therapist::class);
    }
}

