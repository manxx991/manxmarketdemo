<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receivable extends Model
{
    protected $table = 'receivables';
    public $timestamps = false;

    protected $fillable = [
        'transaction_id',
        'customer_id',
        'total_debt',
        'total_paid',
        'remaining_debt',
        'due_date',
        'status',
        'created_at',
    ];

    protected $casts = [
        'total_debt' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'remaining_debt' => 'decimal:2',
        'due_date' => 'date',
        'created_at' => 'datetime',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function payments()
    {
        return $this->hasMany(ReceivablePayment::class, 'receivable_id');
    }
}
