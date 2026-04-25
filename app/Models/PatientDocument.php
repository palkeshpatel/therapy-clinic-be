<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDocument extends Model
{
    protected $table = 'patient_documents';

    protected $fillable = [
        'patient_id',
        'document_type',
        'file_path',
        'uploaded_at',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}

