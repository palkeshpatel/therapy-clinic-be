<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_name',
        'guardian_name',
        'phone',
        'email',
        'dob',
        'joining_date',
        'gender',
        'address',
        'notes',
        'default_billing_type',
        'status',
    ];

    protected $casts = [
        'dob' => 'date',
        'joining_date' => 'date',
    ];

    public function documents()
    {
        return $this->hasMany(PatientDocument::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(PatientMedicalRecord::class);
    }

    public function therapies()
    {
        return $this->hasMany(PatientTherapy::class);
    }

    public function sessions()
    {
        return $this->hasMany(TherapySession::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function dailySchedules()
    {
        return $this->hasMany(DailySchedule::class);
    }

    public function waitingListItems()
    {
        return $this->hasMany(WaitingList::class);
    }
}

