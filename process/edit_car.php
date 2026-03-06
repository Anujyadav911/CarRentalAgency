<?php
/**
 * Process: Edit Car
 *
 * Updates an existing car's details.
 * The car must belong to the logged-in agency (ownership enforced here).
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/booked_cars.php');
}

// ── Auth guard ──────────────────────────────────────────────────────────────
requireAgency();

// ── Collect input ───────────────────────────────────────────────────────────
$carId           = (int)($_POST['car_id']           ?? 0);
$vehicleModel    = trim($_POST['vehicle_model']     ?? '');
$vehicleNumber   = strtoupper(trim($_POST['vehicle_number'] ?? ''));
$seatingCapacity = (int)($_POST['seating_capacity'] ?? 0);
$rentPerDay      = (float)($_POST['rent_per_day']   ?? 0);
$isAvailable     = isset($_POST['is_available']) ? (int)$_POST['is_available'] : 0;
$agencyId        = (int)$_SESSION['user_id'];

// ── Validation ──────────────────────────────────────────────────────────────
if ($carId < 1 || $vehicleModel === '' || $vehicleNumber === '') {
    setFlash('error', 'Vehicle model and number are required.');
    redirect(BASE_URL . '/edit_car.php?id=' . $carId);
}

if ($seatingCapacity < 1 || $seatingCapacity > 50) {
    setFlash('error', 'Please select a valid seating capacity.');
    redirect(BASE_URL . '/edit_car.php?id=' . $carId);
}

if ($rentPerDay <= 0) {
    setFlash('error', 'Rent per day must be a positive amount.');
    redirect(BASE_URL . '/edit_car.php?id=' . $carId);
}

$conn = getDBConnection();

// ── Verify ownership ────────────────────────────────────────────────────────
$stmt = $conn->prepare(
    'SELECT id FROM cars WHERE id = ? AND agency_id = ?'
);
$stmt->bind_param('ii', $carId, $agencyId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt->close();
    setFlash('error', 'Car not found or you do not have permission to edit it.');
    redirect(BASE_URL . '/booked_cars.php');
}
$stmt->close();

// ── Check duplicate vehicle number (exclude this car) ──────────────────────
$stmt = $conn->prepare(
    'SELECT id FROM cars WHERE vehicle_number = ? AND id != ?'
);
$stmt->bind_param('si', $vehicleNumber, $carId);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->close();
    setFlash('error',
        "Vehicle number \"$vehicleNumber\" is already in use by another car.");
    redirect(BASE_URL . '/edit_car.php?id=' . $carId);
}
$stmt->close();

// ── Perform the update ──────────────────────────────────────────────────────
$stmt = $conn->prepare(
    'UPDATE cars
     SET    vehicle_model    = ?,
            vehicle_number   = ?,
            seating_capacity = ?,
            rent_per_day     = ?,
            is_available     = ?
     WHERE  id = ? AND agency_id = ?'
);
$stmt->bind_param(
    'ssiidii',
    $vehicleModel,
    $vehicleNumber,
    $seatingCapacity,
    $rentPerDay,
    $isAvailable,
    $carId,
    $agencyId
);

if ($stmt->execute()) {
    $stmt->close();
    setFlash('success', "\"$vehicleModel\" has been updated successfully.");
    redirect(BASE_URL . '/booked_cars.php');
} else {
    $stmt->close();
    setFlash('error', 'Failed to update the car. Please try again.');
    redirect(BASE_URL . '/edit_car.php?id=' . $carId);
}
