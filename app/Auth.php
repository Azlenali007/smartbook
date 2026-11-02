<?php
/**
 * Smart Khata - Secure Admin Authentication & Session Manager
 */

namespace SmartKhata;

use DateTime;
use Exception;

class Auth {
    private const SESSION_ADMIN_ID = 'sk_admin_id';
    private const SESSION_ADMIN_USER = 'sk_admin_user';
    private const SESSION_LOGGED_AT = 'sk_admin_logged_at';
    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public static function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                       (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

            session_set_cookie_params([
                'lifetime' => 86400 * 7, // 7 days
                'path' => '/',
                'domain' => '',
                'secure' => $isHttps,
                'httponly' => true,
                'samesite' => 'Lax'
            ]);

            session_name('SMARTKHATA_SESSID');
            session_start();
        }
    }

    public static function hashPassword(string $password): string {
        $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
        return password_hash($password, $algo);
    }

    public static function verifyPassword(string $password, string $hash): bool {
        return password_verify($password, $hash);
    }

    public static function check(): bool {
        self::initSession();
        return !empty($_SESSION[self::SESSION_ADMIN_ID]);
    }

    public static function user(): ?array {
        if (!self::check()) return null;
        return [
            'id' => $_SESSION[self::SESSION_ADMIN_ID],
            'username' => $_SESSION[self::SESSION_ADMIN_USER]
        ];
    }

    public static function requireAuth(): void {
        if (!self::check()) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'] ?? '/';
            header('Location: /login');
            exit;
        }
    }

    public static function login(string $username, string $password): array {
        self::initSession();
        $db = Database::getInstance();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown', 0, 250);

        // Fetch admin
        $admin = $db->fetchOne("SELECT * FROM admins WHERE username = ? LIMIT 1", [$username]);

        if (!$admin) {
            self::logAttempt($username, $ip, $ua, 'failed');
            return ['success' => false, 'message' => 'Invalid username or password.'];
        }

        // Check lockout
        if (!empty($admin['locked_until'])) {
            $lockTime = new DateTime($admin['locked_until']);
            $now = new DateTime();
            if ($now < $lockTime) {
                $waitMinutes = ceil(($lockTime->getTimestamp() - $now->getTimestamp()) / 60);
                self::logAttempt($username, $ip, $ua, 'locked');
                return [
                    'success' => false,
                    'message' => "Account is temporarily locked due to consecutive failed attempts. Please retry in {$waitMinutes} minutes."
                ];
            } else {
                // Reset expired lock
                $db->execute("UPDATE admins SET failed_attempts = 0, locked_until = NULL WHERE id = ?", [$admin['id']]);
                $admin['failed_attempts'] = 0;
            }
        }

        // Verify password
        if (!self::verifyPassword($password, $admin['password_hash'])) {
            $failedAttempts = (int)($admin['failed_attempts'] ?? 0) + 1;
            $lockedUntil = null;
            $msg = 'Invalid username or password.';

            if ($failedAttempts >= self::MAX_ATTEMPTS) {
                $lockDuration = sprintf('+%d minutes', self::LOCKOUT_MINUTES);
                $lockedUntil = (new DateTime($lockDuration))->format('Y-m-d H:i:s');
                $msg = "Account locked for " . self::LOCKOUT_MINUTES . " minutes due to multiple failed login attempts.";
            }

            $db->execute("UPDATE admins SET failed_attempts = ?, locked_until = ? WHERE id = ?", [
                $failedAttempts,
                $lockedUntil,
                $admin['id']
            ]);

            self::logAttempt($username, $ip, $ua, 'failed');
            return ['success' => false, 'message' => $msg];
        }

        // Successful login
        session_regenerate_id(true);
        $_SESSION[self::SESSION_ADMIN_ID] = $admin['id'];
        $_SESSION[self::SESSION_ADMIN_USER] = $admin['username'];
        $_SESSION[self::SESSION_LOGGED_AT] = time();

        // Reset failed attempts & update last login
        $db->execute("UPDATE admins SET failed_attempts = 0, locked_until = NULL, last_login_at = ? WHERE id = ?", [
            (new DateTime())->format('Y-m-d H:i:s'),
            $admin['id']
        ]);

        self::logAttempt($username, $ip, $ua, 'success');
        return ['success' => true];
    }

    public static function logout(): void {
        self::initSession();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function changePassword(int $adminId, string $currentPassword, string $newPassword): array {
        $db = Database::getInstance();
        $admin = $db->fetchOne("SELECT * FROM admins WHERE id = ?", [$adminId]);
        if (!$admin || !self::verifyPassword($currentPassword, $admin['password_hash'])) {
            return ['success' => false, 'message' => 'Current password does not match.'];
        }

        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'New password must be at least 8 characters long.'];
        }

        $newHash = self::hashPassword($newPassword);
        $db->execute("UPDATE admins SET password_hash = ?, updated_at = ? WHERE id = ?", [
            $newHash,
            (new DateTime())->format('Y-m-d H:i:s'),
            $adminId
        ]);

        return ['success' => true, 'message' => 'Password changed successfully.'];
    }

    private static function logAttempt(string $username, string $ip, string $ua, string $status): void {
        try {
            $db = Database::getInstance();
            $db->execute("INSERT INTO login_logs (username, ip_address, user_agent, status, attempted_at) VALUES (?, ?, ?, ?, ?)", [
                $username,
                $ip,
                $ua,
                $status,
                (new DateTime())->format('Y-m-d H:i:s')
            ]);
        } catch (\Throwable $e) {
            // Silently ignore logging failures to not disrupt auth flow
        }
    }
}
