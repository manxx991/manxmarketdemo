<?php

namespace App\Services;

class QrisService
{
    public static function crc16Ccitt(string $str): string
    {
        $crc = 0xFFFF;
        $len = strlen($str);
        for ($c = 0; $c < $len; $c++) {
            $crc ^= ord($str[$c]) << 8;
            for ($i = 0; $i < 8; $i++) {
                if ($crc & 0x8000) {
                    $crc = (($crc << 1) ^ 0x1021) & 0xFFFF;
                } else {
                    $crc = ($crc << 1) & 0xFFFF;
                }
            }
        }
        return strtoupper(str_pad(dechex($crc), 4, '0', STR_PAD_LEFT));
    }

    public static function makeDynamicQris(string $staticQris, float|int $amount): string
    {
        $str = trim($staticQris);
        if (empty($str)) {
            return '';
        }

        $last6304 = strrpos($str, '6304');
        if ($last6304 !== false) {
            $str = substr($str, 0, $last6304);
        }

        $str = str_replace('010211', '010212', $str);

        $amountVal = (string)round($amount);
        $amountTag = '54' . str_pad(strlen($amountVal), 2, '0', STR_PAD_LEFT) . $amountVal;

        if (strpos($str, '5802ID') !== false) {
            $parts = explode('5802ID', $str, 2);
            $beforeCountry = $parts[0];
            if (strpos($beforeCountry, '5303360') !== false) {
                $subParts = explode('5303360', $beforeCountry, 2);
                $beforeCountry = $subParts[0] . '5303360';
            }
            $payload = $beforeCountry . $amountTag . '5802ID' . $parts[1];
        } else {
            $payload = $str . $amountTag;
        }

        $payload .= '6304';
        $crcHex = self::crc16Ccitt($payload);

        return $payload . $crcHex;
    }

    public static function parseQrisInfo(string $qris): array
    {
        $info = [
            'merchant_name' => '',
            'city' => '',
            'nmid' => '',
            'provider' => 'QRIS Nasional (Standar ASPI)',
            'is_valid_qris' => false,
            'is_dynamic' => false
        ];
        $str = trim($qris);
        if (strpos($str, '000201') !== 0) {
            return $info;
        }

        $info['is_valid_qris'] = true;
        $info['is_dynamic'] = (strpos($str, '010212') !== false);

        if (stripos($str, 'SHOPEE') !== false || stripos($str, 'AIRPAY') !== false) {
            $info['provider'] = 'ShopeePay / Shopee Partner';
        } elseif (stripos($str, 'DANA') !== false) {
            $info['provider'] = 'DANA Bisnis';
        } elseif (stripos($str, 'GOPAY') !== false || stripos($str, 'GOJEK') !== false) {
            $info['provider'] = 'GoPay Merchant';
        } elseif (stripos($str, 'BCA') !== false) {
            $info['provider'] = 'BCA Merchant';
        } elseif (stripos($str, 'MANDIRI') !== false) {
            $info['provider'] = 'Livin Merchant / Mandiri';
        }

        return $info;
    }
}
