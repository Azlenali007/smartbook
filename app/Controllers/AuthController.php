<?php
/**
 * Smart Khata - Authentication Controller
 */

namespace SmartKhata\Controllers;

use SmartKhata\Auth;
use SmartKhata\CSRF;

class AuthController {
    public function showLogin(): void {
        if (Auth::check()) {
            header('Location: /');
            exit;
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        require SK_APP . '/Views/auth/login.php';
    }

    public function handleLogin(): void {
        CSRF::verifyOrAbort();

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $_SESSION['login_error'] = 'Please enter both username and password.';
            header('Location: /login');
            exit;
        }

        $res = Auth::login($username, $password);
        if ($res['success']) {
            $target = $_SESSION['intended_url'] ?? '/';
            unset($_SESSION['intended_url']);
            header("Location: {$target}");
            exit;
        }

        $_SESSION['login_error'] = $res['message'];
        header('Location: /login');
        exit;
    }

    public function logout(): void {
        Auth::logout();
        header('Location: /login');
        exit;
    }
}
