<?php

namespace Caral\Modules\Cart\Controllers;

use Caral\Core\Template;
use Caral\Modules\Cart\Services\CartService;

class CartController
{
    public function index(): void
    {
        $items = CartService::getItems();
        $total = 0;

        foreach ($items as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        echo Template::render('Cart', 'cart', [
            'items' => $items,
            'total' => $total
        ]);
    }

    public function add(): void
    {
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($productId > 0 && $quantity > 0) {
            CartService::addItem($productId, $quantity);
        }

        // Redirigir al carrito para ver el resultado
        header('Location: /carrito');
        exit();
    }

    public function update(): void
    {
        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($productId > 0) {
            CartService::updateItem($productId, $quantity);
        }

        header('Location: /carrito');
        exit();
    }

    public function delete(string $productId): void
    {
        $id = (int)$productId;
        if ($id > 0) {
            CartService::removeItem($id);
        }

        header('Location: /carrito');
        exit();
    }
}
