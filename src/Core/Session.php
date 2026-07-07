<?php

namespace Caral\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            // Ajustar configuraciones de sesión seguras
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_only_cookies', '1');
            
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', '1');
            }

            session_start();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        self::start();
        session_destroy();
        $_SESSION = [];
    }

    // Helper de Autenticación
    public static function login(array $user): void
    {
        self::set('user_id', $user['id']);
        self::set('user_email', $user['email']);
        self::set('user_name', $user['name'] ?? '');
        self::set('user_role', $user['role_name'] ?? 'Cliente');
    }

    public static function logout(): void
    {
        self::remove('user_id');
        self::remove('user_email');
        self::remove('user_role');
    }

    public static function isLoggedIn(): bool
    {
        return self::has('user_id');
    }

    public static function getUserId(): ?int
    {
        return self::get('user_id');
    }

    public static function getUserEmail(): ?string
    {
        return self::get('user_email');
    }

    public static function getUserRole(): ?string
    {
        return self::get('user_role');
    }

    public static function getUserName(): ?string
    {
        return self::get('user_name');
    }

    public static function setFlash(string $key, string $message): void
    {
        self::set('_flash_' . $key, $message);
    }

    public static function getFlash(string $key): ?string
    {
        $val = self::get('_flash_' . $key);
        self::remove('_flash_' . $key);
        return $val;
    }

    // Identificador único para el carrito anónimo (session token)
    public static function getCartToken(): string
    {
        if (!self::has('cart_token')) {
            self::set('cart_token', bin2hex(random_bytes(32)));
        }
        return self::get('cart_token');
    }
}
