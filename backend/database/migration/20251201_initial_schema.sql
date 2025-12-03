-- DROP & CREATE DATABASE
DROP DATABASE IF EXISTS jollikod_oms;

CREATE DATABASE jollikod_oms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE jollikod_oms;

-- 1. CUSTOMERS
CREATE TABLE customers (
    customer_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30),
    password VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);
CREATE INDEX idx_customer_email ON customers(email);

-- 2. BRANCHES
CREATE TABLE branches (
    branch_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30),
    password VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

-- 3. STAFF
CREATE TABLE staffs (
    staff_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT NOT NULL,
    name VARCHAR(200) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30),
    role VARCHAR(50) DEFAULT 'staff',
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE
);
CREATE INDEX idx_staff_branch ON staff(branch_id);

-- 4. MENUS (1 per branch)
CREATE TABLE menus (
    menu_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id) ON DELETE CASCADE
);
CREATE INDEX idx_menu_branch ON menus(branch_id);

-- 5. MENU CATEGORIES
CREATE TABLE menu_categories (
    menu_category_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

-- 6. MENU ITEMS
CREATE TABLE menu_items (
    menu_item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    menu_id BIGINT NOT NULL,
    menu_category_id BIGINT NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    image_path VARCHAR(255),
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_category_id) REFERENCES menu_categories(menu_category_id) ON DELETE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(menu_id) ON DELETE CASCADE
);
CREATE INDEX idx_menu_items_menuid ON menu_items(menu_id);
CREATE INDEX idx_menu_items_category ON menu_items(menu_category_id);

-- 7. MENU INVENTORIES
CREATE TABLE menu_inventories (
    menu_inventory_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    menu_id BIGINT NOT NULL,
    menu_item_id BIGINT NOT NULL,
    stock_quantity INT DEFAULT 0,
    stock_status VARCHAR(50) DEFAULT 'in_stock',  -- in_stock / low_stock / out_of_stock
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(menu_item_id),
    FOREIGN KEY (menu_id) REFERENCES menus(menu_id) ON DELETE CASCADE
);
CREATE INDEX idx_inventory_item ON menu_inventories(menu_item_id);

-- 8. ORDERS
CREATE TABLE orders (
    order_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    item_quantity INT DEFAULT 0,
    store_address TEXT,
    destination_address TEXT,
    status VARCHAR(50) DEFAULT 'placed',  -- placed → queued → preparing → delivering → delivered → received
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);
CREATE INDEX idx_orders_branch_status_created ON orders(branch_id, status, created_at);

-- 9. ORDER ITEMS
CREATE TABLE order_items (
    order_item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT NOT NULL,
    menu_item_id BIGINT,
    quantity INT DEFAULT 1,
    total DECIMAL(12,2) DEFAULT 0,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);
CREATE INDEX idx_order_items_orderid ON order_items(order_id);

-- 10. ORDER LOGS
CREATE TABLE order_logs (
    order_log_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT NOT NULL,
    queued_at TIMESTAMP NULL,
    preparing_at TIMESTAMP NULL,
    delivering_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    received_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

-- 11. PAYMENTS
CREATE TABLE payments (
    payment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    order_id BIGINT NOT NULL,
    subtotal DECIMAL(12,2) DEFAULT 0,
    vat DECIMAL(10,2) DEFAULT 0,
    delivery_fee DECIMAL(10,2) DEFAULT 0,
    total DECIMAL(12,2) DEFAULT 0,
    wallet_provider VARCHAR(100),
    wallet_account VARCHAR(255),
    status VARCHAR(50) DEFAULT 'pending',  -- pending / paid
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);
CREATE INDEX idx_payments_orderid ON payments(order_id);

-- 12. FEEDBACKS
CREATE TABLE feedbacks (
    feedback_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    branch_id BIGINT NOT NULL,
    customer_id BIGINT NOT NULL,
    rating INT DEFAULT NULL,
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (branch_id) REFERENCES branches(branch_id),
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
);
