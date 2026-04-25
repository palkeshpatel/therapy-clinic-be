<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WaitingList extends Model
{
    protected $table = 'waiting_list';

    protected $fillable = [
        'patient_id',
        'therapy_id',
        'requested_date',
        'priority',
        'status',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'therapy_id' => 'integer',
        'priority' => 'integer',
        'requested_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }
}

