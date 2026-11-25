<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $table = 'transaction_items';
    public $timestamps = false;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'product_name_snapshot',
        'sku_snapshot',
        'qty',
        'cost_price_snapshot',
        'unit_price',
        'discount_per_item',
        'subtotal_item',
    ];

    protected $casts = [
        'qty' => 'integer',
        'cost_price_snapshot' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'discount_per_item' => 'decimal:2',
        'subtotal_item' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
