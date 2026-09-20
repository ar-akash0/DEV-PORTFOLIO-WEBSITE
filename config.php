<?php
// Config helper to load environment or default values
function get_env_val($key, $default = '') {
    $val = getenv($key);
    return ($val !== false && $val !== '') ? $val : $default;
}

// Database Credentials
define('DB_HOST', get_env_val('DB_HOST', '127.0.0.1'));
define('DB_USER', get_env_val('DB_USER', 'db_user'));
define('DB_PASS', get_env_val('DB_PASS', 'db_password'));
define('DB_NAME', get_env_val('DB_NAME', 'bsrp_db'));
define('DB_PORT', (int)get_env_val('DB_PORT', 3306));

// SA-MP Socket Target
define('SAMP_SERVER_IP', get_env_val('SAMP_IP', '127.0.0.1'));
define('SAMP_SERVER_PORT', (int)get_env_val('SAMP_PORT', 7777));

// Admin Security
define('ADMIN_PASS', get_env_val('ADMIN_PASSWORD', 'admin_secure_pass'));
define('DISCORD_WEBHOOK', get_env_val('DISCORD_WEBHOOK_URL', ''));