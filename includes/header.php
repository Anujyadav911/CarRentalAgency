<?php
/**
 * Shared HTML header and top-navigation bar.
 *
 * Expects the including script to have:
 *   - Required functions.php (and therefore an active session)
 *   - Optionally defined $pageTitle (string)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = $pageTitle ?? 'Car Rental';
$flash     = getFlash();
$base      = BASE_URL;
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageTitle) ?> | DriveEasy Rentals</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <!-- Custom stylesheet -->
    <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css">
</head>
<body>

<!-- ── Navigation Bar ──────────────────────────────────────────────────────── -->
<nav class="navbar navbar-expand-lg navbar-dark site-navbar shadow-sm">
    <div class="container">

        <!-- Brand -->
        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold"
           href="<?= $base ?>/index.php">
            <i class="fas fa-car-side fa-lg text-warning"></i>
            DriveEasy<span class="text-warning">Rentals</span>
        </a>

        <!-- Mobile toggle -->
        <button class="navbar-toggler border-0" type="button"
                data-bs-toggle="collapse" data-bs-target="#mainNav"
                aria-controls="mainNav" aria-expanded="false"
                aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">

            <!-- Left-side links -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>/index.php">
                        <i class="fas fa-car me-1"></i>Available Cars
                    </a>
                </li>

                <?php if (isAgency()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>/add_car.php">
                        <i class="fas fa-plus-circle me-1"></i>Add Car
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>/booked_cars.php">
                        <i class="fas fa-clipboard-list me-1"></i>Booked Cars
                    </a>
                </li>
                <?php endif; ?>

                <?php if (isCustomer()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>/my_bookings.php">
                        <i class="fas fa-history me-1"></i>My Bookings
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Right-side links -->
            <ul class="navbar-nav ms-auto">
                <?php if (isLoggedIn()): ?>

                <!-- User dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                       href="#" role="button" data-bs-toggle="dropdown"
                       aria-expanded="false">
                        <i class="fas fa-user-circle fa-lg"></i>
                        <span><?= sanitize($_SESSION['user_name']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li class="px-3 py-2">
                            <small class="text-muted">
                                <i class="fas fa-id-badge me-1"></i>
                                <?= ucfirst(sanitize($_SESSION['user_type'])) ?> Account
                            </small>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <a class="dropdown-item text-danger"
                               href="<?= $base ?>/process/logout.php">
                                <i class="fas fa-sign-out-alt me-1"></i>Logout
                            </a>
                        </li>
                    </ul>
                </li>

                <?php else: ?>

                <!-- Guest links -->
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>/login.php">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-plus me-1"></i>Register
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li>
                            <a class="dropdown-item"
                               href="<?= $base ?>/register_customer.php">
                                <i class="fas fa-user me-1 text-primary"></i>As Customer
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item"
                               href="<?= $base ?>/register_agency.php">
                                <i class="fas fa-building me-1 text-primary"></i>As Agency
                            </a>
                        </li>
                    </ul>
                </li>

                <?php endif; ?>
            </ul>
        </div><!-- /.collapse -->
    </div><!-- /.container -->
</nav>

<!-- ── Flash Message ───────────────────────────────────────────────────────── -->
<?php if ($flash): ?>
<div class="container-fluid px-4 pt-3">
    <div class="alert <?= alertClass($flash['type']) ?> alert-dismissible fade show shadow-sm"
         role="alert">
        <i class="fas <?= flashIcon($flash['type']) ?> me-2"></i>
        <?= sanitize($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"
                aria-label="Close"></button>
    </div>
</div>
<?php endif; ?>

<!-- ── Page Content ────────────────────────────────────────────────────────── -->
<main class="py-4">
