<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $fillable = [
        'clinic_name',
        'address',
        'phone',
        'email',
        'gst_number',
    ];

    public function settings()
    {
        return $this->hasMany(ClinicSetting::class);
    }
}

