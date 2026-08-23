<?php
/**
 * Ruwanpura Gems - Central Configuration (sample)
 * ---------------------------------------------------------------
 * Copy this file to config.php and fill in your real credentials.
 * config.php is gitignored so real credentials never get committed.
 * ---------------------------------------------------------------
 */

// ---- Database credentials (change these on your cPanel) ----
define('DB_HOST', 'localhost');
define('DB_NAME', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---- Site paths ----
// BASE_URL: '/' for domain root, or '/subfolder/' if in a subdirectory.
// Keep the trailing slash.
define('BASE_URL', '/');

define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_DIR', ROOT_PATH . '/assets/uploads');
define('UPLOAD_URL', BASE_URL . 'assets/uploads/');

// ---- Session ----
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---- Error reporting (set display_errors to '0' in production) ----
error_reporting(E_ALL);
ini_set('display_errors', '1');

// ---- Timezone ----
date_default_timezone_set('Asia/Bangkok');
