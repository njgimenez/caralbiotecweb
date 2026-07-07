<?php

namespace Caral\Modules\Checkout\Services;

class IzipayService
{
    public static function sdkUrl(): string
    {
        $env = self::env();
        return $env === 'production'
            ? 'https://checkout.izipay.pe/payments/v1/js/index.js'
            : 'https://sandbox-checkout.izipay.pe/payments/v1/js/index.js';
    }

    public static function tokenEndpoint(): string
    {
        $customUrl = trim((string)($_ENV['IZIPAY_TOKEN_ENDPOINT'] ?? ''));
        if ($customUrl !== '') {
            return $customUrl;
        }

        return self::env() === 'production'
            ? 'https://api-pw.izipay.pe/security/v1/Token/Generate'
            : 'https://sandbox-api-pw.izipay.pe/security/v1/Token/Generate';
    }

    public static function isDemoMode(): bool
    {
        $value = strtolower((string)($_ENV['IZIPAY_DEMO_MODE'] ?? 'true'));
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    public static function publicConfig(array $tokenContext = []): array
    {
        $config = [
            'env' => self::env(),
            'sdkUrl' => self::sdkUrl(),
            'merchantCode' => (string)($_ENV['IZIPAY_MERCHANT_CODE'] ?? ''),
            'tokenSession' => (string)($_ENV['IZIPAY_TOKEN_SESSION'] ?? ''),
            'keyRSA' => (string)($_ENV['IZIPAY_KEY_RSA'] ?? ''),
            'demoMode' => self::isDemoMode(),
        ];

        if (!$config['demoMode'] && $config['tokenSession'] === '' && $tokenContext !== []) {
            $config['tokenSession'] = self::generateTokenSession($tokenContext);
        }

        return $config;
    }

    public static function generateTokenSession(array $context): string
    {
        $merchantCode = trim((string)($_ENV['IZIPAY_MERCHANT_CODE'] ?? ''));
        $apiKey = trim((string)($_ENV['IZIPAY_API_KEY'] ?? ''));

        if ($merchantCode === '') {
            throw new \RuntimeException('IZIPAY_MERCHANT_CODE no esta configurado.');
        }

        if ($apiKey === '') {
            throw new \RuntimeException('IZIPAY_API_KEY no esta configurado para generar el token de sesion.');
        }

        $transactionId = (string)($context['transactionId'] ?? '');
        $orderNumber = (string)($context['orderNumber'] ?? '');
        $amount = number_format((float)($context['amount'] ?? 0), 2, '.', '');

        if ($transactionId === '' || $orderNumber === '' || (float)$amount <= 0) {
            throw new \RuntimeException('Faltan datos de transaccion para generar el token de sesion Izipay.');
        }

        $payload = [
            'requestSource' => (string)($_ENV['IZIPAY_REQUEST_SOURCE'] ?? 'ECOMMERCE'),
            'merchantCode' => $merchantCode,
            'orderNumber' => $orderNumber,
            'publicKey' => $apiKey,
            'amount' => $amount,
        ];

        $ch = curl_init(self::tokenEndpoint());
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'transactionId: ' . $transactionId,
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \RuntimeException('No se pudo conectar con Izipay: ' . $curlError);
        }

        $data = json_decode((string)$response, true);
        if (!is_array($data)) {
            throw new \RuntimeException('Respuesta invalida de Izipay al generar token de sesion.');
        }

        $token = $data['response']['token'] ?? $data['token'] ?? null;
        if ($httpCode < 200 || $httpCode >= 300 || !is_string($token) || $token === '') {
            $message = $data['message'] ?? $data['messageUser'] ?? 'No se pudo generar el token de sesion Izipay.';
            throw new \RuntimeException($message . ' HTTP ' . $httpCode);
        }

        return $token;
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

    private static function env(): string
    {
        return strtolower((string)($_ENV['IZIPAY_ENV'] ?? 'sandbox')) === 'production' ? 'production' : 'sandbox';
    }
}
