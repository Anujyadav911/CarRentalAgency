<?php
/**
 * Booked Cars Page  (Agency only)
 *
 * Displays all cars added by the logged-in agency.
 * For each car an accordion section lists every booking with:
 *   customer name, email, phone, start date, number of days,
 *   total amount, and booking status.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireAgency();

$pageTitle  = 'Booked Cars';
$agencyId   = (int)$_SESSION['user_id'];
$conn       = getDBConnection();

// ── Fetch agency's cars with booking count ────────────────────────────────────
$stmt = $conn->prepare(
    'SELECT c.id,
            c.vehicle_model,
            c.vehicle_number,
            c.seating_capacity,
            c.rent_per_day,
            c.is_available,
            COUNT(b.id) AS booking_count
     FROM   cars c
     LEFT   JOIN bookings b ON b.car_id = c.id
     WHERE  c.agency_id = ?
     GROUP  BY c.id
     ORDER  BY c.created_at DESC'
);
$stmt->bind_param('i', $agencyId);
$stmt->execute();
$cars = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ── Fetch all bookings for this agency's cars ──────────────────────────────────
$stmt = $conn->prepare(
    'SELECT b.id              AS booking_id,
            b.car_id,
            b.start_date,
            b.num_days,
            b.total_amount,
            b.status,
            b.booked_at,
            cu.full_name      AS customer_name,
            cu.email          AS customer_email,
            cu.phone          AS customer_phone
     FROM   bookings  b
     JOIN   cars      c  ON  c.id = b.car_id
     JOIN   customers cu ON cu.id = b.customer_id
     WHERE  c.agency_id = ?
     ORDER  BY b.booked_at DESC'
);
$stmt->bind_param('i', $agencyId);
$stmt->execute();
$allBookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Group bookings by car_id for quick lookup
$bookingsByCarId = [];
foreach ($allBookings as $booking) {
    $bookingsByCarId[$booking['car_id']][] = $booking;
}

// Summary stats
$totalCars     = count($cars);
$totalBookings = count($allBookings);
$totalRevenue  = array_sum(array_column($allBookings, 'total_amount'));

require_once __DIR__ . '/includes/header.php';
?>

<div class="container pb-5">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2><i class="fas fa-clipboard-list me-2"></i>Booked Cars Overview</h2>
            <p class="mb-0 opacity-75 small">
                All vehicles in your fleet and their rental history.
            </p>
        </div>
        <a href="<?= BASE_URL ?>/add_car.php" class="btn btn-accent btn-sm">
            <i class="fas fa-plus me-1"></i>Add New Car
        </a>
    </div>

    <!-- ── Summary Stats ──────────────────────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="icon-wrap" style="background:#eef2fb;">
                    <i class="fas fa-car text-primary-custom"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-count"><?= $totalCars ?></div>
                    <div class="stat-label">Cars in Fleet</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="icon-wrap" style="background:#fff8e7;">
                    <i class="fas fa-calendar-check text-warning"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-count"><?= $totalBookings ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="icon-wrap" style="background:#eafaf1;">
                    <i class="fas fa-rupee-sign text-success"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-count" style="font-size:1.4rem;">
                        <?= formatCurrency($totalRevenue) ?>
                    </div>
                    <div class="stat-label">Total Revenue</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Cars Accordion ───────────────────────────────────────────────────  -->
    <?php if (empty($cars)): ?>
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-car"></i></div>
        <h5>No cars in your fleet yet</h5>
        <p>Add your first vehicle to start receiving bookings.</p>
        <a href="<?= BASE_URL ?>/add_car.php" class="btn btn-primary-custom">
            <i class="fas fa-plus me-1"></i>Add Your First Car
        </a>
    </div>

    <?php else: ?>

    <div class="accordion car-accordion" id="carsAccordion">
        <?php foreach ($cars as $index => $car): ?>
        <?php
            $carBookings   = $bookingsByCarId[$car['id']] ?? [];
            $bookingCount  = count($carBookings);
            $collapseId    = 'car-collapse-' . $car['id'];
            $headingId     = 'car-heading-' . $car['id'];
        ?>
        <div class="accordion-item">

            <!-- Accordion Header -->
            <h2 class="accordion-header" id="<?= $headingId ?>">
                <button class="accordion-button <?= $index > 0 ? 'collapsed' : '' ?>"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#<?= $collapseId ?>"
                        aria-expanded="<?= $index === 0 ? 'true' : 'false' ?>"
                        aria-controls="<?= $collapseId ?>">

                    <div class="d-flex align-items-center gap-3 flex-wrap w-100 me-3">

                        <!-- Car icon & model -->
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-car text-primary-custom fa-lg"></i>
                            <div>
                                <div class="fw-700"><?= sanitize($car['vehicle_model']) ?></div>
                                <small class="text-muted fw-500">
                                    <?= sanitize($car['vehicle_number']) ?>
                                    &bull; <?= (int)$car['seating_capacity'] ?> Seats
                                    &bull; <?= formatCurrency((float)$car['rent_per_day']) ?>/day
                                </small>
                            </div>
                        </div>

                        <!-- Right side badges -->
                        <div class="ms-auto d-flex align-items-center gap-2 flex-shrink-0">
                            <?php if ($car['is_available']): ?>
                            <span class="badge-available">
                                <i class="fas fa-circle-check me-1"></i>Available
                            </span>
                            <?php else: ?>
                            <span class="badge-unavailable">
                                <i class="fas fa-circle-xmark me-1"></i>Not Available
                            </span>
                            <?php endif; ?>

                            <span class="badge bg-primary rounded-pill">
                                <?= $bookingCount ?> Booking<?= $bookingCount !== 1 ? 's' : '' ?>
                            </span>
                        </div>
                    </div>
                </button>
            </h2>

            <!-- Accordion Body -->
            <div id="<?= $collapseId ?>"
                 class="accordion-collapse collapse <?= $index === 0 ? 'show' : '' ?>"
                 aria-labelledby="<?= $headingId ?>"
                 data-bs-parent="#carsAccordion">
                <div class="accordion-body p-0">

                    <!-- Car Actions -->
                    <div class="d-flex justify-content-end gap-2 p-3 border-bottom bg-light">
                        <a href="<?= BASE_URL ?>/edit_car.php?id=<?= (int)$car['id'] ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-pen-to-square me-1"></i>Edit Car
                        </a>
                    </div>

                    <?php if (empty($carBookings)): ?>
                    <!-- No bookings for this car -->
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                        <p class="mb-0 small">No bookings yet for this vehicle.</p>
                    </div>

                    <?php else: ?>
                    <!-- Bookings Table -->
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">#</th>
                                    <th>Customer</th>
                                    <th>Contact</th>
                                    <th>Start Date</th>
                                    <th>Days</th>
                                    <th>Total Amount</th>
                                    <th>Status</th>
                                    <th>Booked On</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($carBookings as $bi => $booking): ?>
                                <tr>
                                    <td class="ps-3 text-muted"><?= $bi + 1 ?></td>
                                    <td>
                                        <div class="fw-600">
                                            <?= sanitize($booking['customer_name']) ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= sanitize($booking['customer_email']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($booking['customer_phone']): ?>
                                        <small>
                                            <i class="fas fa-phone fa-xs me-1 text-muted"></i>
                                            <?= sanitize($booking['customer_phone']) ?>
                                        </small>
                                        <?php else: ?>
                                        <small class="text-muted">—</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= date('d M Y',
                                            strtotime($booking['start_date'])) ?>
                                    </td>
                                    <td><?= (int)$booking['num_days'] ?> day<?= $booking['num_days'] > 1 ? 's' : '' ?></td>
                                    <td class="fw-600 text-success">
                                        <?= formatCurrency((float)$booking['total_amount']) ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $booking['status'] ?>">
                                            <?= ucfirst($booking['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d M Y', strtotime($booking['booked_at'])) ?>
                                        </small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>

                </div><!-- /.accordion-body -->
            </div><!-- /.accordion-collapse -->
        </div><!-- /.accordion-item -->
        <?php endforeach; ?>
    </div><!-- /.accordion -->

    <?php endif; ?>
</div><!-- /.container -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
