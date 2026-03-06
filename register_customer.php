<?php
/**
 * Customer Registration Page
 *
 * Collects: full name, email, phone, password (with confirmation).
 * POSTs to /process/register_customer.php for validation and DB insert.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect already-authenticated users
if (isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$pageTitle = 'Customer Registration';

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card" style="max-width:520px">

        <!-- Header -->
        <div class="auth-header">
            <div class="icon-circle">
                <i class="fas fa-user-plus"></i>
            </div>
            <h3>Create Customer Account</h3>
            <p>Join DriveEasy to start renting cars today</p>
        </div>

        <!-- Body -->
        <div class="auth-body">
            <form action="<?= BASE_URL ?>/process/register_customer.php"
                  method="POST"
                  novalidate>

                <!-- Full Name -->
                <div class="mb-3">
                    <label for="full_name" class="form-label">
                        <i class="fas fa-user me-1"></i>Full Name
                    </label>
                    <input type="text"
                           id="full_name"
                           name="full_name"
                           class="form-control"
                           placeholder="John Doe"
                           maxlength="100"
                           required>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope me-1"></i>Email Address
                    </label>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-control"
                           placeholder="you@example.com"
                           maxlength="100"
                           required
                           autocomplete="email">
                </div>

                <!-- Phone -->
                <div class="mb-3">
                    <label for="phone" class="form-label">
                        <i class="fas fa-phone me-1"></i>Phone Number
                        <span class="text-muted fw-400">(optional)</span>
                    </label>
                    <input type="tel"
                           id="phone"
                           name="phone"
                           class="form-control"
                           placeholder="+91 98765 43210"
                           maxlength="15">
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
                        <i class="fas fa-user-check me-1"></i>Create Account
                    </button>
                </div>
            </form>

            <p class="text-center mt-3 mb-0 small text-muted">
                Already have an account?
                <a href="<?= BASE_URL ?>/login.php"
                   class="fw-600 text-primary-custom text-decoration-none">
                    Log in here
                </a>
            </p>
            <p class="text-center mt-2 mb-0 small text-muted">
                Registering as an agency?
                <a href="<?= BASE_URL ?>/register_agency.php"
                   class="fw-600 text-primary-custom text-decoration-none">
                    Agency registration
                </a>
            </p>
        </div><!-- /.auth-body -->
    </div><!-- /.auth-card -->
</div><!-- /.auth-wrapper -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
