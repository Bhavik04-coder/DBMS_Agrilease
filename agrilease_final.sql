-- agrilease_final.sql
CREATE DATABASE IF NOT EXISTS agrilease_v2;
USE agrilease_v2;

-- users table
DROP TABLE IF EXISTS users;
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(150) DEFAULT NULL,
  email VARCHAR(150) DEFAULT NULL,
  phone VARCHAR(25) DEFAULT NULL,
  lat DECIMAL(10,7) DEFAULT NULL,
  lng DECIMAL(10,7) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- products table
DROP TABLE IF EXISTS products;
CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  category VARCHAR(100) DEFAULT 'General',
  price DECIMAL(10,2) DEFAULT 0,
  image_path VARCHAR(255) DEFAULT NULL,
  location VARCHAR(255) DEFAULT NULL,
  lat DECIMAL(10,7) DEFAULT NULL,
  lng DECIMAL(10,7) DEFAULT NULL,
  listed_by INT DEFAULT NULL,
  status ENUM('available','booked') DEFAULT 'available',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listed_by) REFERENCES users(id) ON DELETE SET NULL
);

-- bookings table
DROP TABLE IF EXISTS bookings;
CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  renter_id INT,
  owner_id INT,
  status VARCHAR(50) DEFAULT 'pending',
  renter_lat DECIMAL(10,7) DEFAULT NULL,
  renter_lng DECIMAL(10,7) DEFAULT NULL,
  owner_lat DECIMAL(10,7) DEFAULT NULL,
  owner_lng DECIMAL(10,7) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (renter_id) REFERENCES users(id) ON DELETE SET NULL,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
);

-- notifications table
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  message TEXT,
  is_read TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- sample users
INSERT INTO users (username, password, full_name, email, phone, lat, lng) VALUES
('owner1', '$2y$10$abcdefghijklmnopqrstuv', 'Owner One', 'owner1@example.com', '9999999999', 19.0760, 72.8777),
('renter1', '$2y$10$abcdefghijklmnopqrstuv', 'Renter One', 'renter1@example.com', '8888888888', 18.5204, 73.8567);

-- sample product
INSERT INTO products (title, description, category, price, image_path, location, lat, lng, listed_by) VALUES
('Tractor Model X', 'Reliable tractor for medium farms', 'Tractor', 2500.00, 'assets/images/Harvester2.jpg', 'Pune', 18.5204, 73.8567, 1);

-- sample booking (none yet)
