<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    public $timestamps = false;

    protected $fillable = [
        'invoice_number',
        'cashier_id',
        'customer_id',
        'customer_name_manual',
        'transaction_date',
        'subtotal',
        'total_discount',
        'grand_total',
        'paid_amount',
        'change_amount',
        'payment_method',
        'payment_status',
        'void_by',
        'void_reason',
        'void_at',
        'created_at',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'void_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function cashier()
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function voidUser()
    {
        return $this->belongsTo(User::class, 'void_by');
    }

    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
    }

    public function receivable()
    {
        return $this->hasOne(Receivable::class, 'transaction_id');
    }
}
