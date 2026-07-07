<?php

namespace Caral\Modules\Cart\Services;

use PDO;
use Caral\Core\Database;
use Caral\Core\Session;

class CartService
{
    private static ?int $cartId = null;

    /**
     * Obtener o crear el ID del carrito activo para la sesión actual
     */
    public static function getActiveCartId(): int
    {
        if (self::$cartId !== null) {
            return self::$cartId;
        }

        $db = Database::getConnection();
        $userId = Session::getUserId();
        $cartToken = Session::getCartToken();
        $tokenHash = hash('sha256', $cartToken);

        try {
            if ($userId) {
                // 1. Buscar carrito activo asociado al usuario
                $stmt = $db->prepare("SELECT id FROM shopping_carts WHERE user_id = :user_id AND status = 'active' LIMIT 1");
                $stmt->execute(['user_id' => $userId]);
                $cart = $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // 2. Buscar carrito activo asociado al token de sesión anónima
                $stmt = $db->prepare("SELECT id FROM shopping_carts WHERE session_token_hash = :hash AND status = 'active' LIMIT 1");
                $stmt->execute(['hash' => $tokenHash]);
                $cart = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if ($cart) {
                self::$cartId = (int)$cart['id'];
                // Actualizar última actividad del carrito
                $updateStmt = $db->prepare("UPDATE shopping_carts SET last_activity_at = CURRENT_TIMESTAMP WHERE id = :id");
                $updateStmt->execute(['id' => self::$cartId]);
            } else {
                // 3. Crear nuevo carrito si no existe
                $publicToken = self::generateUuid();
                $insertStmt = $db->prepare("
                    INSERT INTO shopping_carts (public_token, user_id, session_token_hash, status, currency) 
                    VALUES (:public_token, :user_id, :session_token_hash, 'active', 'PEN')
                ");
                $insertStmt->execute([
                    'public_token' => $publicToken,
                    'user_id' => $userId,
                    'session_token_hash' => $userId ? null : $tokenHash
                ]);
                self::$cartId = (int)$db->lastInsertId();
            }
        } catch (\Exception $e) {
            self::$cartId = 0; // Error fallback
        }

        return self::$cartId;
    }

    /**
     * Agregar un producto al carrito activo
     */
    public static function addItem(int $productId, int $quantity): bool
    {
        if ($quantity <= 0) return false;

        $db = Database::getConnection();
        $cartId = self::getActiveCartId();

        try {
            // Obtener precio actual del producto para guardarlo en price_seen
            $prodStmt = $db->prepare("SELECT price, stock FROM products WHERE id = :id AND is_active = 1");
            $prodStmt->execute(['id' => $productId]);
            $product = $prodStmt->fetch(PDO::FETCH_ASSOC);

            if (!$product || $product['stock'] < $quantity) {
                return false;
            }

            // Insertar o actualizar item
            $stmt = $db->prepare("
                INSERT INTO cart_items (shopping_cart_id, product_id, quantity, price_seen) 
                VALUES (:cart_id, :product_id, :quantity, :price_seen)
                ON DUPLICATE KEY UPDATE quantity = quantity + :quantity_add, price_seen = :price_seen_update
            ");
            return $stmt->execute([
                'cart_id' => $cartId,
                'product_id' => $productId,
                'quantity' => $quantity,
                'price_seen' => $product['price'],
                'quantity_add' => $quantity,
                'price_seen_update' => $product['price']
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Actualizar la cantidad de un producto en el carrito
     */
    public static function updateItem(int $productId, int $quantity): bool
    {
        if ($quantity <= 0) {
            return self::removeItem($productId);
        }

        $db = Database::getConnection();
        $cartId = self::getActiveCartId();

        try {
            $stmt = $db->prepare("
                UPDATE cart_items 
                SET quantity = :quantity 
                WHERE shopping_cart_id = :cart_id AND product_id = :product_id
            ");
            return $stmt->execute([
                'quantity' => $quantity,
                'cart_id' => $cartId,
                'product_id' => $productId
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Eliminar un producto del carrito
     */
    public static function removeItem(int $productId): bool
    {
        $db = Database::getConnection();
        $cartId = self::getActiveCartId();

        try {
            $stmt = $db->prepare("DELETE FROM cart_items WHERE shopping_cart_id = :cart_id AND product_id = :product_id");
            return $stmt->execute([
                'cart_id' => $cartId,
                'product_id' => $productId
            ]);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener todas las líneas de productos del carrito activo
     */
    public static function getItems(): array
    {
        $db = Database::getConnection();
        $cartId = self::getActiveCartId();

        try {
            $stmt = $db->prepare("
                SELECT ci.*, p.name, p.slug, p.price, p.sku 
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                WHERE ci.shopping_cart_id = :cart_id
                ORDER BY ci.id ASC
            ");
            $stmt->execute(['cart_id' => $cartId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener el conteo total de items en el carrito activo
     */
    public static function getCartCount(): int
    {
        $db = Database::getConnection();
        $cartId = self::getActiveCartId();

        try {
            $stmt = $db->prepare("SELECT SUM(quantity) as total_qty FROM cart_items WHERE shopping_cart_id = :cart_id");
            $stmt->execute(['cart_id' => $cartId]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($res['total_qty'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Generar un UUID v4 básico para tokens públicos
     */
    private static function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // version 4
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // variant SRFC 4122
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
