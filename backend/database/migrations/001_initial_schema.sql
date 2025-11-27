CREATE TABLE IF NOT EXISTS customers (
  customer_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  customer_name VARCHAR(200) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  email_verified TINYINT DEFAULT 0,
  phone VARCHAR(30),
  password VARCHAR(255) NOT NULL,
  lot_number VARCHAR(100),
  street VARCHAR(255),
  city VARCHAR(100),
  province VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS branches (
  branch_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  owner_name VARCHAR(200) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  franchise_number VARCHAR(100),
  email_verified TINYINT DEFAULT 0,
  phone VARCHAR(30),
  password VARCHAR(255) NOT NULL,
  lot_number VARCHAR(100),
  street VARCHAR(255),
  city VARCHAR(100),
  province VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  verified_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  branch_verified TINYINT DEFAULT 0,
  vat_rate DECIMAL(5,2) DEFAULT 0,
  delivery_rate DECIMAL(10,2) DEFAULT 0
);

CREATE TABLE IF NOT EXISTS staff (
  staff_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  staff_name VARCHAR(200) NOT NULL,
  branch_id BIGINT NOT NULL,
  email VARCHAR(150) NOT NULL,
  email_verified TINYINT DEFAULT 0,
  phone VARCHAR(30),
  password VARCHAR(255) NOT NULL,
  role VARCHAR(50) DEFAULT 'staff',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS inventories (
  inventory_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  branch_id BIGINT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS inventory_items (
  inventory_item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  inventory_id BIGINT NOT NULL,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  category VARCHAR(100),
  image_path VARCHAR(255),
  stock_quantity INT DEFAULT 0,
  stock_status VARCHAR(50) DEFAULT 'in_stock',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (inventory_id) REFERENCES inventories(inventory_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS inventory_logs (
  inventory_log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  inventory_id BIGINT NOT NULL,
  staff_id BIGINT,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (inventory_id) REFERENCES inventories(inventory_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
  order_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  branch_id BIGINT NOT NULL,
  customer_id BIGINT NOT NULL,
  item_quantity INT DEFAULT 0,
  store_address TEXT,
  destination_address TEXT,
  status VARCHAR(50) DEFAULT 'placed',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
  FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);

CREATE TABLE IF NOT EXISTS order_items (
  order_item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  inventory_item_id BIGINT,
  quantity INT DEFAULT 1,
  total DECIMAL(12,2) DEFAULT 0,
  FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_logs (
  order_log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  queued_at TIMESTAMP NULL,
  preparing_at TIMESTAMP NULL,
  delivering_at TIMESTAMP NULL,
  delivered_at TIMESTAMP NULL,
  received_at TIMESTAMP NULL,
  rating INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS payments (
  payment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT NOT NULL,
  subtotal DECIMAL(12,2) DEFAULT 0,
  vat DECIMAL(10,2) DEFAULT 0,
  delivery_fee DECIMAL(10,2) DEFAULT 0,
  total DECIMAL(12,2) DEFAULT 0,
  wallet_provider VARCHAR(100),
  wallet_account VARCHAR(255),
  status VARCHAR(50) DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS feedbacks (
  feedback_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  branch_id BIGINT NOT NULL,
  customer_id BIGINT NOT NULL,
  subject VARCHAR(255),
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
  FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);
