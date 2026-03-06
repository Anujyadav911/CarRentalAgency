<?php
/**
 * Database configuration and singleton connection helper.
 *
 * Car Rental Agency Application
 */

declare(strict_types=1);

// Hide errors from visitors in production
ini_set('display_errors', '0');
error_reporting(0);

// ── Database credentials ──────────────────────────────────────────────────────
// InfinityFree production credentials
define('DB_HOST', 'sql100.infinityfree.com');
define('DB_USER', 'if0_41321331');
define('DB_PASS', 'Car437896');    // ← your InfinityFree login password
define('DB_NAME', 'if0_41321331_car_rental');

// ── Application base URL ──────────────────────────────────────────────────────
// Set to '' when the app is served from the web root (e.g. http://localhost:8000)
// Set to '/subdir' if hosted under a subdirectory (e.g. '/CarRentalAgency')
define('BASE_URL', '');

/**
 * Returns a singleton MySQLi connection.
 *
 * Terminates the request with a user-friendly error page on failure so that
 * raw database error details are never exposed to the browser.
 *
 * @return mysqli
 */
function getDBConnection(): mysqli
{
    static $conn = null;

    if ($conn === null) {
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            $conn->set_charset('utf8mb4');
        } catch (\mysqli_sql_exception $e) {
            http_response_code(500);
            die(
                '<div style="font-family:sans-serif;padding:2rem;max-width:600px;margin:2rem auto;'
                . 'border:1px solid #f5c6cb;border-radius:8px;background:#fff5f5;color:#721c24;">'
                . '<h2>⚠️ Database Connection Error</h2>'
                . '<p>Could not connect to the MySQL database.<br>'
                . 'Please verify the credentials in <code>config/db.php</code> and ensure '
                . 'the MySQL service is running.</p>'
                . '</div>'
            );
        }
    }

    return $conn;
}
