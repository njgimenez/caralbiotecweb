<?php

namespace Caral\Modules\Checkout\Services;

class IzipayService
{
    public static function publicKey(): string
    {
        return trim((string)($_ENV['IZIPAY_PUBLIC_KEY'] ?? ''));
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
        return (string)($_ENV['IZIPAY_CREATE_PAYMENT_ENDPOINT'] ?? 'https://api.micuentaweb.pe/api-payment/V4/Charge/CreatePayment');
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
                    'address' => (string)($order['shipping_address'] ?? ''),
                    'country' => 'PE',
                    'city' => (string)($order['shipping_city'] ?? 'Lima'),
                    'state' => (string)($order['shipping_district'] ?? 'Lima'),
                    'zipCode' => (string)($_ENV['IZIPAY_DEFAULT_ZIP_CODE'] ?? '15000'),
                ],
            ],
            'metadata' => [
                'orderId' => (string)$order['id'],
                'source' => 'caral-web',
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
        ];
    }

    public static function validateFrontendHash(array $post): bool
    {
        return self::validateHash($post, self::hmacKey());
    }

    public static function validateIpnHash(array $post): bool
    {
        return self::validateHash($post, self::password());
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

    private static function validateHash(array $post, string $key): bool
    {
        if ($key === '' || empty($post['kr-answer']) || empty($post['kr-hash'])) {
            return false;
        }

        $krAnswer = str_replace('\/', '/', (string)$post['kr-answer']);
        $calculated = hash_hmac('sha256', $krAnswer, $key);
        return hash_equals($calculated, (string)$post['kr-hash']);
    }

    private static function assertCredentials(): void
    {
        foreach ([
            'IZIPAY_USERNAME' => self::username(),
            'IZIPAY_PASSWORD' => self::password(),
            'IZIPAY_PUBLIC_KEY' => self::publicKey(),
            'IZIPAY_HMAC_SHA256' => self::hmacKey(),
        ] as $name => $value) {
            if ($value === '') {
                throw new \RuntimeException($name . ' no esta configurado.');
            }
        }
    }

    private static function username(): string
    {
        return trim((string)($_ENV['IZIPAY_USERNAME'] ?? ''));
    }

    private static function password(): string
    {
        return trim((string)($_ENV['IZIPAY_PASSWORD'] ?? ''));
    }

    private static function hmacKey(): string
    {
        return trim((string)($_ENV['IZIPAY_HMAC_SHA256'] ?? ''));
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
