<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $table = 'store_settings';
    protected $primaryKey = 'setting_key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'setting_key',
        'setting_value',
        'updated_at',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    public static function get(string $key, $default = null): ?string
    {
        $setting = static::find($key);
        return $setting ? $setting->setting_value : $default;
    }

    public static function allSettings(): array
    {
        return static::pluck('setting_value', 'setting_key')->toArray();
    }
}
