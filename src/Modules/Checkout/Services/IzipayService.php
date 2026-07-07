<?php

namespace Caral\Modules\Checkout\Services;

class IzipayService
{
    public static function sdkUrl(): string
    {
        $env = strtolower((string)($_ENV['IZIPAY_ENV'] ?? 'sandbox'));
        return $env === 'production'
            ? 'https://checkout.izipay.pe/payments/v1/js/index.js'
            : 'https://sandbox-checkout.izipay.pe/payments/v1/js/index.js';
    }

    public static function isDemoMode(): bool
    {
        $value = strtolower((string)($_ENV['IZIPAY_DEMO_MODE'] ?? 'true'));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    public static function publicConfig(): array
    {
        return [
            'env' => strtolower((string)($_ENV['IZIPAY_ENV'] ?? 'sandbox')),
            'sdkUrl' => self::sdkUrl(),
            'merchantCode' => (string)($_ENV['IZIPAY_MERCHANT_CODE'] ?? ''),
            'tokenSession' => (string)($_ENV['IZIPAY_TOKEN_SESSION'] ?? ''),
            'keyRSA' => (string)($_ENV['IZIPAY_KEY_RSA'] ?? ''),
            'demoMode' => self::isDemoMode(),
        ];
    }

    public static function decodeResponse(string $raw): array
    {
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new \RuntimeException('La respuesta de Izipay no es valida.');
        }

        return $data;
    }

    public static function isApproved(array $response): bool
    {
        $code = (string)($response['code'] ?? '');
        if (in_array($code, ['00', '000'], true)) {
            return true;
        }

        $order = $response['response']['order'][0] ?? [];
        return strcasecmp((string)($order['stateMessage'] ?? ''), 'Autorizado') === 0;
    }

    public static function operationId(array $response): string
    {
        $order = $response['response']['order'][0] ?? [];
        return (string)(
            $order['uniqueId']
            ?? $order['referenceNumber']
            ?? $response['transactionId']
            ?? 'IZIPAY-DEMO-' . date('YmdHis')
        );
    }

    public static function orderNumber(array $response): ?string
    {
        $orderNumber = $response['response']['order'][0]['orderNumber'] ?? null;
        return is_string($orderNumber) && $orderNumber !== '' ? $orderNumber : null;
    }
}
