<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapistPayroll extends Model
{
    protected $table = 'therapist_payroll';

    protected $fillable = [
        'therapist_id',
        'month',
        'present_days',
        'leave_days',
        'holiday_days',
        'absent_days',
        'total_sessions',
        'overtime_sessions',
        'salary_amount',
        'deduction_amount',
        'net_salary',
        'paid_at',
    ];

    protected $casts = [
        'therapist_id' => 'integer',
        'month' => 'date',
        'present_days' => 'integer',
        'leave_days' => 'integer',
        'holiday_days' => 'integer',
        'absent_days' => 'integer',
        'total_sessions' => 'integer',
        'overtime_sessions' => 'integer',
        'salary_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function therapist()
    {
        return $this->belongsTo(Therapist::class);
    }
}

