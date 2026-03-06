<?php
/**
 * Process: Add Car
 *
 * Validates the submitted car details, verifies the user is an agency,
 * and inserts the new car into the database.
 * Restricted to authenticated agency users only.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/add_car.php');
}

// ── Auth guard ──────────────────────────────────────────────────────────────
requireAgency();

// ── Collect & sanitise input ────────────────────────────────────────────────
$vehicleModel    = trim($_POST['vehicle_model']    ?? '');
$vehicleNumber   = strtoupper(trim($_POST['vehicle_number'] ?? ''));
$seatingCapacity = (int)($_POST['seating_capacity']         ?? 0);
$rentPerDay      = (float)($_POST['rent_per_day']           ?? 0);
$agencyId        = (int)$_SESSION['user_id'];

// ── Validation ──────────────────────────────────────────────────────────────
if ($vehicleModel === '' || $vehicleNumber === '') {
    setFlash('error', 'Vehicle model and number are required.');
    redirect(BASE_URL . '/add_car.php');
}

if ($seatingCapacity < 1 || $seatingCapacity > 50) {
    setFlash('error', 'Please select a valid seating capacity.');
    redirect(BASE_URL . '/add_car.php');
}

if ($rentPerDay <= 0) {
    setFlash('error', 'Rent per day must be a positive amount.');
    redirect(BASE_URL . '/add_car.php');
}

$conn = getDBConnection();

// ── Check for duplicate vehicle number ─────────────────────────────────────
$stmt = $conn->prepare('SELECT id FROM cars WHERE vehicle_number = ?');
$stmt->bind_param('s', $vehicleNumber);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    setFlash('error',
        "A car with vehicle number \"$vehicleNumber\" is already registered.");
    redirect(BASE_URL . '/add_car.php');
}
$stmt->close();

// ── Insert the new car ──────────────────────────────────────────────────────
$stmt = $conn->prepare(
    'INSERT INTO cars
        (agency_id, vehicle_model, vehicle_number, seating_capacity, rent_per_day)
     VALUES (?, ?, ?, ?, ?)'
);
$stmt->bind_param(
    'issid',
    $agencyId,
    $vehicleModel,
    $vehicleNumber,
    $seatingCapacity,
    $rentPerDay
);

if ($stmt->execute()) {
    $stmt->close();
    setFlash('success',
        "\"$vehicleModel\" has been added to your fleet successfully!");
    redirect(BASE_URL . '/booked_cars.php');
} else {
    $stmt->close();
    setFlash('error', 'Failed to add the car. Please try again.');
    redirect(BASE_URL . '/add_car.php');
}
