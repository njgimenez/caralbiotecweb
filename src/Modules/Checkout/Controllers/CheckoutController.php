<?php

namespace Caral\Modules\Checkout\Controllers;

use Caral\Core\Session;
use Caral\Core\Template;
use Caral\Modules\Cart\Services\CartService;
use Caral\Modules\Checkout\Services\IzipayService;
use Caral\Modules\Checkout\Services\OrderService;

class CheckoutController
{
    public function show(): void
    {
        $items = CartService::getItems();

        if (empty($items)) {
            header('Location: /carrito');
            exit();
        }

        echo Template::render('Checkout', 'checkout', $this->checkoutViewData($items));
    }

    public function process(): void
    {
        $items = CartService::getItems();

        if (empty($items)) {
            header('Location: /carrito');
            exit();
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $city = trim($_POST['city'] ?? 'Lima');
        $izipayResponseRaw = trim($_POST['izipay_response'] ?? '');
        $izipayOrderNumber = trim($_POST['izipay_order_number'] ?? '');

        $errors = [];

        if (strlen($name) < 3) {
            $errors[] = 'El nombre completo es requerido.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo electronico no es valido.';
        }
        if ($address === '') {
            $errors[] = 'La direccion de envio es requerida.';
        }
        if ($district === '') {
            $errors[] = 'El distrito es requerido.';
        }
        if ($izipayResponseRaw === '') {
            $errors[] = 'No se recibio la respuesta de Izipay. Intente nuevamente.';
        }

        $paymentResponse = null;
        if ($izipayResponseRaw !== '') {
            try {
                $paymentResponse = IzipayService::decodeResponse($izipayResponseRaw);
                if (!IzipayService::isApproved($paymentResponse)) {
                    $errors[] = $paymentResponse['messageUser'] ?? 'Izipay no aprobo la transaccion.';
                }
            } catch (\RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (!empty($errors)) {
            echo Template::render('Checkout', 'checkout', $this->checkoutViewData($items, [
                'errors' => $errors,
                'old' => $_POST,
                'orderNumber' => $izipayOrderNumber ?: OrderService::generateOrderNumber(),
            ]));
            return;
        }

        try {
            $orderId = OrderService::createFromCart([
                'order_number' => IzipayService::orderNumber($paymentResponse ?? []) ?: $izipayOrderNumber ?: OrderService::generateOrderNumber(),
                'payment_method' => 'izipay',
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'address' => $address,
                'district' => $district,
                'city' => $city,
            ]);

            OrderService::markAsPaid(
                $orderId,
                IzipayService::operationId($paymentResponse ?? []),
                $paymentResponse ?? []
            );

            $this->completeCart();

            Session::set('last_order_id', $orderId);
            header('Location: /checkout/confirmacion');
            exit();
        } catch (\Throwable $e) {
            Session::set('checkout_error', 'No se pudo completar tu orden: ' . $e->getMessage());
            header('Location: /checkout/error');
            exit();
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

    private function checkoutViewData(array $items, array $overrides = []): array
    {
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['price_seen'] * $item['quantity'];
        }

        $orderNumber = $overrides['orderNumber'] ?? OrderService::generateOrderNumber();
        $transactionId = (string)(time() . random_int(1000, 9999));
        $dateTimeTransaction = date('YmdHis');
        try {
            $izipay = IzipayService::publicConfig([
                'transactionId' => $transactionId,
                'orderNumber' => $orderNumber,
                'amount' => $subtotal,
            ]);
        } catch (\RuntimeException $e) {
            $izipay = IzipayService::publicConfig();
            $overrides['errors'][] = $e->getMessage();
        }
        $izipay['orderNumber'] = $orderNumber;
        $izipay['transactionId'] = $transactionId;
        $izipay['dateTimeTransaction'] = $dateTimeTransaction;

        return array_merge([
            'items' => $items,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'izipay' => $izipay,
            'user' => [
                'name' => Session::getUserName(),
                'email' => Session::getUserEmail(),
            ],
        ], $overrides);
    }

    private function completeCart(): void
    {
        try {
            $db = \Caral\Core\Database::getConnection();
            $cartId = CartService::getActiveCartId();
            if ($cartId > 0) {
                $db->prepare("UPDATE shopping_carts SET status = 'completed' WHERE id = :id")
                   ->execute(['id' => $cartId]);
            }
        } catch (\Throwable) {
            // No bloquear el flujo por esto.
        }
    }
}

