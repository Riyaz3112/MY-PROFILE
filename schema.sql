CREATE DATABASE IF NOT EXISTS lookstylo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lookstylo;

CREATE TABLE IF NOT EXISTS admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  price DECIMAL(10,2) NOT NULL,
  image VARCHAR(255) DEFAULT NULL,
  images TEXT DEFAULT NULL,
  stock_quantity INT DEFAULT 0,
  sizes VARCHAR(255) DEFAULT 'S,M,L,XL',
  description TEXT,
  category VARCHAR(100) DEFAULT 'general',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_products_category (category)
);

CREATE TABLE IF NOT EXISTS product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  filename VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS cart (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255) NOT NULL,
  product_id INT NOT NULL,
  size VARCHAR(50) DEFAULT 'M',
  color VARCHAR(50) DEFAULT 'Black',
  quantity INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  INDEX idx_cart_session (session_id)
);

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(255) NOT NULL,
  mobile VARCHAR(20) NOT NULL,
  email VARCHAR(255) DEFAULT NULL,
  password_hash VARCHAR(255) DEFAULT NULL,
  address TEXT NOT NULL,
  city VARCHAR(100) NOT NULL,
  state VARCHAR(100) NOT NULL,
  pincode VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_mobile (mobile)
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id VARCHAR(50) NOT NULL UNIQUE,
  user_id INT DEFAULT NULL,
  customer_name VARCHAR(255) NOT NULL,
  mobile VARCHAR(20) NOT NULL,
  email VARCHAR(255) DEFAULT NULL,
  address TEXT NOT NULL,
  city VARCHAR(100) NOT NULL,
  state VARCHAR(100) NOT NULL,
  pincode VARCHAR(20) NOT NULL,
  order_notes TEXT DEFAULT NULL,
  total_amount DECIMAL(10,2) NOT NULL,
  utr_number VARCHAR(100) NOT NULL,
  payment_screenshot VARCHAR(255) DEFAULT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'Payment Verification Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_orders_status (status),
  INDEX idx_orders_mobile (mobile)
);

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  size VARCHAR(50) NOT NULL,
  color VARCHAR(50) NOT NULL,
  quantity INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
  INDEX idx_order_items_order (order_id)
);

CREATE TABLE IF NOT EXISTS payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  utr_number VARCHAR(100) NOT NULL,
  screenshot VARCHAR(255) DEFAULT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'Pending Verification',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  INDEX idx_payments_order (order_id)
);

CREATE TABLE IF NOT EXISTS order_tracking (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  status VARCHAR(50) NOT NULL,
  note TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  INDEX idx_tracking_order (order_id)
);

CREATE TABLE IF NOT EXISTS billing_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  invoice_id VARCHAR(255) NOT NULL UNIQUE,
  bill_date DATE DEFAULT NULL,
  bill_type VARCHAR(50) DEFAULT 'Non-GST',
  customer_name VARCHAR(255) DEFAULT NULL,
  customer_phone VARCHAR(50) DEFAULT NULL,
  customer_address TEXT DEFAULT NULL,
  delivery_mode VARCHAR(100) DEFAULT NULL,
  courier VARCHAR(100) DEFAULT NULL,
  tracking VARCHAR(255) DEFAULT NULL,
  discount DECIMAL(10,2) DEFAULT 0,
  promo_code VARCHAR(100) DEFAULT NULL,
  items TEXT DEFAULT NULL,
  total DECIMAL(10,2) DEFAULT 0,
  cash DECIMAL(10,2) DEFAULT 0,
  gpay DECIMAL(10,2) DEFAULT 0,
  advance DECIMAL(10,2) DEFAULT 0,
  balance DECIMAL(10,2) DEFAULT 0,
  description TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_billing_phone (customer_phone),
  INDEX idx_billing_date (bill_date)
);

INSERT INTO admin (username, password) VALUES ('Shariff', 'Shariff@123') ON DUPLICATE KEY UPDATE password=VALUES(password);
UPDATE admin SET username = 'Shariff', password = 'Shariff@123' WHERE username = 'admin';

INSERT INTO products (name, slug, price, image, description, category) VALUES
('Classic Oversized Tee', 'classic-oversized-tee', 999, 'images/tee-1.jpg', 'Premium oversized cotton tee for everyday fashion.', 'tees'),
('Streetwear Jersey', 'streetwear-jersey', 1499, 'images/jersey-1.jpg', 'Comfortable streetwear jersey with premium finish.', 'jerseys'),
('Track Pants', 'track-pants', 1299, 'images/track-1.jpg', 'Soft comfort track pants for daily wear.', 'pants')
ON DUPLICATE KEY UPDATE name=VALUES(name);
