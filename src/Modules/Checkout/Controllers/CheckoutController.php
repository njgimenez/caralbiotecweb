<?php

namespace Caral\Modules\Checkout\Controllers;

use Caral\Core\Session;
use Caral\Core\Template;
use Caral\Modules\Cart\Services\CartService;
use Caral\Modules\Checkout\Services\DeliveryService;
use Caral\Modules\Checkout\Services\IzipayService;
use Caral\Modules\Checkout\Services\OrderService;

class CheckoutController
{
    private const CHECKOUT_ORDER_ID = 'checkout_order_id';
    private const CHECKOUT_TOKEN = 'checkout_token';

    public function show(): void
    {
        $order = $this->recoverableOrder();
        if ($order) {
            if ($order['payment_status'] === 'paid') {
                $this->redirectToConfirmation((int)$order['id']);
            }

            echo Template::render('Checkout', 'checkout', $this->viewDataFromOrder($order, [
                'mode' => 'recovery',
            ]));
            return;
        }

        $items = CartService::getItems();
        if (empty($items)) {
            header('Location: /carrito');
            exit();
        }

        echo Template::render('Checkout', 'checkout', $this->viewDataFromCart($items, [
            'mode' => 'details',
        ]));
    }

    public function process(): void
    {
        $order = $this->recoverableOrder();
        if ($order && in_array($order['payment_status'], ['pending', 'payment_started', 'authorized'], true)) {
            header('Location: ' . $this->paymentUrl((int)$order['id'], (string)Session::get(self::CHECKOUT_TOKEN)));
            exit();
        }

        $items = CartService::getItems();
        if (empty($items)) {
            header('Location: /carrito');
            exit();
        }

        $customerData = $this->customerDataFromPost();
        $errors = $this->validateCustomerData($customerData);

        if ($errors !== []) {
            echo Template::render('Checkout', 'checkout', $this->viewDataFromCart($items, [
                'mode' => 'details',
                'errors' => $errors,
                'old' => $_POST,
            ]));
            return;
        }

        try {
            $checkoutToken = OrderService::generateCheckoutToken();
            $orderId = OrderService::createPendingFromCart($customerData, $checkoutToken);
            $this->setCheckoutSession($orderId, $checkoutToken);

            header('Location: ' . $this->paymentUrl($orderId, $checkoutToken));
            exit();
        } catch (\Throwable $e) {
            echo Template::render('Checkout', 'checkout', $this->viewDataFromCart($items, [
                'mode' => 'details',
                'errors' => ['No se pudo iniciar el pago: ' . $e->getMessage()],
                'old' => $_POST,
            ]));
        }
    }

    public function payment(): void
    {
        $order = $this->recoverableOrder();
        if (!$order) {
            header('Location: /carrito');
            exit();
        }

        if ($order['payment_status'] === 'paid') {
            $this->redirectToConfirmation((int)$order['id']);
        }

        if (in_array($order['payment_status'], ['cancelled', 'failed'], true)) {
            Session::set('checkout_error', 'Esta orden ya no se puede continuar. Inicia una nueva compra desde el carrito.');
            header('Location: /checkout/error');
            exit();
        }

        $items = OrderService::getItems((int)$order['id']);
        try {
            $payment = IzipayService::createFormToken($order, $items);
            OrderService::markPaymentStarted((int)$order['id'], [
                'provider' => 'izipay_formtoken',
                'createPayment' => $payment['response'],
            ]);

            echo Template::render('Checkout', 'checkout', $this->viewDataFromOrder($order, [
                'mode' => 'payment',
                'izipay' => [
                    'formToken' => $payment['formToken'],
                    'publicKey' => IzipayService::publicKey(),
                    'scriptUrl' => IzipayService::kryptonScriptUrl(),
                    'classicCssUrl' => IzipayService::kryptonClassicCssUrl(),
                    'classicJsUrl' => IzipayService::kryptonClassicJsUrl(),
                    'postUrlSuccess' => '/checkout/izipay/resultado',
                ],
            ]));
        } catch (\Throwable $e) {
            echo Template::render('Checkout', 'checkout', $this->viewDataFromOrder($order, [
                'mode' => 'recovery',
                'errors' => ['No se pudo generar el formulario de pago: ' . $e->getMessage()],
            ]));
        }
    }

    public function cancel(): void
    {
        $order = $this->recoverableOrder();
        if ($order) {
            OrderService::cancelPending((int)$order['id']);
        }

        $this->clearCheckoutSession();
        header('Location: /carrito');
        exit();
    }

    public function result(): void
    {
        try {
            if ($_POST === []) {
                throw new \RuntimeException('No se recibio respuesta de Izipay.');
            }

            if (!IzipayService::validateFrontendHash($_POST)) {
                throw new \RuntimeException('Firma de respuesta Izipay invalida.');
            }

            $answer = IzipayService::decodeAnswer($_POST);
            $orderNumber = IzipayService::orderId($answer);
            $order = $orderNumber !== '' ? OrderService::findByOrderNumber($orderNumber) : null;
            if (!$order) {
                throw new \RuntimeException('No se encontro la orden asociada al pago.');
            }

            $status = IzipayService::orderStatus($answer);
            OrderService::markFrontendResult((int)$order['id'], $status, [
                'post' => $_POST,
                'answer' => $answer,
            ]);

            if (IzipayService::isPaid($answer)) {
                OrderService::markAsPaid((int)$order['id'], IzipayService::transactionUuid($answer), $answer);
                $this->redirectToConfirmation((int)$order['id']);
            }

            if ($order['payment_status'] === 'paid') {
                $this->redirectToConfirmation((int)$order['id']);
            }

            $this->clearCheckoutSession();
            Session::set('checkout_error', 'Izipay no aprobo la transaccion. Estado: ' . ($status ?: 'desconocido'));
            header('Location: /checkout/error');
            exit();
        } catch (\Throwable $e) {
            Session::set('checkout_error', 'No se pudo validar la respuesta de Izipay: ' . $e->getMessage());
            header('Location: /checkout/error');
            exit();
        }
    }

    public function ipn(): void
    {
        header('Content-Type: text/plain; charset=utf-8');

        try {
            if ($_POST === []) {
                http_response_code(400);
                echo 'No post data received';
                return;
            }

            if (!IzipayService::validateIpnHash($_POST)) {
                http_response_code(400);
                echo 'Invalid signature';
                return;
            }

            $answer = IzipayService::decodeAnswer($_POST);
            $orderNumber = IzipayService::orderId($answer);
            $order = $orderNumber !== '' ? OrderService::findByOrderNumber($orderNumber) : null;
            if (!$order) {
                http_response_code(404);
                echo 'Order not found';
                return;
            }

            $orderId = (int)$order['id'];
            OrderService::markIpnResult($orderId, [
                'post' => $_POST,
                'answer' => $answer,
            ]);

            if (IzipayService::isPaid($answer)) {
                OrderService::markAsPaid($orderId, IzipayService::transactionUuid($answer), $answer);
            } else {
                OrderService::markAsFailed($orderId, $answer);
            }

            echo 'OK! OrderStatus is ' . IzipayService::orderStatus($answer);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo 'IPN error';
        }
    }

    public function confirmation(): void
    {
        $orderId = Session::get('last_order_id');

        if (!$orderId) {
            header('Location: /');
            exit();
        }

        $order = OrderService::findById((int)$orderId);
        $orderItems = OrderService::getItems((int)$orderId);

        echo Template::render('Checkout', 'confirmation', [
            'order' => $order,
            'orderItems' => $orderItems,
        ]);
    }

    public function paymentError(): void
    {
        $errorMessage = Session::get('checkout_error') ?? 'Ocurrio un error al procesar el pago.';
        Session::remove('checkout_error');

        echo Template::render('Checkout', 'payment_error', [
            'errorMessage' => $errorMessage,
        ]);
    }

    private function recoverableOrder(): ?array
    {
        $orderId = (int)($_GET['order'] ?? Session::get(self::CHECKOUT_ORDER_ID, 0));
        $token = (string)($_GET['token'] ?? Session::get(self::CHECKOUT_TOKEN, ''));

        if ($orderId <= 0 || $token === '') {
            return null;
        }

        $order = OrderService::findByCheckoutToken($orderId, $token);
        if (!$order) {
            $this->clearCheckoutSession();
            return null;
        }

        $this->setCheckoutSession($orderId, $token);
        return $order;
    }

    private function customerDataFromPost(): array
    {
        return [
            'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'document_type' => trim($_POST['document_type'] ?? 'DNI'),
            'document' => trim($_POST['document'] ?? ''),
            'fulfillment_method' => ($_POST['fulfillment_method'] ?? 'delivery') === 'pickup' ? 'pickup' : 'delivery',
            'address' => trim($_POST['address'] ?? ''),
            'district' => trim($_POST['district'] ?? ''),
            'city' => DeliveryService::normalizeCity(trim($_POST['city'] ?? 'Lima')),
            'courier' => strtoupper(trim($_POST['courier'] ?? '')),
        ];
    }

    private function validateCustomerData(array $data): array
    {
        $errors = [];
        if (strlen($data['name']) < 3) {
            $errors[] = 'El nombre completo es requerido.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electronico no es valido.';
        }
        if ($data['document'] === '') {
            $errors[] = 'El documento de identidad es requerido.';
        }
        if ($data['fulfillment_method'] === 'delivery') {
            if ($data['address'] === '') {
                $errors[] = 'La direccion de envio es requerida.';
            }

            if (DeliveryService::isLimaCity((string)$data['city'])) {
                if ($data['district'] === '') {
                    $errors[] = 'El distrito es requerido para envios en Lima.';
                } elseif (!DeliveryService::findDistrict($data['district'])) {
                    $errors[] = 'Selecciona un distrito valido para delivery en Lima.';
                }
            } elseif (!in_array((string)$data['courier'], DeliveryService::provinceCourierOptions(), true)) {
                $errors[] = 'Selecciona el courier para envio a provincia: OLVA o SHALOM.';
            }
        }

        return $errors;
    }

    private function viewDataFromCart(array $items, array $overrides = []): array
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price_seen'] * $item['quantity'];
        }

        return array_merge([
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => 0,
            'total' => $subtotal,
            'order' => null,
            'izipay' => [],
            'deliveryOptions' => DeliveryService::publicOptions(),
            'cityOptions' => DeliveryService::peruCities(),
            'provinceHandlingFee' => DeliveryService::provinceHandlingFee(),
            'user' => [
                'name' => Session::getUserName(),
                'email' => Session::getUserEmail(),
            ],
        ], $overrides);
    }

    private function viewDataFromOrder(array $order, array $overrides = []): array
    {
        $token = (string)Session::get(self::CHECKOUT_TOKEN, '');

        return array_merge([
            'items' => OrderService::getItems((int)$order['id']),
            'subtotal' => (float)$order['subtotal'],
            'shipping' => (float)$order['shipping_cost'],
            'total' => (float)$order['total'],
            'order' => $order,
            'paymentUrl' => $this->paymentUrl((int)$order['id'], $token),
            'cancelUrl' => '/checkout/cancelar',
            'izipay' => [],
            'deliveryOptions' => DeliveryService::publicOptions(),
            'cityOptions' => DeliveryService::peruCities(),
            'provinceHandlingFee' => DeliveryService::provinceHandlingFee(),
            'user' => [
                'name' => $order['customer_name'],
                'email' => $order['customer_email'],
            ],
        ], $overrides);
    }

    private function paymentUrl(int $orderId, string $token): string
    {
        return '/checkout/pago?order=' . $orderId . '&token=' . urlencode($token);
    }

    private function setCheckoutSession(int $orderId, string $token): void
    {
        Session::set(self::CHECKOUT_ORDER_ID, $orderId);
        Session::set(self::CHECKOUT_TOKEN, $token);
    }

    private function clearCheckoutSession(): void
    {
        Session::remove(self::CHECKOUT_ORDER_ID);
        Session::remove(self::CHECKOUT_TOKEN);
    }

    private function redirectToConfirmation(int $orderId): void
    {
        Session::set('last_order_id', $orderId);
        $this->clearCheckoutSession();
        header('Location: /checkout/confirmacion');
        exit();
    }
}
