<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $table = 'invoice_items';

    protected $fillable = [
        'invoice_id',
        'therapy_id',
        'description',
        'quantity',
        'amount',
    ];

    protected $casts = [
        'invoice_id' => 'integer',
        'therapy_id' => 'integer',
        'quantity' => 'integer',
        'amount' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function therapy()
    {
        return $this->belongsTo(Therapy::class);
    }
}

