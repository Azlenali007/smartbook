<?php
/**
 * Smart Khata - Public Landing Page Controller
 */

namespace SmartKhata\Controllers;

use SmartKhata\Auth;
use SmartKhata\Services\InstallerService;

class LandingController {
    public function index(): void {
        $isLoggedIn = Auth::check();
        $admin = Auth::user();
        $settings = getSettings();

        require SK_APP . '/Views/home/landing.php';
    }
}
