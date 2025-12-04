-- Sample branch
INSERT INTO branches (name, email, password, phone, address) VALUES 
('Jollikod Main Branch', 'branch@jollikod.com', '$2y$10$z5b3f1v7x9r1t3y5u7i9o0p2l4k6j8h0g2f4d6s8a0c2v4b6n8m1.', '09171234567', '123 Quezon City');

-- Sample customer
INSERT INTO customers (name, email, password, phone, address) VALUES 
('Juan Dela Cruz', 'juan@gmail.com', '$2y$10$z5b3f1v7x9r1t3y5u7i9o0p2l4k6j8h0g2f4d6s8a0c2v4b6n8m1.', '09181234567', '456 Manila');

-- Create menu for branch 1
INSERT INTO menus (branch_id) VALUES (1);

-- Sample categories (you can add more)
INSERT INTO menu_categories (name) VALUES ('Chicken'), ('Sides'), ('Drinks');

-- Sample items
INSERT INTO menu_items (menu_id, menu_category_id, name, description, price) VALUES
(1, 1, 'Chickenjoy', 'Crispy fried chicken', 99.00),
(1, 2, 'French Fries', 'Crispy fries', 45.00),
(1, 3, 'Iced Tea', '1 Liter', 60.00);

-- Inventory
INSERT INTO menu_inventories (menu_id, menu_item_id, stock_quantity, stock_status) VALUES
(1, 1, 50, 'in_stock'),
(1, 2, 100, 'in_stock'),
(1, 3, 80, 'in_stock');