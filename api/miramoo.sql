-- ------------------------------------------------------------
-- MiraMoo Database Schema
-- ------------------------------------------------------------

-- Create database
CREATE DATABASE IF NOT EXISTS `miramoo`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

-- Use database
USE `miramoo`;

-- ------------------------------------------------------------
-- Movies Table
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `movies` (
  `movie_id` INT AUTO_INCREMENT PRIMARY KEY,
  `movie_name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `genre` VARCHAR(100) NULL,
  `duration_mins` INT NULL,
  `poster_url` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Showtimes Table
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `showtimes` (
  `showtime_id` INT AUTO_INCREMENT PRIMARY KEY,
  `movie_id` INT NOT NULL,
  `hall` VARCHAR(50) NOT NULL,
  `show_date` DATE NOT NULL,
  `show_time` TIME NOT NULL,
  FOREIGN KEY (`movie_id`) REFERENCES `movies`(`movie_id`) ON DELETE CASCADE,
  INDEX (`movie_id`),
  INDEX (`show_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Seats Table
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `seats` (
  `seat_id` INT AUTO_INCREMENT PRIMARY KEY,
  `showtime_id` INT NOT NULL,
  `seat_no` VARCHAR(10) NOT NULL,
  `is_booked` BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (`showtime_id`) REFERENCES `showtimes`(`showtime_id`) ON DELETE CASCADE,
  UNIQUE INDEX (`showtime_id`, `seat_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Bookings Table
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` INT AUTO_INCREMENT PRIMARY KEY,
  `showtime_id` INT NOT NULL,
  `user_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NULL,
  `verification_status` ENUM('PENDING', 'VERIFIED') DEFAULT 'PENDING',
  `date_created` DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`showtime_id`) REFERENCES `showtimes`(`showtime_id`) ON DELETE CASCADE,
  INDEX (`showtime_id`),
  INDEX (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Booking_Seats Table (many-to-many relationship)
CREATE TABLE IF NOT EXISTS `booking_seats` (
  `booking_id` INT NOT NULL,
  `seat_id` INT NOT NULL,
  PRIMARY KEY (`booking_id`, `seat_id`),
  FOREIGN KEY (`booking_id`) REFERENCES `bookings`(`booking_id`) ON DELETE CASCADE,
  FOREIGN KEY (`seat_id`) REFERENCES `seats`(`seat_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Contact Table (for Contact Us form submissions)
CREATE TABLE IF NOT EXISTS `contact` (
  `contact_id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `submitted_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Accounts Table
CREATE TABLE IF NOT EXISTS `accounts` (
  `account_id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NULL,
  `birthday` DATE NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `account_type` ENUM('ADMIN', 'MEMBER') DEFAULT 'MEMBER'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;