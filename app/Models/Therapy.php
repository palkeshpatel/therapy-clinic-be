<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Therapy extends Model
{
    protected $fillable = [
        'therapy_name',
        'description',
        'default_price',
        'status',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
    ];

    public function patientTherapies()
    {
        return $this->hasMany(PatientTherapy::class);
    }

    public function sessions()
    {
        return $this->hasMany(TherapySession::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function waitingListItems()
    {
        return $this->hasMany(WaitingList::class);
    }

    public function dailySchedules()
    {
        return $this->hasMany(DailySchedule::class);
    }
}

