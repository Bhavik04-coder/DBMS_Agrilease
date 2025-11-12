-- =====================================================
-- AGRILEASE COMPLETE DATABASE WITH RECEIPTS
-- Single file with everything you need
-- =====================================================

DROP DATABASE IF EXISTS agrilease_v2;
CREATE DATABASE agrilease_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agrilease_v2;

-- Disable foreign key checks temporarily
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables if any
DROP TABLE IF EXISTS receipts;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- USERS TABLE
-- Stores user account information
-- =====================================================
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) DEFAULT NULL,
  email VARCHAR(150) DEFAULT NULL UNIQUE,
  phone VARCHAR(25) DEFAULT NULL,
  address TEXT DEFAULT NULL,
  lat DECIMAL(10, 7) DEFAULT NULL,
  lng DECIMAL(10, 7) DEFAULT NULL,
  profile_image VARCHAR(255) DEFAULT NULL,
  user_type ENUM('renter', 'owner', 'both') DEFAULT 'both',
  is_active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_username (username),
  INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- PRODUCTS TABLE
-- Stores agricultural equipment listings
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
  views INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (listed_by) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_status (status),
  INDEX idx_category (category),
  INDEX idx_listed_by (listed_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- BOOKINGS TABLE
-- Stores rental booking information
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
  payment_status ENUM('pending', 'paid', 'refunded') DEFAULT 'pending',
  payment_method VARCHAR(50) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (renter_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_status (status),
  INDEX idx_renter (renter_id),
  INDEX idx_owner (owner_id),
  INDEX idx_dates (start_date, end_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- RECEIPTS TABLE
-- Stores receipt/invoice information for bookings
-- =====================================================
CREATE TABLE receipts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT NOT NULL,
  receipt_number VARCHAR(50) NOT NULL UNIQUE,
  issue_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  subtotal DECIMAL(10, 2) DEFAULT 0,
  tax_amount DECIMAL(10, 2) DEFAULT 0,
  discount_amount DECIMAL(10, 2) DEFAULT 0,
  total_amount DECIMAL(10, 2) DEFAULT 0,
  payment_status ENUM('pending', 'paid', 'refunded', 'partial') DEFAULT 'pending',
  payment_date DATETIME DEFAULT NULL,
  payment_method VARCHAR(50) DEFAULT NULL,
  transaction_id VARCHAR(100) DEFAULT NULL,
  notes TEXT DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  INDEX idx_receipt_number (receipt_number),
  INDEX idx_booking (booking_id),
  INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- NOTIFICATIONS TABLE
-- Stores user notifications
-- =====================================================
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  title VARCHAR(255) DEFAULT NULL,
  message TEXT,
  type ENUM('booking', 'payment', 'system', 'general') DEFAULT 'general',
  is_read TINYINT(1) DEFAULT 0,
  related_id INT DEFAULT NULL,
  related_type VARCHAR(50) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_user_read (user_id, is_read),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- REVIEWS TABLE
-- Stores product and user reviews
-- =====================================================
CREATE TABLE reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  booking_id INT,
  product_id INT DEFAULT NULL,
  reviewer_id INT,
  reviewed_id INT,
  rating INT DEFAULT 5 CHECK (rating >= 1 AND rating <= 5),
  comment TEXT,
  review_type ENUM('product', 'renter', 'owner') DEFAULT 'product',
  is_verified TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewed_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_product (product_id),
  INDEX idx_reviewer (reviewer_id),
  INDEX idx_reviewed (reviewed_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TRIGGERS
-- Automatically generate receipt when booking is confirmed
-- =====================================================
DELIMITER $$

CREATE TRIGGER after_booking_confirmed
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
  -- Only create receipt if status changed to confirmed and no receipt exists
  IF NEW.status = 'confirmed' AND OLD.status != 'confirmed' THEN
    IF NOT EXISTS (SELECT 1 FROM receipts WHERE booking_id = NEW.id) THEN
      INSERT INTO receipts (
        booking_id,
        receipt_number,
        subtotal,
        tax_amount,
        discount_amount,
        total_amount,
        payment_status
      ) VALUES (
        NEW.id,
        CONCAT('RCP-', LPAD(NEW.id, 8, '0')),
        NEW.total_price,
        NEW.total_price * 0.18, -- 18% GST
        0,
        NEW.total_price * 1.18, -- Total with tax
        NEW.payment_status
      );
    END IF;
  END IF;
END$$

DELIMITER ;

-- =====================================================
-- VIEWS
-- Useful views for common queries
-- =====================================================

-- View for complete booking details with receipt
CREATE VIEW booking_details_with_receipt AS
SELECT 
  b.id AS booking_id,
  b.status AS booking_status,
  b.start_date,
  b.end_date,
  b.total_price,
  b.notes,
  b.created_at AS booking_date,
  p.id AS product_id,
  p.title AS product_title,
  p.image_path,
  p.price AS daily_price,
  renter.id AS renter_id,
  renter.full_name AS renter_name,
  renter.email AS renter_email,
  renter.phone AS renter_phone,
  owner.id AS owner_id,
  owner.full_name AS owner_name,
  owner.email AS owner_email,
  owner.phone AS owner_phone,
  r.id AS receipt_id,
  r.receipt_number,
  r.total_amount AS receipt_total,
  r.payment_status,
  r.payment_date,
  r.payment_method
FROM bookings b
LEFT JOIN products p ON b.product_id = p.id
LEFT JOIN users renter ON b.renter_id = renter.id
LEFT JOIN users owner ON b.owner_id = owner.id
LEFT JOIN receipts r ON b.id = r.booking_id;

-- View for product statistics
CREATE VIEW product_statistics AS
SELECT 
  p.id,
  p.title,
  p.category,
  p.price,
  p.status,
  COUNT(DISTINCT b.id) AS total_bookings,
  COUNT(DISTINCT CASE WHEN b.status = 'completed' THEN b.id END) AS completed_bookings,
  COALESCE(AVG(rev.rating), 0) AS average_rating,
  COUNT(DISTINCT rev.id) AS total_reviews,
  p.views
FROM products p
LEFT JOIN bookings b ON p.id = b.product_id
LEFT JOIN reviews rev ON p.id = rev.product_id
GROUP BY p.id;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

-- Procedure to calculate booking duration
DELIMITER $$

CREATE PROCEDURE calculate_booking_duration(
  IN p_booking_id INT,
  OUT p_duration INT
)
BEGIN
  SELECT DATEDIFF(end_date, start_date) + 1 INTO p_duration
  FROM bookings
  WHERE id = p_booking_id;
END$$

DELIMITER ;

-- Procedure to get user booking history
DELIMITER $$

CREATE PROCEDURE get_user_booking_history(
  IN p_user_id INT,
  IN p_user_type VARCHAR(10)
)
BEGIN
  IF p_user_type = 'renter' THEN
    SELECT b.*, p.title, p.image_path, u.full_name AS owner_name
    FROM bookings b
    LEFT JOIN products p ON b.product_id = p.id
    LEFT JOIN users u ON b.owner_id = u.id
    WHERE b.renter_id = p_user_id
    ORDER BY b.created_at DESC;
  ELSEIF p_user_type = 'owner' THEN
    SELECT b.*, p.title, p.image_path, u.full_name AS renter_name
    FROM bookings b
    LEFT JOIN products p ON b.product_id = p.id
    LEFT JOIN users u ON b.renter_id = u.id
    WHERE b.owner_id = p_user_id
    ORDER BY b.created_at DESC;
  END IF;
END$$

DELIMITER ;

-- =====================================================
-- SAMPLE DATA (Optional - Remove if not needed)
-- =====================================================

-- Insert sample users
INSERT INTO users (username, password, full_name, email, phone, address, lat, lng) VALUES
('john_farmer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Farmer', 'john@example.com', '9876543210', 'Village Road, Punjab', 30.7333, 76.7794),
('mary_agri', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Mary Agriculture', 'mary@example.com', '9876543211', 'Farm Street, Haryana', 29.0588, 76.0856);

-- Insert sample products
INSERT INTO products (title, description, category, price, location, lat, lng, listed_by, status) VALUES
('Tractor - John Deere 5050D', 'Powerful 50HP tractor suitable for all farming needs', 'Tractors', 1500.00, 'Punjab', 30.7333, 76.7794, 1, 'available'),
('Harvester - New Holland TC5.90', 'Efficient combine harvester for wheat and rice', 'Harvesters', 3000.00, 'Haryana', 29.0588, 76.0856, 2, 'available');

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================

-- Additional indexes for better query performance
CREATE INDEX idx_booking_status_dates ON bookings(status, start_date, end_date);
CREATE INDEX idx_product_status_category ON products(status, category);
CREATE INDEX idx_receipt_payment ON receipts(payment_status, payment_date);

-- =====================================================
-- DATABASE READY
-- All tables, triggers, views, and procedures created
-- =====================================================

SELECT 'Database setup completed successfully!' AS status;
