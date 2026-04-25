<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalaryModel extends Model
{
    protected $table = 'salary_models';

    protected $fillable = [
        'therapist_id',
        'salary_type',
        'fixed_salary',
        'per_session_rate',
        'effective_from',
    ];

    protected $casts = [
        'therapist_id' => 'integer',
        'fixed_salary' => 'decimal:2',
        'per_session_rate' => 'decimal:2',
        'effective_from' => 'date',
    ];

    public function therapist()
    {
        return $this->belongsTo(Therapist::class);
    }
}

