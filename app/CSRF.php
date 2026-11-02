<?php
/**
 * Smart Khata - Cross-Site Request Forgery (CSRF) Protection
 */

namespace SmartKhata;

class CSRF {
    private const SESSION_KEY = 'sk_csrf_token';

    public static function getToken(): string {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    public static function getField(): string {
        $token = htmlspecialchars(self::getToken(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    public static function validate(?string $token = null): bool {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        if ($token === null) {
            $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        }

        if (empty($token) || empty($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        return hash_equals($_SESSION[self::SESSION_KEY], $token);
    }

    public static function verifyOrAbort(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!self::validate()) {
                http_response_code(403);
                header('Content-Type: text/html; charset=utf-8');
                echo '<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body style="font-family:sans-serif;padding:40px;text-align:center;"><h2>403 — Invalid CSRF Security Token</h2><p>Your session may have expired. Please go back and try again.</p><p><a href="/">Return Home</a></p></body></html>';
                exit;
            }
        }
    }
}
