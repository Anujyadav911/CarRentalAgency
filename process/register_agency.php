<?php
/**
 * Process: Agency Registration
 *
 * Validates and inserts a new agency record.
 * Redirects back to the registration page on error,
 * or to the agency login tab on success.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/register_agency.php');
}

// ── Collect & sanitise input ────────────────────────────────────────────────
$agencyName      = trim($_POST['agency_name']      ?? '');
$email           = trim($_POST['email']            ?? '');
$phone           = trim($_POST['phone']            ?? '');
$address         = trim($_POST['address']          ?? '');
$password        = $_POST['password']              ?? '';
$confirmPassword = $_POST['confirm_password']      ?? '';

// ── Server-side validation ──────────────────────────────────────────────────
if ($agencyName === '' || $email === '' || $password === '') {
    setFlash('error', 'Agency name, email and password are required.');
    redirect(BASE_URL . '/register_agency.php');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setFlash('error', 'Please enter a valid email address.');
    redirect(BASE_URL . '/register_agency.php');
}

if (strlen($password) < 8) {
    setFlash('error', 'Password must be at least 8 characters long.');
    redirect(BASE_URL . '/register_agency.php');
}

if ($password !== $confirmPassword) {
    setFlash('error', 'Passwords do not match. Please try again.');
    redirect(BASE_URL . '/register_agency.php');
}

$conn = getDBConnection();

// ── Check for duplicate email ───────────────────────────────────────────────
$stmt = $conn->prepare('SELECT id FROM agencies WHERE email = ?');
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    setFlash('error',
        'An agency account with this email already exists. Please log in.');
    redirect(BASE_URL . '/register_agency.php');
}
$stmt->close();

// ── Insert new agency ───────────────────────────────────────────────────────
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$phoneValue     = $phone   !== '' ? $phone   : null;
$addressValue   = $address !== '' ? $address : null;

$stmt = $conn->prepare(
    'INSERT INTO agencies (agency_name, email, password, phone, address)
     VALUES (?, ?, ?, ?, ?)'
);
$stmt->bind_param(
    'sssss',
    $agencyName,
    $email,
    $hashedPassword,
    $phoneValue,
    $addressValue
);

if ($stmt->execute()) {
    $stmt->close();
    setFlash('success',
        'Agency registration successful! Please log in to add your vehicles.');
    redirect(BASE_URL . '/login.php?tab=agency');
} else {
    $stmt->close();
    setFlash('error', 'Registration failed. Please try again later.');
    redirect(BASE_URL . '/register_agency.php');
}
