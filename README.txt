# AgriLease (Cleaned & Modernized)

## Setup
1. Create a MySQL database named `agrilease` and a `users` and `products` table (example schema below).
2. Update credentials in `includes/config.php` if needed.
3. Place the folder on your PHP server root and visit `/index.php`.

## Example tables
```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100),
  email VARCHAR(100),
  phone VARCHAR(20),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  category VARCHAR(50),
  image_path VARCHAR(255),
  listed_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listed_by) REFERENCES users(id) ON DELETE CASCADE
);
```
