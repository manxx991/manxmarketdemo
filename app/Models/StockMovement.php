<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $table = 'stock_movements';
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'user_id',
        'reference_id',
        'movement_type',
        'qty_change',
        'stock_before',
        'stock_after',
        'notes',
        'created_at',
    ];

    protected $casts = [
        'qty_change' => 'integer',
        'stock_before' => 'integer',
        'stock_after' => 'integer',
        'created_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
