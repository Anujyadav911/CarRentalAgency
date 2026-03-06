<?php
/**
 * Available Cars Page
 *
 * Public page — visible to all visitors.
 * - Guests see a "Login to Rent" button.
 * - Logged-in customers see a full booking form on each card.
 * - Logged-in agencies see a disabled "Agencies cannot book" notice.
 * - Agencies also see Edit buttons on their own cars.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Available Cars';
$conn      = getDBConnection();

// ── Fetch all cars with agency name ─────────────────────────────────────────
$stmt = $conn->prepare(
    'SELECT c.id,
            c.agency_id,
            c.vehicle_model,
            c.vehicle_number,
            c.seating_capacity,
            c.rent_per_day,
            c.is_available,
            a.agency_name
     FROM   cars     c
     JOIN   agencies a ON a.id = c.agency_id
     ORDER  BY c.is_available DESC, c.created_at DESC'
);
$stmt->execute();
$cars = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$loggedAgencyId = isAgency() ? (int)$_SESSION['user_id'] : 0;

require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Hero ─────────────────────────────────────────────────────────────────── -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="mb-2">
            <i class="fas fa-car-side me-2 text-warning"></i>Find Your Perfect Ride
        </h1>
        <p class="mb-0">
            Browse our wide selection of rental cars and book your next journey in seconds.
        </p>
    </div>
</section>

<!-- ── Cars Grid ─────────────────────────────────────────────────────────────  -->
<div class="container pb-5">

    <!-- Toolbar -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="fw-700 mb-0 text-primary-custom">
            <i class="fas fa-list me-2"></i>
            <?= count($cars) ?> Car<?= count($cars) !== 1 ? 's' : '' ?> Listed
        </h4>
        <?php if (isAgency()): ?>
        <a href="<?= BASE_URL ?>/add_car.php" class="btn btn-accent">
            <i class="fas fa-plus me-1"></i>Add New Car
        </a>
        <?php endif; ?>
    </div>

    <?php if (empty($cars)): ?>
    <!-- Empty State -->
    <div class="empty-state">
        <div class="empty-icon"><i class="fas fa-car"></i></div>
        <h5>No cars listed yet</h5>
        <p>Check back soon, or register as an agency to list your vehicles.</p>
    </div>

    <?php else: ?>

    <div class="row g-4">
        <?php foreach ($cars as $car): ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="car-card card">

                <!-- Card Header -->
                <div class="card-header d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="fw-700 mb-1"><?= sanitize($car['vehicle_model']) ?></h5>
                        <small class="opacity-75">
                            <i class="fas fa-building me-1"></i>
                            <?= sanitize($car['agency_name']) ?>
                        </small>
                    </div>
                    <i class="fas fa-car car-icon"></i>
                </div>

                <!-- Card Body -->
                <div class="card-body d-flex flex-column">

                    <!-- Details -->
                    <div class="mb-3">
                        <div class="car-detail-row">
                            <i class="fas fa-id-card text-primary-custom fa-fw"></i>
                            <span class="label">Vehicle No.</span>
                            <span class="value"><?= sanitize($car['vehicle_number']) ?></span>
                        </div>
                        <div class="car-detail-row">
                            <i class="fas fa-users text-primary-custom fa-fw"></i>
                            <span class="label">Seating Capacity</span>
                            <span class="value"><?= (int)$car['seating_capacity'] ?> Persons</span>
                        </div>
                        <div class="car-detail-row border-0">
                            <i class="fas fa-tag text-primary-custom fa-fw"></i>
                            <span class="label">Rent Per Day</span>
                            <span class="rent-price">
                                <?= formatCurrency((float)$car['rent_per_day']) ?>
                                <small>/day</small>
                            </span>
                        </div>
                    </div>

                    <!-- Availability badge -->
                    <div class="mb-3">
                        <?php if ($car['is_available']): ?>
                        <span class="badge-available">
                            <i class="fas fa-circle-check me-1"></i>Available
                        </span>
                        <?php else: ?>
                        <span class="badge-unavailable">
                            <i class="fas fa-circle-xmark me-1"></i>Not Available
                        </span>
                        <?php endif; ?>
                    </div>

                    <!-- ── Booking / Action section ─────────────────────── -->
                    <div class="mt-auto">
                        <?php if ($car['is_available']): ?>

                            <?php if (isCustomer()): ?>
                            <!-- CUSTOMER: full booking form -->
                            <form action="<?= BASE_URL ?>/process/book_car.php"
                                  method="POST"
                                  class="booking-form"
                                  data-price-per-day="<?= (float)$car['rent_per_day'] ?>">

                                <input type="hidden" name="car_id"
                                       value="<?= (int)$car['id'] ?>">

                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label">
                                            <i class="fas fa-calendar-day me-1"></i>Start Date
                                        </label>
                                        <input type="date"
                                               name="start_date"
                                               class="form-control form-control-sm start-date future-date"
                                               min="<?= date('Y-m-d') ?>"
                                               required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label">
                                            <i class="fas fa-clock me-1"></i>No. of Days
                                        </label>
                                        <select name="num_days"
                                                class="form-select form-select-sm days-select"
                                                required>
                                            <?php for ($d = 1; $d <= 30; $d++): ?>
                                            <option value="<?= $d ?>">
                                                <?= $d ?> Day<?= $d > 1 ? 's' : '' ?>
                                            </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="total-row">
                                    <small class="text-muted">Total Estimate:</small>
                                    <strong class="total-amount"></strong>
                                </div>

                                <button type="submit" class="btn btn-rent mt-2">
                                    <i class="fas fa-key me-1"></i>Rent This Car
                                </button>
                            </form>

                            <?php elseif (isAgency()): ?>
                            <!-- AGENCY: cannot book, show notice -->
                            <div class="d-grid">
                                <button class="btn btn-secondary" disabled
                                        data-bs-toggle="tooltip"
                                        title="Agencies cannot book cars">
                                    <i class="fas fa-ban me-1"></i>
                                    Not available for agencies
                                </button>
                            </div>

                            <?php else: ?>
                            <!-- GUEST: redirect to login -->
                            <a href="<?= BASE_URL ?>/login.php"
                               class="btn btn-primary-custom w-100">
                                <i class="fas fa-sign-in-alt me-1"></i>Login to Rent
                            </a>
                            <?php endif; ?>

                        <?php else: ?>
                        <!-- Car not available -->
                        <button class="btn btn-secondary w-100" disabled>
                            <i class="fas fa-circle-xmark me-1"></i>Currently Unavailable
                        </button>
                        <?php endif; ?>

                        <!-- Agency: edit own cars -->
                        <?php if ($loggedAgencyId && (int)$car['agency_id'] === $loggedAgencyId): ?>
                        <a href="<?= BASE_URL ?>/edit_car.php?id=<?= (int)$car['id'] ?>"
                           class="btn btn-outline-secondary btn-sm w-100 mt-2">
                            <i class="fas fa-pen-to-square me-1"></i>Edit Car Details
                        </a>
                        <?php endif; ?>
                    </div><!-- /.mt-auto -->

                </div><!-- /.card-body -->
            </div><!-- /.car-card -->
        </div><!-- /.col -->
        <?php endforeach; ?>
    </div><!-- /.row -->

    <?php endif; ?>
</div><!-- /.container -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
