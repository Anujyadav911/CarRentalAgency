<?php
/**
 * Agency Registration Page
 *
 * Collects: agency name, email, phone, address, password (with confirmation).
 * POSTs to /process/register_agency.php for validation and DB insert.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect already-authenticated users
if (isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$pageTitle = 'Agency Registration';

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width:540px">

        <!-- Header -->
        <div class="auth-header">
            <div class="icon-circle">
                <i class="fas fa-building"></i>
            </div>
            <h3>Register Your Agency</h3>
            <p>List your vehicles and start earning with DriveEasy</p>
        </div>

        <!-- Body -->
        <div class="auth-body">
            <form action="<?= BASE_URL ?>/process/register_agency.php"
                  method="POST"
                  novalidate>

                <!-- Agency Name -->
                <div class="mb-3">
                    <label for="agency_name" class="form-label">
                        <i class="fas fa-building me-1"></i>Agency Name
                    </label>
                    <input type="text"
                           id="agency_name"
                           name="agency_name"
                           class="form-control"
                           placeholder="e.g. Swift Wheels Rental Co."
                           maxlength="150"
                           required>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-1"></i>Business Email
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control"
                           placeholder="contact@youragency.com"
                           maxlength="100"
                           required
                           autocomplete="email">
                </div>

                <!-- Phone -->
                <div class="mb-3">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone me-1"></i>Contact Number
                        <span class="text-muted fw-400">(optional)</span>
                    </label>
                    <input type="tel"
                           id="phone"
                           name="phone"
                           class="form-control"
                           placeholder="+91 98765 43210"
                           maxlength="15">
                </div>

                <!-- Address -->
                <div class="mb-3">
                    <label for="address" class="form-label">
                        <i class="fas fa-map-marker-alt me-1"></i>Business Address
                        <span class="text-muted fw-400">(optional)</span>
                    </label>
                    <textarea id="address"
                              name="address"
                              class="form-control"
                              rows="2"
                              placeholder="123 Main Street, Mumbai, Maharashtra"></textarea>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-1"></i>Password
                    </label>
                    <div class="input-group">
                        <input type="password"
                               id="password"
                               name="password"
                               class="form-control"
                               placeholder="Minimum 8 characters"
                               minlength="8"
                               required
                               autocomplete="new-password">
                        <button type="button"
                                class="toggle-password"
                                data-target="password"
                                aria-label="Toggle visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">
                        <i class="fas fa-lock me-1"></i>Confirm Password
                    </label>
                    <div class="input-group">
                        <input type="password"
                               id="confirm_password"
                               name="confirm_password"
                               class="form-control"
                               placeholder="Re-enter your password"
                               required
                               autocomplete="new-password">
                        <button type="button"
                                class="toggle-password"
                                data-target="confirm_password"
                                aria-label="Toggle visibility">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary-custom">
                        <i class="fas fa-check-circle me-1"></i>Register Agency
                    </button>
                </div>
            </form>

            <p class="text-center mt-3 mb-0 small text-muted">
                Already registered?
                <a href="<?= BASE_URL ?>/login.php?tab=agency"
                   class="fw-600 text-primary-custom text-decoration-none">
                    Agency login
                </a>
            </p>
            <p class="text-center mt-2 mb-0 small text-muted">
                Registering as a customer?
                <a href="<?= BASE_URL ?>/register_customer.php"
                   class="fw-600 text-primary-custom text-decoration-none">
                    Customer registration
                </a>
            </p>
        </div><!-- /.auth-body -->
    </div><!-- /.auth-card -->
</div><!-- /.auth-wrapper -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
