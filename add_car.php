<?php
/**
 * Add New Car Page  (Agency only)
 *
 * Displays a form to add a vehicle to the agency's fleet.
 * Access is restricted to authenticated agency users.
 * POSTs to /process/add_car.php.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

requireAgency();   // Redirect to login if not an agency

$pageTitle = 'Add New Car';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container pb-5">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div>
            <h2><i class="fas fa-plus-circle me-2"></i>Add New Car</h2>
            <p class="mb-0 opacity-75 small">
                Fill in the details below to list a new vehicle for rental.
            </p>
        </div>
        <a href="<?= BASE_URL ?>/index.php" class="btn btn-outline-light btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Cars
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border-0 rounded-lg shadow-custom">
                <div class="card-body p-4">

                    <form action="<?= BASE_URL ?>/process/add_car.php"
                          method="POST"
                          novalidate>

                        <!-- Vehicle Model -->
                        <div class="mb-3">
                            <label for="vehicle_model" class="form-label">
                                <i class="fas fa-car me-1"></i>Vehicle Model
                            </label>
                            <input type="text"
                                   id="vehicle_model"
                                   name="vehicle_model"
                                   class="form-control"
                                   placeholder="e.g. Toyota Innova Crysta"
                                   maxlength="100"
                                   required>
                        </div>

                        <!-- Vehicle Number -->
                        <div class="mb-3">
                            <label for="vehicle_number" class="form-label">
                                <i class="fas fa-id-card me-1"></i>Vehicle Number
                                <span class="text-muted fw-400 small">(Registration Plate)</span>
                            </label>
                            <input type="text"
                                   id="vehicle_number"
                                   name="vehicle_number"
                                   class="form-control text-uppercase"
                                   placeholder="e.g. MH01AB1234"
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
                                <option value="" disabled selected>-- Select capacity --</option>
                                <?php foreach ([2, 4, 5, 6, 7, 8, 9, 10, 12] as $seats): ?>
                                <option value="<?= $seats ?>"><?= $seats ?> Persons</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Rent Per Day -->
                        <div class="mb-4">
                            <label for="rent_per_day" class="form-label">
                                <i class="fas fa-rupee-sign me-1"></i>Rent Per Day (₹)
                            </label>
                            <input type="number"
                                   id="rent_per_day"
                                   name="rent_per_day"
                                   class="form-control"
                                   placeholder="e.g. 1500"
                                   min="1"
                                   step="0.01"
                                   required>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fas fa-plus me-1"></i>Add Car to Fleet
                            </button>
                            <a href="<?= BASE_URL ?>/index.php"
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
