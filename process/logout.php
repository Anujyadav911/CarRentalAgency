<?php
/**
 * Process: Logout
 *
 * Destroys the current session and redirects to the home page.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Clear all session data
$_SESSION = [];

// Invalidate the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

redirect(BASE_URL . '/index.php');
