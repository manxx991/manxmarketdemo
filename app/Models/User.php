<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'username',
        'phone',
        'password',
        'role',
        'credit_limit',
        'is_active',
        'created_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'cashier_id');
    }

    public function customerTransactions()
    {
        return $this->hasMany(Transaction::class, 'customer_id');
    }

    public function receivables()
    {
        return $this->hasMany(Receivable::class, 'customer_id');
    }

    public function receivedPayments()
    {
        return $this->hasMany(ReceivablePayment::class, 'received_by');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'user_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeRole($query, $role)
    {
        return $query->where('role', $role);
    }
}
