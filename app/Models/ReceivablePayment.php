<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReceivablePayment extends Model
{
    protected $table = 'receivable_payments';
    public $timestamps = false;

    protected $fillable = [
        'receivable_id',
        'payment_number',
        'received_by',
        'payment_date',
        'amount_paid',
        'payment_method',
        'reference_note',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount_paid' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function receivable()
    {
        return $this->belongsTo(Receivable::class, 'receivable_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
