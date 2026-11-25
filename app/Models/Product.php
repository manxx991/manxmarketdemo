<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $table = 'products';
    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'sku',
        'barcode',
        'name',
        'image_path',
        'unit',
        'cost_price',
        'retail_price',
        'stock',
        'min_stock',
        'is_active',
        'created_at',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): string
    {
        if ($this->image_path && str_starts_with($this->image_path, 'assets/produk/')
            && Storage::disk('product_assets')->exists($this->image_path)) {
            return asset($this->image_path);
        }

        if ($this->image_path && Storage::disk('public')->exists($this->image_path)) {
            return asset('storage/' . $this->image_path);
        }

        return asset('assets/produk/' . strtolower($this->sku) . '.webp');
    }

    protected $casts = [
        'cost_price' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'stock' => 'integer',
        'min_stock' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function wholesaleRules()
    {
        return $this->hasMany(WholesaleRule::class, 'product_id');
    }

    public function activeWholesaleRule()
    {
        return $this->hasOne(WholesaleRule::class, 'product_id')->where('is_active', 1);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'product_id');
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'product_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
