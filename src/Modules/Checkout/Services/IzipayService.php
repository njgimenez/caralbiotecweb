<?php

namespace Caral\Modules\Checkout\Services;

use Caral\Modules\Admin\Services\CompanySettingsService;

class IzipayService
{
    public static function mode(): string
    {
        if (method_exists(CompanySettingsService::class, 'izipayMode')) {
            return CompanySettingsService::izipayMode();
        }

        $mode = strtolower(trim((string)($_ENV['IZIPAY_MODE'] ?? 'test')));
        return in_array($mode, ['production', 'prod', 'live'], true) ? 'production' : 'test';
    }

    public static function publicKey(): string
    {
        return self::credential('PUBLIC_KEY');
    }

    public static function kryptonScriptUrl(): string
    {
        return (string)($_ENV['IZIPAY_KRYPTON_SCRIPT_URL'] ?? 'https://static.micuentaweb.pe/static/js/krypton-client/V4.0/stable/kr-payment-form.min.js');
    }

    public static function kryptonClassicCssUrl(): string
    {
        return (string)($_ENV['IZIPAY_KRYPTON_CSS_URL'] ?? 'https://static.micuentaweb.pe/static/js/krypton-client/V4.0/ext/classic.css');
    }

    public static function kryptonClassicJsUrl(): string
    {
        return (string)($_ENV['IZIPAY_KRYPTON_CLASSIC_URL'] ?? 'https://static.micuentaweb.pe/static/js/krypton-client/V4.0/ext/classic.js');
    }

    public static function createPaymentEndpoint(): string
    {
        return self::credential('CREATE_PAYMENT_ENDPOINT') ?: (string)($_ENV['IZIPAY_CREATE_PAYMENT_ENDPOINT'] ?? 'https://api.micuentaweb.pe/api-payment/V4/Charge/CreatePayment');
    }

    public static function createFormToken(array $order, array $items): array
    {
        self::assertCredentials();

        $body = [
            'amount' => (int)round(((float)$order['total']) * 100),
            'currency' => (string)($order['currency'] ?? 'PEN'),
            'orderId' => (string)$order['order_number'],
            'customer' => [
                'email' => (string)$order['customer_email'],
                'billingDetails' => [
                    'firstName' => self::firstName((string)$order['customer_name']),
                    'lastName' => self::lastName((string)$order['customer_name']),
                    'phoneNumber' => (string)($order['customer_phone'] ?? ''),
                    'identityType' => (string)($order['customer_document_type'] ?? 'DNI'),
                    'identityCode' => (string)($order['customer_document'] ?? '00000000'),
                    'address' => (string)($order['shipping_address'] ?? 'Recojo en tienda'),
                    'country' => 'PE',
                    'city' => (string)($order['shipping_city'] ?? 'Lima'),
                    'state' => (string)($order['shipping_district'] ?? $order['delivery_zone'] ?? 'Lima'),
                    'zipCode' => (string)($_ENV['IZIPAY_DEFAULT_ZIP_CODE'] ?? '15000'),
                ],
            ],
            'metadata' => [
                'orderId' => (string)$order['id'],
                'source' => 'caral-web',
                'environment' => self::mode(),
                'deliveryType' => (string)($order['delivery_type'] ?? ''),
                'courier' => (string)($order['courier'] ?? ''),
            ],
        ];

        if ($items !== []) {
            $body['shoppingCart'] = [
                'cartItemInfo' => array_map(static function (array $item): array {
                    return [
                        'productLabel' => (string)($item['product_name'] ?? $item['name'] ?? 'Producto'),
                        'productAmount' => (int)round(((float)($item['unit_price'] ?? $item['price_seen'] ?? 0)) * 100),
                        'productQty' => (int)($item['quantity'] ?? 1),
                    ];
                }, $items),
            ];
        }

        $ch = curl_init(self::createPaymentEndpoint());
        curl_setopt_array($ch, [
            CURLOPT_HEADER => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode(self::username() . ':' . self::password()),
                'Content-Type: application/json',
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($body, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            throw new \RuntimeException('No se pudo conectar con Izipay: ' . $curlError);
        }

        $response = json_decode((string)$rawResponse, true);
        if (!is_array($response)) {
            throw new \RuntimeException('Respuesta invalida de Izipay al crear el formToken.');
        }

        $formToken = $response['answer']['formToken'] ?? null;
        if ($httpCode < 200 || $httpCode >= 300 || !is_string($formToken) || $formToken === '') {
            $message = $response['answer']['errorMessage']
                ?? $response['answer']['detailedErrorMessage']
                ?? $response['message']
                ?? 'No se pudo crear el formToken de Izipay.';
            throw new \RuntimeException($message . ' HTTP ' . $httpCode);
        }

        return [
            'formToken' => $formToken,
            'request' => $body,
            'response' => $response,
            'environment' => self::mode(),
        ];
    }

    public static function validateFrontendHash(array $post): bool
    {
        return self::validateHashWithCandidates($post, self::hashCandidates());
    }

    public static function validateIpnHash(array $post): bool
    {
        return self::validateHashWithCandidates($post, self::hashCandidates());
    }

    public static function decodeAnswer(array $post): array
    {
        $raw = (string)($post['kr-answer'] ?? '');
        $answer = json_decode(str_replace('\/', '/', $raw), true);
        if (!is_array($answer)) {
            throw new \RuntimeException('La respuesta de Izipay no contiene un kr-answer valido.');
        }

        return $answer;
    }

    public static function orderStatus(array $answer): string
    {
        return strtoupper((string)($answer['orderStatus'] ?? ''));
    }

    public static function orderId(array $answer): string
    {
        return (string)($answer['orderDetails']['orderId'] ?? '');
    }

    public static function transactionUuid(array $answer): string
    {
        $transaction = $answer['transactions'][0] ?? [];
        return (string)($transaction['uuid'] ?? '');
    }

    public static function isPaid(array $answer): bool
    {
        return self::orderStatus($answer) === 'PAID';
    }

    private static function validateHashWithCandidates(array $post, array $keys): bool
    {
        if (empty($post['kr-answer']) || empty($post['kr-hash'])) {
            return false;
        }

        foreach ($keys as $key) {
            if (self::validateHash($post, $key)) {
                return true;
            }
        }

        self::logHashMismatch($post, $keys);
        return false;
    }

    private static function validateHash(array $post, string $key): bool
    {
        if ($key === '') {
            return false;
        }

        $receivedHash = strtolower((string)$post['kr-hash']);
        $rawAnswer = (string)$post['kr-answer'];
        $normalizedAnswer = str_replace('\/', '/', $rawAnswer);

        foreach (array_unique([$rawAnswer, $normalizedAnswer]) as $answer) {
            $calculated = hash_hmac('sha256', $answer, $key);
            if (hash_equals($calculated, $receivedHash)) {
                return true;
            }
        }

        return false;
    }

    private static function logHashMismatch(array $post, array $candidateKeys): void
    {
        $rawAnswer = (string)($post['kr-answer'] ?? '');
        $normalizedAnswer = str_replace('\/', '/', $rawAnswer);
        $answer = json_decode(str_replace('\/', '/', $rawAnswer), true);
        $orderId = is_array($answer) ? (string)($answer['orderDetails']['orderId'] ?? '') : '';

        error_log('[Izipay] Hash mismatch ' . json_encode([
            'orderId' => $orderId,
            'activeMode' => self::mode(),
            'krHashKey' => (string)($post['kr-hash-key'] ?? ''),
            'krHashAlgorithm' => (string)($post['kr-hash-algorithm'] ?? ''),
            'krAnswerType' => (string)($post['kr-answer-type'] ?? ''),
            'answerLength' => strlen($rawAnswer),
            'answerSha256Prefix' => substr(hash('sha256', $rawAnswer), 0, 12),
            'hashPrefix' => substr((string)($post['kr-hash'] ?? ''), 0, 12),
            'candidateKeys' => array_keys($candidateKeys),
            'calculatedHashPrefixes' => self::calculatedHashPrefixes($rawAnswer, $normalizedAnswer, $candidateKeys),
        ], JSON_UNESCAPED_UNICODE));
    }

    private static function calculatedHashPrefixes(string $rawAnswer, string $normalizedAnswer, array $candidateKeys): array
    {
        $prefixes = [];
        foreach ($candidateKeys as $name => $key) {
            if ((string)$key === '') {
                continue;
            }

            $prefixes[(string)$name] = [
                'raw' => substr(hash_hmac('sha256', $rawAnswer, (string)$key), 0, 12),
                'normalized' => substr(hash_hmac('sha256', $normalizedAnswer, (string)$key), 0, 12),
            ];
        }

        return $prefixes;
    }

    private static function assertCredentials(): void
    {
        foreach ([
            'USERNAME' => self::username(),
            'PASSWORD' => self::password(),
            'PUBLIC_KEY' => self::publicKey(),
            'HMAC_SHA256' => self::hmacKey(),
        ] as $name => $value) {
            if ($value === '') {
                throw new \RuntimeException('IZIPAY_' . strtoupper(self::mode()) . '_' . $name . ' no esta configurado.');
            }
        }
    }

    private static function username(?string $mode = null): string
    {
        return self::credential('USERNAME', $mode);
    }

    private static function password(?string $mode = null): string
    {
        return self::credential('PASSWORD', $mode);
    }

    private static function hmacKey(?string $mode = null): string
    {
        return self::credential('HMAC_SHA256', $mode);
    }

    private static function hashCandidates(): array
    {
        $candidates = [];
        foreach (array_unique([self::mode(), 'test', 'production']) as $mode) {
            $candidates['password_' . $mode] = self::password($mode);
            $candidates['sha256_hmac_' . $mode] = self::hmacKey($mode);
        }

        $candidates['password_legacy'] = trim((string)($_ENV['IZIPAY_PASSWORD'] ?? ''));
        $candidates['sha256_hmac_legacy'] = trim((string)($_ENV['IZIPAY_HMAC_SHA256'] ?? ''));

        return array_filter($candidates, static fn(string $value): bool => $value !== '');
    }

    private static function credential(string $name, ?string $mode = null): string
    {
        $mode = CompanySettingsService::normalizeIzipayMode($mode ?? self::mode());
        $prefix = $mode === 'production' ? 'IZIPAY_PROD_' : 'IZIPAY_TEST_';
        $modeValue = trim((string)($_ENV[$prefix . $name] ?? ''));

        if ($modeValue !== '') {
            return $modeValue;
        }

        return trim((string)($_ENV['IZIPAY_' . $name] ?? ''));
    }

    private static function firstName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        return $parts[0] ?? 'Cliente';
    }

    private static function lastName(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];
        array_shift($parts);
        return trim(implode(' ', $parts)) ?: 'Caral';
    }
}