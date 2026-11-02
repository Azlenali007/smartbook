<?php
/**
 * Smart Khata - Configuration Sample Template
 * Generated automatically during installation or configured manually.
 */

return [
    // Database Driver: 'mysql' or 'sqlite'
    'db_driver' => 'sqlite',

    // For MySQL:
    'db_host' => '127.0.0.1',
    'db_port' => '3306',
    'db_name' => 'smart_khata',
    'db_user' => 'root',
    'db_pass' => '',
    'db_charset' => 'utf8mb4',

    // For SQLite:
    'db_path' => dirname(__DIR__) . '/storage/database/smart_khata.sqlite',

    // Application Settings
    'app_name' => 'Smart Khata',
    'app_env' => 'production', // 'development' or 'production'
    'app_secret' => 'change_this_to_a_random_secret_string',
    'base_url' => '',
    'timezone' => 'Asia/Kolkata',
    'currency' => 'INR',
    'currency_symbol' => '₹',
];
