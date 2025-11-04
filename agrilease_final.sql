-- =====================================================
-- AGRILEASE FINAL DATABASE SETUP (VERSION 2)
-- Safe to re-run anytime — no FK errors.
-- =====================================================
-- Drop and recreate the entire database
DROP DATABASE IF EXISTS agrilease_v2;
CREATE DATABASE agrilease_v2;
USE agrilease_v2;
-- Disable foreign key checks temporarily (for safety)
SET FOREIGN_KEY_CHECKS = 0;
-- Drop existing tables if any (correct order)
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;
-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;
-- =====================================================
-- USERS TABLE
-- =====================================================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) DEFAULT NULL,
  email VARCHAR(150) DEFAULT NULL,
  phone VARCHAR(25) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  lat DECIMAL(10, 7) DEFAULT NULL,
  lng DECIMAL(10, 7) DEFAULT NULL,
  profile_image VARCHAR(255) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- =====================================================
-- PRODUCTS TABLE
-- =====================================================
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  category VARCHAR(100) DEFAULT 'General',
  price DECIMAL(10, 2) DEFAULT 0,
  image_path VARCHAR(255) DEFAULT NULL,
  location VARCHAR(255) DEFAULT NULL,
  lat DECIMAL(10, 7) DEFAULT NULL,
  lng DECIMAL(10, 7) DEFAULT NULL,
  listed_by INT DEFAULT NULL,
  status ENUM('available', 'booked', 'maintenance') DEFAULT 'available',
  availability ENUM('Available', 'Rented', 'Maintenance') DEFAULT 'Available',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (listed_by) REFERENCES users(id) ON DELETE
  SET NULL
);
-- =====================================================
-- BOOKINGS TABLE
-- =====================================================
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  renter_id INT,
  owner_id INT,
  status ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
  start_date DATE DEFAULT NULL,
  end_date DATE DEFAULT NULL,
  total_price DECIMAL(10, 2) DEFAULT 0,
  renter_lat DECIMAL(10, 7) DEFAULT NULL,
  renter_lng DECIMAL(10, 7) DEFAULT NULL,
  owner_lat DECIMAL(10, 7) DEFAULT NULL,
  owner_lng DECIMAL(10, 7) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (renter_id) REFERENCES users(id) ON DELETE
  SET NULL,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE
  SET NULL
);
-- =====================================================
-- NOTIFICATIONS TABLE
-- =====================================================
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  title VARCHAR(255) DEFAULT NULL,
  message TEXT,
  type ENUM('booking', 'payment', 'system', 'general') DEFAULT 'general',
  is_read TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
-- =====================================================
-- REVIEWS TABLE
-- =====================================================
CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT,
  product_id INT DEFAULT NULL,
  reviewer_id INT,
  reviewed_id INT,
  rating INT DEFAULT 5 CHECK (
    rating >= 1
    AND rating <= 5
  ),
  comment TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewed_id) REFERENCES users(id) ON DELETE CASCADE
);
-- =====================================================
-- DATABASE READY FOR PRODUCTION USE
-- No sample data - clean installation
-- =====================================================
-- =====================================================
-- END OF FILE
-- =====================================================