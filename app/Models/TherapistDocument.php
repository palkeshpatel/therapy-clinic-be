<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapistDocument extends Model
{
    protected $table = 'therapist_documents';

    protected $fillable = [
        'therapist_id',
        'document_type',
        'file_path',
    ];

    protected $casts = [
        'therapist_id' => 'integer',
    ];

    public function therapist()
    {
        return $this->belongsTo(Therapist::class);
    }
}

