<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WholesaleRule extends Model
{
    protected $table = 'wholesale_rules';
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'rule_type',
        'min_qty',
        'min_amount',
        'discount_type',
        'discount_value',
        'is_active',
        'created_at',
    ];

    protected $casts = [
        'min_qty' => 'integer',
        'min_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
