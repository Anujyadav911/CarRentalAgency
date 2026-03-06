<?php
/**
 * Process: Book Car
 *
 * Handles a customer's car rental booking request.
 *
 * Business rules enforced:
 *  - Only authenticated customers may book.
 *  - Agency users are explicitly rejected.
 *  - The selected car must exist and be currently available.
 *  - Start date must not be in the past.
 *  - Number of days must be between 1 and 30.
 *  - On success: inserts a booking record and marks the car as unavailable.
 */

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/index.php');
}

// ── Auth checks ─────────────────────────────────────────────────────────────

// Agencies are not allowed to book
if (isAgency()) {
    setFlash('error', 'Agencies cannot book cars. Only customers can rent vehicles.');
    redirect(BASE_URL . '/index.php');
}

// Guests must log in first
if (!isCustomer()) {
    setFlash('info', 'Please log in as a customer to rent a car.');
    redirect(BASE_URL . '/login.php');
}

// ── Collect input ───────────────────────────────────────────────────────────
$carId      = (int)($_POST['car_id']    ?? 0);
$startDate  = trim($_POST['start_date'] ?? '');
$numDays    = (int)($_POST['num_days']  ?? 0);
$customerId = (int)$_SESSION['user_id'];

// ── Validation ──────────────────────────────────────────────────────────────
if ($carId < 1) {
    setFlash('error', 'Invalid car selection. Please try again.');
    redirect(BASE_URL . '/index.php');
}

// Validate date format and ensure it's not in the past
$startDateObj = \DateTimeImmutable::createFromFormat('Y-m-d', $startDate);
$today        = new \DateTimeImmutable('today');

if (!$startDateObj || $startDateObj->format('Y-m-d') !== $startDate) {
    setFlash('error', 'Please select a valid start date.');
    redirect(BASE_URL . '/index.php');
}

if ($startDateObj < $today) {
    setFlash('error', 'Start date cannot be in the past.');
    redirect(BASE_URL . '/index.php');
}

if ($numDays < 1 || $numDays > 30) {
    setFlash('error', 'Number of rental days must be between 1 and 30.');
    redirect(BASE_URL . '/index.php');
}

$conn = getDBConnection();

// ── Fetch car and verify availability (with row-level lock) ─────────────────
$conn->begin_transaction();

try {
    $stmt = $conn->prepare(
        'SELECT id, vehicle_model, rent_per_day, is_available
         FROM   cars
         WHERE  id = ?
         FOR UPDATE'
    );
    $stmt->bind_param('i', $carId);
    $stmt->execute();
    $car = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$car) {
        $conn->rollback();
        setFlash('error', 'The selected car could not be found.');
        redirect(BASE_URL . '/index.php');
    }

    if (!$car['is_available']) {
        $conn->rollback();
        setFlash('error',
            '"' . $car['vehicle_model'] . '" is no longer available. '
            . 'Please choose another car.');
        redirect(BASE_URL . '/index.php');
    }

    // ── Calculate total ─────────────────────────────────────────────────────
    $totalAmount = round((float)$car['rent_per_day'] * $numDays, 2);

    // ── Insert booking record ───────────────────────────────────────────────
    $stmt = $conn->prepare(
        'INSERT INTO bookings
            (car_id, customer_id, start_date, num_days, total_amount)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->bind_param(
        'iisid',
        $carId,
        $customerId,
        $startDate,
        $numDays,
        $totalAmount
    );
    $stmt->execute();
    $stmt->close();

    // ── Mark car as unavailable ─────────────────────────────────────────────
    $stmt = $conn->prepare('UPDATE cars SET is_available = 0 WHERE id = ?');
    $stmt->bind_param('i', $carId);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    setFlash(
        'success',
        sprintf(
            '🎉 Booking confirmed! "%s" is reserved for %d day%s starting %s. '
            . 'Total: %s',
            $car['vehicle_model'],
            $numDays,
            $numDays > 1 ? 's' : '',
            date('d M Y', strtotime($startDate)),
            formatCurrency($totalAmount)
        )
    );
    redirect(BASE_URL . '/my_bookings.php');

} catch (\Throwable $e) {
    $conn->rollback();
    setFlash('error', 'Booking failed due to a server error. Please try again.');
    redirect(BASE_URL . '/index.php');
}
