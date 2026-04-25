<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicSetting extends Model
{
    protected $table = 'clinic_settings';

    protected $fillable = [
        'clinic_id',
        'setting_key',
        'setting_value',
    ];

    protected $casts = [
        'clinic_id' => 'integer',
    ];

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }
}

