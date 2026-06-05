<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Therapist extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'specialization',
        'phone',
        'email',
        'joined_date',
        'status',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'joined_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(TherapistDocument::class);
    }

    public function patientTherapies()
    {
        return $this->hasMany(PatientTherapy::class);
    }

    public function attendance()
    {
        return $this->hasMany(TherapistAttendance::class);
    }

    public function leaves()
    {
        return $this->hasMany(TherapistLeave::class);
    }

    public function salaryModels()
    {
        return $this->hasMany(SalaryModel::class);
    }

    public function payrolls()
    {
        return $this->hasMany(TherapistPayroll::class);
    }

    public function schedule()
    {
        return $this->hasMany(TherapistSchedule::class);
    }

    public function dailySchedules()
    {
        return $this->hasMany(DailySchedule::class);
    }

    public function sessions()
    {
        return $this->hasMany(TherapySession::class);
    }
}

