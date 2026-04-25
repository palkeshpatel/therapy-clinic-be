<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapistPayroll extends Model
{
    protected $table = 'therapist_payroll';

    protected $fillable = [
        'therapist_id',
        'month',
        'total_sessions',
        'overtime_sessions',
        'salary_amount',
        'paid_at',
    ];

    protected $casts = [
        'therapist_id' => 'integer',
        'month' => 'date',
        'total_sessions' => 'integer',
        'overtime_sessions' => 'integer',
        'salary_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function therapist()
    {
        return $this->belongsTo(Therapist::class);
    }
}

