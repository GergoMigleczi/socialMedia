<?php
/**
 * Application Configuration
 */

// Application settings
define('APP_NAME', 'Social Media App');
define('APP_URL', getenv('APP_URL'));
define('BASE_URL', '/socialMedia/public');
define('ADMIN_BASE_URL', '/socialMedia/admin');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // Options: development, testing, production

// Directory settings
define('VIEWS_PATH', APP_ROOT . '/Views');
define('CONTROLLERS_PATH', APP_ROOT . '/Controllers');
define('MODELS_PATH', APP_ROOT . '/Models');
define('MEDIA_PATH', DIRECTORY_ROOT . '/media');

// Session configuration
define('SESSION_NAME', 'social_media_session');
define('SESSION_LIFETIME', 7200); // Session lifetime in seconds (2 hours)
define('SESSION_SECURE', false); // Set to true in production with HTTPS
define('SESSION_HTTP_ONLY', true); // Prevents JavaScript access to session cookie

// Security settings
define('CSRF_PROTECTION', true); // Enable/disable CSRF protection
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT); // Password hashing algorithm
define('PASSWORD_HASH_COST', 12); // Cost factor for password hashing

// Error handling and logging
define('DISPLAY_ERRORS', true); // Show errors (set to false in production)
define('LOG_ERRORS', true); // Log errors to file
define('ERROR_LOG_PATH', dirname(APP_ROOT) . '/logs/error.log');

// Time and locale settings
define('DEFAULT_TIMEZONE', 'UTC');
define('DEFAULT_LOCALE', 'en_US');

// Initialize error settings
if (APP_ENV == 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
}

// Set default timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

// Initialize session with secure settings
session_name(SESSION_NAME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', SESSION_HTTP_ONLY);
ini_set('session.cookie_secure', SESSION_SECURE);
ini_set('session.use_strict_mode', 1);
ini_set('session.sid_length', 48);
ini_set('session.sid_bits_per_character', 6);

// Define any global helper functions
function base_url($path = '') {
    return APP_URL . '/' . ltrim($path, '/');
}

function view_path($view) {
    return VIEWS_PATH . '/' . str_replace('.', '/', $view) . '.php';
}

function redirect($path) {
    header('Location: ' . base_url($path));
    exit;
}

function is_development() {
    return APP_ENV == 'development';
}