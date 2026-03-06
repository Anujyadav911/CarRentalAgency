<?php
/**
 * Process: Customer Registration
 *
 * Validates and inserts a new customer record.
 * Redirects back to the registration page on error,
 * or to the login page on success.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/register_customer.php');
}

// ── Collect & sanitise input ────────────────────────────────────────────────
$fullName        = trim($_POST['full_name']        ?? '');
$email           = trim($_POST['email']            ?? '');
$phone           = trim($_POST['phone']            ?? '');
$password        = $_POST['password']              ?? '';
$confirmPassword = $_POST['confirm_password']      ?? '';

// ── Server-side validation ──────────────────────────────────────────────────
if ($fullName === '' || $email === '' || $password === '') {
    setFlash('error', 'Full name, email and password are required.');
    redirect(BASE_URL . '/register_customer.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Please enter a valid email address.');
    redirect(BASE_URL . '/register_customer.php');
}

if (strlen($password) < 8) {
    setFlash('error', 'Password must be at least 8 characters long.');
    redirect(BASE_URL . '/register_customer.php');
}

if ($password !== $confirmPassword) {
    setFlash('error', 'Passwords do not match. Please try again.');
    redirect(BASE_URL . '/register_customer.php');
}

$conn = getDBConnection();

// ── Check for duplicate email ───────────────────────────────────────────────
$stmt = $conn->prepare('SELECT id FROM customers WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    setFlash('error', 'An account with this email already exists. Please log in.');
    redirect(BASE_URL . '/register_customer.php');
}
$stmt->close();

// ── Insert new customer ─────────────────────────────────────────────────────
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$phoneValue     = $phone !== '' ? $phone : null;

$stmt = $conn->prepare(
    'INSERT INTO customers (full_name, email, password, phone)
     VALUES (?, ?, ?, ?)'
);
$stmt->bind_param('ssss', $fullName, $email, $hashedPassword, $phoneValue);

if ($stmt->execute()) {
    $stmt->close();
    setFlash('success',
        'Registration successful! Please log in with your new account.');
    redirect(BASE_URL . '/login.php');
} else {
    $stmt->close();
    setFlash('error', 'Registration failed. Please try again later.');
    redirect(BASE_URL . '/register_customer.php');
}
