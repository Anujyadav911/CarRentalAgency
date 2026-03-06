<?php
/**
 * Login Page
 *
 * Single page with two Bootstrap tabs:
 *   - Customer Login
 *   - Agency Login
 *
 * Both tabs POST to /process/login.php with a hidden `user_type` field.
 * Already-authenticated users are redirected to the home page.
 */

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect(BASE_URL . '/index.php');
}

$pageTitle = 'Login';

// Which tab to show active (e.g. after a failed login attempt)
$activeTab = ($_GET['tab'] ?? 'customer') === 'agency' ? 'agency' : 'customer';

require_once __DIR__ . '/includes/header.php';
?>

<div class="auth-wrapper">
    <div class="auth-card">

        <!-- Header -->
        <div class="auth-header">
            <div class="icon-circle">
                <i class="fas fa-sign-in-alt"></i>
            </div>
            <h3>Welcome Back</h3>
            <p>Log in to your DriveEasy account</p>
        </div>

        <!-- Body -->
        <div class="auth-body">

            <!-- Tabs -->
            <ul class="nav nav-tabs" id="loginTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'customer' ? 'active' : '' ?>"
                            id="customer-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#customerPane"
                            type="button"
                            role="tab">
                        <i class="fas fa-user me-1"></i>Customer
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $activeTab === 'agency' ? 'active' : '' ?>"
                            id="agency-tab"
                            data-bs-toggle="tab"
                            data-bs-target="#agencyPane"
                            type="button"
                            role="tab">
                        <i class="fas fa-building me-1"></i>Agency
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="loginTabContent">

                <!-- ── Customer Login ──────────────────────────────────── -->
                <div class="tab-pane fade <?= $activeTab === 'customer' ? 'show active' : '' ?>"
                     id="customerPane"
                     role="tabpanel">

                    <form action="<?= BASE_URL ?>/process/login.php"
                          method="POST"
                          novalidate>
                        <input type="hidden" name="user_type" value="customer">

                        <div class="mb-3">
                            <label for="cust-email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email Address
                            </label>
                            <input type="email"
                                   id="cust-email"
                                   name="email"
                                   class="form-control"
                                   placeholder="you@example.com"
                                   required
                                   autocomplete="email">
                        </div>

                        <div class="mb-4">
                            <label for="cust-password" class="form-label">
                                <i class="fas fa-lock me-1"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       id="cust-password"
                                       name="password"
                                       class="form-control"
                                       placeholder="Enter your password"
                                       required
                                       autocomplete="current-password">
                                <button type="button"
                                        class="toggle-password"
                                        data-target="cust-password"
                                        aria-label="Toggle visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fas fa-sign-in-alt me-1"></i>Login as Customer
                            </button>
                        </div>
                    </form>

                    <p class="text-center mt-3 mb-0 small text-muted">
                        Don't have an account?
                        <a href="<?= BASE_URL ?>/register_customer.php"
                           class="fw-600 text-primary-custom text-decoration-none">
                            Register here
                        </a>
                    </p>
                </div><!-- /#customerPane -->

                <!-- ── Agency Login ─────────────────────────────────────── -->
                <div class="tab-pane fade <?= $activeTab === 'agency' ? 'show active' : '' ?>"
                     id="agencyPane"
                     role="tabpanel">

                    <form action="<?= BASE_URL ?>/process/login.php"
                          method="POST"
                          novalidate>
                        <input type="hidden" name="user_type" value="agency">

                        <div class="mb-3">
                            <label for="agn-email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email Address
                            </label>
                            <input type="email"
                                   id="agn-email"
                                   name="email"
                                   class="form-control"
                                   placeholder="agency@example.com"
                                   required
                                   autocomplete="email">
                        </div>

                        <div class="mb-4">
                            <label for="agn-password" class="form-label">
                                <i class="fas fa-lock me-1"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       id="agn-password"
                                       name="password"
                                       class="form-control"
                                       placeholder="Enter your password"
                                       required
                                       autocomplete="current-password">
                                <button type="button"
                                        class="toggle-password"
                                        data-target="agn-password"
                                        aria-label="Toggle visibility">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary-custom">
                                <i class="fas fa-sign-in-alt me-1"></i>Login as Agency
                            </button>
                        </div>
                    </form>

                    <p class="text-center mt-3 mb-0 small text-muted">
                        Not registered yet?
                        <a href="<?= BASE_URL ?>/register_agency.php"
                           class="fw-600 text-primary-custom text-decoration-none">
                            Register your agency
                        </a>
                    </p>
                </div><!-- /#agencyPane -->

            </div><!-- /.tab-content -->
        </div><!-- /.auth-body -->
    </div><!-- /.auth-card -->
</div><!-- /.auth-wrapper -->

<?php require_once __DIR__ . '/includes/footer.php'; ?>
