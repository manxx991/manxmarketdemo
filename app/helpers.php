<?php

use App\Models\StoreSetting;
use App\Models\User;
use App\Services\QrisService;
use Illuminate\Support\Facades\Auth;

if (!function_exists('formatRupiah')) {
    function formatRupiah(float|int|null $amount): string
    {
        return 'Rp ' . number_format((float)($amount ?? 0), 0, ',', '.');
    }
}

if (!function_exists('formatRp')) {
    function formatRp(float|int|null $amount): string
    {
        return formatRupiah($amount);
    }
}

if (!function_exists('getStoreSettings')) {
    function getStoreSettings(): array
    {
        try {
            return StoreSetting::allSettings();
        } catch (\Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('storeSetting')) {
    function storeSetting(string $key, $default = null): ?string
    {
        try {
            return StoreSetting::get($key, $default);
        } catch (\Throwable $e) {
            return $default;
        }
    }
}

if (!function_exists('getCurrentUser')) {
    function getCurrentUser(): ?User
    {
        return Auth::user();
    }
}

if (!function_exists('crc16_ccitt')) {
    function crc16_ccitt(string $str): string
    {
        return QrisService::crc16Ccitt($str);
    }
}

if (!function_exists('makeDynamicQris')) {
    function makeDynamicQris(string $staticQris, float|int $amount): string
    {
        return QrisService::makeDynamicQris($staticQris, $amount);
    }
}
