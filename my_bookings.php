<?php
/**
 * My Bookings Page  (Customer only)
 *
 * Shows all rental bookings made by the currently logged-in customer,
 * sorted by most recent first.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireCustomer();

$pageTitle   = 'My Bookings';
$customerId  = (int)$_SESSION['user_id'];
$conn        = getDBConnection();

// ── Fetch all bookings for this customer ──────────────────────────────────────
$stmt = $conn->prepare(
    'SELECT b.id,
            b.start_date,
            b.num_days,
            b.total_amount,
            b.status,
            b.booked_at,
            c.vehicle_model,
            c.vehicle_number,
            c.seating_capacity,
            c.rent_per_day,
            a.agency_name
     FROM   bookings b
     JOIN   cars     c  ON c.id  = b.car_id
     JOIN   agencies a  ON a.id  = c.agency_id
     WHERE  b.customer_id = ?
     ORDER  BY b.booked_at DESC'
);
$stmt->bind_param('i', $customerId);
$stmt->execute();
$bookings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Stats
$totalBookings = count($bookings);
$totalSpent    = array_sum(array_column($bookings, 'total_amount'));
$activeCount   = count(array_filter($bookings,
    fn($b) => $b['status'] === 'active'));

require_once __DIR__ . '/includes/header.php';
?>

<div class="container pb-5">

    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="fas fa-history me-2"></i>My Bookings</h2>
        <p class="mb-0 opacity-75 small">
            A complete history of all your car rental bookings.
        </p>
    </div>

    <!-- ── Summary Stats ──────────────────────────────────────────────────── -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="icon-wrap" style="background:#eef2fb;">
                    <i class="fas fa-calendar-check text-primary-custom"></i>
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
                    <i class="fas fa-car-on text-success"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-count"><?= $activeCount ?></div>
                    <div class="stat-label">Active Rentals</div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="stat-card">
                <div class="icon-wrap" style="background:#fff8e7;">
                    <i class="fas fa-rupee-sign text-warning"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-count" style="font-size:1.4rem;">
                        <?= formatCurrency($totalSpent) ?>
                    </div>
                    <div class="stat-label">Total Spent</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Bookings Table ────────────────────────────────────────────────── -->
    <?php if (empty($bookings)): ?>
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-calendar-xmark"></i></div>
        <h5>No bookings yet</h5>
        <p>Browse available cars and make your first booking today!</p>
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-primary-custom">
            <i class="fas fa-car me-1"></i>Browse Available Cars
        </a>
    </div>

    <?php else: ?>

    <div class="table-responsive rounded-lg shadow-custom">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th class="ps-3">#</th>
                    <th>Vehicle</th>
                    <th>Agency</th>
                    <th>Start Date</th>
                    <th>Duration</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Booked On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $i => $booking): ?>
                <tr>
                    <td class="ps-3 text-muted"><?= $i + 1 ?></td>
                    <td>
                        <div class="fw-600">
                            <?= sanitize($booking['vehicle_model']) ?>
                        </div>
                        <small class="text-muted">
                            <i class="fas fa-id-card fa-xs me-1"></i>
                            <?= sanitize($booking['vehicle_number']) ?>
                            &bull; <?= (int)$booking['seating_capacity'] ?> seats
                        </small>
                    </td>
                    <td>
                        <small>
                            <i class="fas fa-building fa-xs me-1 text-muted"></i>
                            <?= sanitize($booking['agency_name']) ?>
                        </small>
                    </td>
                    <td>
                        <?= date('d M Y', strtotime($booking['start_date'])) ?>
                    </td>
                    <td>
                        <?= (int)$booking['num_days'] ?>
                        day<?= $booking['num_days'] > 1 ? 's' : '' ?>
                        <br>
                        <small class="text-muted">
                            (<?= formatCurrency((float)$booking['rent_per_day']) ?>/day)
                        </small>
                    </td>
                    <td class="fw-700 text-success">
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

    <p class="text-muted small mt-3 text-end">
        Showing <?= $totalBookings ?> booking<?= $totalBookings !== 1 ? 's' : '' ?>.
    </p>

    <?php endif; ?>

    <div class="mt-3">
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-primary">
            <i class="fas fa-search me-1"></i>Browse More Cars
        </a>
    </div>

</div><!-- /.container -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
