<?php
/**
 * Process: Login (Customer & Agency)
 *
 * Receives POST from the login page.
 * The hidden field `user_type` determines which table to query.
 * On success, the session is populated and the user is redirected to the
 * appropriate dashboard page.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/login.php');
}

// ── Collect input ───────────────────────────────────────────────────────────
$userType = $_POST['user_type'] ?? '';
$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';

// Validate user_type
if (!in_array($userType, ['customer', 'agency'], true)) {
    setFlash('error', 'Invalid login type. Please try again.');
    redirect(BASE_URL . '/login.php');
}

if ($email === '' || $password === '') {
    setFlash('error', 'Email and password are required.');
    $tab = $userType === 'agency' ? '?tab=agency' : '';
    redirect(BASE_URL . '/login.php' . $tab);
}

$conn = getDBConnection();

// ── Query the correct table based on user type ──────────────────────────────
if ($userType === 'customer') {
    $stmt = $conn->prepare(
        'SELECT id, full_name AS display_name, password
         FROM   customers
         WHERE  email = ?
         LIMIT  1'
    );
} else {
    $stmt = $conn->prepare(
        'SELECT id, agency_name AS display_name, password
         FROM   agencies
         WHERE  email = ?
         LIMIT  1'
    );
}

$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ── Verify password ─────────────────────────────────────────────────────────
$tab = $userType === 'agency' ? '?tab=agency' : '';

if (!$user || !password_verify($password, $user['password'])) {
    setFlash('error', 'Invalid email or password. Please try again.');
    redirect(BASE_URL . '/login.php' . $tab);
}

// ── Populate session ────────────────────────────────────────────────────────
session_regenerate_id(true);   // Mitigate session-fixation attacks

$_SESSION['user_id']   = (int)$user['id'];
$_SESSION['user_type'] = $userType;
$_SESSION['user_name'] = $user['display_name'];

// ── Redirect to the appropriate landing page ────────────────────────────────
if ($userType === 'agency') {
    setFlash('success', 'Welcome back, ' . $user['display_name'] . '!');
    redirect(BASE_URL . '/booked_cars.php');
} else {
    setFlash('success', 'Welcome back, ' . $user['display_name'] . '!');
    redirect(BASE_URL . '/index.php');
}
