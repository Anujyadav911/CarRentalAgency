<?php
/**
 * Edit Car Page  (Agency only)
 *
 * Pre-fills a form with the selected car's current details.
 * The car must belong to the logged-in agency (ownership enforced server-side).
 * POSTs to /process/edit_car.php.
 *
 * URL: /edit_car.php?id=<car_id>
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireAgency();

$carId  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$conn   = getDBConnection();

// ── Fetch the car — must belong to the logged-in agency ─────────────────────
$stmt = $conn->prepare(
    'SELECT id, vehicle_model, vehicle_number, seating_capacity,
            rent_per_day, is_available
     FROM   cars
     WHERE  id = ? AND agency_id = ?'
);
$stmt->bind_param('ii', $carId, $_SESSION['user_id']);
$stmt->execute();
$car = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$car) {
    setFlash('error', 'Car not found or you do not have permission to edit it.');
    redirect(BASE_URL . '/booked_cars.php');
}

$pageTitle = 'Edit Car';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container pb-5">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2><i class="fas fa-pen-to-square me-2"></i>Edit Car Details</h2>
            <p class="mb-0 opacity-75 small">
                Update the information for
                <strong><?= sanitize($car['vehicle_model']) ?></strong>
                (<?= sanitize($car['vehicle_number']) ?>)
            </p>
        </div>
        <a href="<?= BASE_URL ?>/booked_cars.php" class="btn btn-outline-light btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Booked Cars
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 rounded-lg shadow-custom">
                <div class="card-body p-4">

                    <form action="<?= BASE_URL ?>/process/edit_car.php"
                          method="POST"
                          novalidate>

                        <!-- Hidden car ID -->
                        <input type="hidden" name="car_id" value="<?= (int)$car['id'] ?>">

                        <!-- Vehicle Model -->
                        <div class="mb-3">
                            <label for="vehicle_model" class="form-label">
                                <i class="fas fa-car me-1"></i>Vehicle Model
                            </label>
                            <input type="text"
                                   id="vehicle_model"
                                   name="vehicle_model"
                                   class="form-control"
                                   value="<?= sanitize($car['vehicle_model']) ?>"
                                   maxlength="100"
                                   required>
                        </div>

                        <!-- Vehicle Number -->
                        <div class="mb-3">
                            <label for="vehicle_number" class="form-label">
                                <i class="fas fa-id-card me-1"></i>Vehicle Number
                            </label>
                            <input type="text"
                                   id="vehicle_number"
                                   name="vehicle_number"
                                   class="form-control text-uppercase"
                                   value="<?= sanitize($car['vehicle_number']) ?>"
                                   maxlength="50"
                                   required>
                        </div>

                        <!-- Seating Capacity -->
                        <div class="mb-3">
                            <label for="seating_capacity" class="form-label">
                                <i class="fas fa-users me-1"></i>Seating Capacity
                            </label>
                            <select id="seating_capacity"
                                    name="seating_capacity"
                                    class="form-select"
                                    required>
                                <?php foreach ([2, 4, 5, 6, 7, 8, 9, 10, 12] as $seats): ?>
                                <option value="<?= $seats ?>"
                                    <?= (int)$car['seating_capacity'] === $seats ? 'selected' : '' ?>>
                                    <?= $seats ?> Persons
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Rent Per Day -->
                        <div class="mb-3">
                            <label for="rent_per_day" class="form-label">
                                <i class="fas fa-rupee-sign me-1"></i>Rent Per Day (₹)
                            </label>
                            <input type="number"
                                   id="rent_per_day"
                                   name="rent_per_day"
                                   class="form-control"
                                   value="<?= number_format((float)$car['rent_per_day'], 2, '.', '') ?>"
                                   min="1"
                                   step="0.01"
                                   required>
                        </div>

                        <!-- Availability Toggle -->
                        <div class="mb-4">
                            <label class="form-label d-block">
                                <i class="fas fa-toggle-on me-1"></i>Availability Status
                            </label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                       type="radio"
                                       name="is_available"
                                       id="avail_yes"
                                       value="1"
                                    <?= $car['is_available'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="avail_yes">
                                    <i class="fas fa-circle-check text-success me-1"></i>
                                    Available
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input"
                                       type="radio"
                                       name="is_available"
                                       id="avail_no"
                                       value="0"
                                    <?= !$car['is_available'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="avail_no">
                                    <i class="fas fa-circle-xmark text-danger me-1"></i>
                                    Not Available
                                </label>
                            </div>
                            <small class="form-text text-muted d-block mt-1">
                                Mark as "Available" to allow customers to rent this car again.
                            </small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fas fa-save me-1"></i>Save Changes
                            </button>
                            <a href="<?= BASE_URL ?>/booked_cars.php"
                               class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

</div><!-- /.container -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
