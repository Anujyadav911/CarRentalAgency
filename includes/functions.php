<?php
/**
 * Core utility functions for the Car Rental Agency application.
 *
 * Covers: session bootstrap, authentication guards, flash messages,
 * input sanitisation, redirect helper, and formatting utilities.
 */

declare(strict_types=1);

// Start session exactly once, before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ── Authentication helpers ────────────────────────────────────────────────────

/** Returns true when any user is currently logged in. */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['user_type']);
}

/** Returns true when the authenticated user is a customer. */
function isCustomer(): bool
{
    return isLoggedIn() && $_SESSION['user_type'] === 'customer';
}

/** Returns true when the authenticated user is an agency. */
function isAgency(): bool
{
    return isLoggedIn() && $_SESSION['user_type'] === 'agency';
}

/**
 * Gate: allow only agency users.
 * Sets a flash error and redirects to the login page if the check fails.
 */
function requireAgency(): void
{
    if (!isAgency()) {
        setFlash('error', 'Access denied. Please log in as a Car Rental Agency.');
        redirect(BASE_URL . '/login.php');
    }
}

/**
 * Gate: allow only customer users.
 * Sets a flash error and redirects to the login page if the check fails.
 */
function requireCustomer(): void
{
    if (!isCustomer()) {
        setFlash('error', 'Access denied. Please log in as a Customer.');
        redirect(BASE_URL . '/login.php');
    }
}

/**
 * Gate: require any authenticated user.
 * Redirects to the login page with an informational message if not logged in.
 */
function requireLogin(): void
{
    if (!isLoggedIn()) {
        setFlash('info', 'Please log in to continue.');
        redirect(BASE_URL . '/login.php');
    }
}

// ── Flash messages ────────────────────────────────────────────────────────────

/**
 * Stores a single flash message in the session.
 *
 * @param string $type    One of: success | error | info | warning
 * @param string $message Human-readable message
 */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = compact('type', 'message');
}

/**
 * Retrieves and clears the flash message from the session.
 *
 * @return array{type:string,message:string}|null
 */
function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

// ── Input / output helpers ────────────────────────────────────────────────────

/**
 * Strips tags, trims whitespace, and HTML-encodes special characters.
 * Must be applied to every piece of user-supplied data rendered in HTML.
 *
 * @param string $data Raw user input
 * @return string Safe output string
 */
function sanitize(string $data): string
{
    return htmlspecialchars(
        strip_tags(trim($data)),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/**
 * Sends an HTTP Location redirect and terminates execution.
 *
 * @param string $url Absolute or root-relative URL
 */
function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

/**
 * Formats a numeric value as Indian Rupees.
 *
 * @param float $amount
 * @return string  e.g. "₹1,500.00"
 */
function formatCurrency(float $amount): string
{
    return '₹' . number_format($amount, 2);
}

/**
 * Maps a flash-message type to the corresponding Bootstrap 5 alert class.
 *
 * @param string $type
 * @return string Bootstrap alert modifier class
 */
function alertClass(string $type): string
{
    return match ($type) {
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        default   => 'alert-info',
    };
}

/**
 * Maps a flash type to a Font Awesome icon class.
 *
 * @param string $type
 * @return string FA icon class
 */
function flashIcon(string $type): string
{
    return match ($type) {
        'success' => 'fa-circle-check',
        'error'   => 'fa-circle-exclamation',
        'warning' => 'fa-triangle-exclamation',
        default   => 'fa-circle-info',
    };
}
