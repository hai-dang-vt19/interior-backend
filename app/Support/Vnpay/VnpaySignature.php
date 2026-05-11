<?php

declare(strict_types=1);

namespace App\Support\Vnpay;

/**
 * Tạo và kiểm tra chữ ký HMAC SHA512 theo cách VNPay đề xuất (sort key vnp_*).
 */
final class VnpaySignature
{
    /**
     * Trả về URL redirect VNPay (đã gắn vnp_SecureHash).
     *
     * @param  array<string, string|int|float|null>  $inputData
     */
    public static function buildPaymentRedirectUrl(string $paymentBaseUrl, array $inputData, string $hashSecret): string
    {
        $normalized = self::normalizeForSigning($inputData);
        ksort($normalized);
        $query = self::buildQueryString($normalized);
        [, $secureHash] = self::buildQueryAndHash($normalized, $hashSecret);

        return rtrim($paymentBaseUrl, '?&').'?'.$query.'&vnp_SecureHash='.$secureHash;
    }

    /**
     * @param  array<string, string|null>  $vnpPayload  Các tham số vnp_* (có thể có vnp_SecureHash)
     */
    public static function verify(array $vnpPayload, ?string $secureHashSent, string $hashSecret): bool
    {
        if ($secureHashSent === null || $secureHashSent === '' || $hashSecret === '') {
            return false;
        }

        $copy = $vnpPayload;
        unset($copy['vnp_SecureHash']);
        $normalized = self::normalizeForSigning($copy);
        [, $expectedHash] = self::buildQueryAndHash($normalized, $hashSecret);

        return hash_equals(strtoupper($expectedHash), strtoupper($secureHashSent));
    }

    /**
     * @param  array<string, string|int|float|null>  $inputData
     * @return array<string, string>
     */
    private static function normalizeForSigning(array $inputData): array
    {
        $out = [];
        foreach ($inputData as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $out[(string) $key] = (string) $value;
        }

        return $out;
    }

    /** @param array<string, string> $sorted */
    private static function buildQueryString(array $sorted): string
    {
        $parts = [];
        foreach ($sorted as $key => $value) {
            $parts[] = urlencode($key).'='.urlencode($value);
        }

        return implode('&', $parts);
    }

    /**
     * @param  array<string, string>  $inputData  không rỗng value
     * @return array{0: string hashData, 1: string secureHash}
     */
    private static function buildQueryAndHash(array $inputData, string $hashSecret): array
    {
        ksort($inputData);
        $hashData = '';
        $first = false;
        foreach ($inputData as $key => $value) {
            if ($first) {
                $hashData .= '&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashData .= urlencode($key).'='.urlencode($value);
                $first = true;
            }
        }

        return [$hashData, hash_hmac('sha512', $hashData, $hashSecret)];
    }
}
