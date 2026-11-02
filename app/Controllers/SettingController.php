<?php
/**
 * Smart Khata - Settings Controller
 */

namespace SmartKhata\Controllers;

use SmartKhata\Auth;
use SmartKhata\CSRF;
use SmartKhata\Database;

class SettingController {
    public function index(): void {
        Auth::requireAuth();
        $settings = getSettings();
        $admin = Auth::user();

        require SK_APP . '/Views/settings/index.php';
    }

    public function updateBusiness(): void {
        Auth::requireAuth();
        CSRF::verifyOrAbort();

        $db = Database::getInstance();

        $keys = [
            'shop_name',
            'shop_phone',
            'shop_address',
            'shop_gstin',
            'gst_rate',
            'invoice_prefix',
            'invoice_footer',
            'date_format'
        ];

        foreach ($keys as $k) {
            $val = trim($_POST[$k] ?? '');
            $sCheck = $db->fetchOne("SELECT id FROM settings WHERE setting_key = ?", [$k]);
            if ($sCheck) {
                $db->execute("UPDATE settings SET setting_value = ? WHERE setting_key = ?", [$val, $k]);
            } else {
                $db->execute("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)", [$k, $val]);
            }
        }

        $_SESSION['flash_success'] = "Business settings saved successfully.";
        header('Location: /settings');
        exit;
    }

    public function updatePassword(): void {
        Auth::requireAuth();
        CSRF::verifyOrAbort();

        $admin = Auth::user();
        $currPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confPass = $_POST['confirm_password'] ?? '';

        if ($newPass !== $confPass) {
            $_SESSION['flash_error'] = "New passwords do not match.";
            header('Location: /settings');
            exit;
        }

        $res = Auth::changePassword((int)$admin['id'], $currPass, $newPass);
        if ($res['success']) {
            $_SESSION['flash_success'] = $res['message'];
        } else {
            $_SESSION['flash_error'] = $res['message'];
        }

        header('Location: /settings');
        exit;
    }
}
