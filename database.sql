-- ============================================================
-- Car Rental Agency — Database Schema
-- ============================================================
-- NOTE: Run this SQL inside your already-created database.
-- On shared hosting (InfinityFree, Hostinger etc.) create the
-- database via cPanel first, then import this file via phpMyAdmin.
-- ============================================================

-- ------------------------------------------------------------
-- Table: customers
-- Stores registered customer accounts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
    `id`         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `full_name`  VARCHAR(100)  NOT NULL,
    `email`      VARCHAR(100)  NOT NULL,
    `password`   VARCHAR(255)  NOT NULL,
    `phone`      VARCHAR(15)            DEFAULT NULL,
    `created_at` TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_customer_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: agencies
-- Stores registered car rental agency accounts
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `agencies` (
    `id`          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `agency_name` VARCHAR(150)  NOT NULL,
    `email`       VARCHAR(100)  NOT NULL,
    `password`    VARCHAR(255)  NOT NULL,
    `phone`       VARCHAR(15)            DEFAULT NULL,
    `address`     TEXT                   DEFAULT NULL,
    `created_at`  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_agency_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: cars
-- Stores cars listed by agencies for rental.
-- is_available is managed by the agency (mark 0 when rented out,
-- 1 when free again).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `cars` (
    `id`               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `agency_id`        INT UNSIGNED     NOT NULL,
    `vehicle_model`    VARCHAR(100)     NOT NULL,
    `vehicle_number`   VARCHAR(50)      NOT NULL,
    `seating_capacity` TINYINT UNSIGNED NOT NULL,
    `rent_per_day`     DECIMAL(10,2)    NOT NULL,
    `is_available`     TINYINT(1)       NOT NULL DEFAULT 1,
    `created_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`       TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                 ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_vehicle_number` (`vehicle_number`),
    KEY `idx_agency_id`    (`agency_id`),
    KEY `idx_is_available` (`is_available`),
    CONSTRAINT `fk_cars_agency`
        FOREIGN KEY (`agency_id`) REFERENCES `agencies` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: bookings
-- Records every rental booking made by a customer.
-- When a booking is placed the application marks the car as
-- unavailable; the agency can re-enable it via the Edit Car page.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
    `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `car_id`       INT UNSIGNED     NOT NULL,
    `customer_id`  INT UNSIGNED     NOT NULL,
    `start_date`   DATE             NOT NULL,
    `num_days`     TINYINT UNSIGNED NOT NULL,
    `total_amount` DECIMAL(10,2)    NOT NULL,
    `status`       ENUM('active','completed','cancelled')
                                    NOT NULL DEFAULT 'active',
    `booked_at`    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_car_id`      (`car_id`),
    KEY `idx_customer_id` (`customer_id`),
    CONSTRAINT `fk_bookings_car`
        FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_bookings_customer`
        FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
