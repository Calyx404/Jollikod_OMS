USE jollikod_oms;

-- ============================
-- SEED: BRANCHES
-- ============================
INSERT INTO branches (name, email, phone, password, address)
VALUES
('Jollikod La Trinidad', 'latrinidad@jollikod.com', '09123456789', MD5('password123'), 'La Trinidad, Benguet'),
('Jollikod Baguio', 'baguio@jollikod.com', '09987654321', MD5('password123'), 'Baguio City, Benguet');


-- ============================
-- SEED: STAFF (FACTORY STYLE)
-- ============================
INSERT INTO staff (name, branch_id, email, phone, password, role)
VALUES
-- Branch 1 staff
('Alice Santos', 1, 'alice.latrinidad@jollikod.com', '09120000001', MD5('staff123'), 'staff'),
('Mark Dela Cruz', 1, 'mark.latrinidad@jollikod.com', '09120000002', MD5('staff123'), 'staff'),

-- Branch 2 staff
('Jenny Ramos', 2, 'jenny.baguio@jollikod.com', '09230000001', MD5('staff123'), 'staff'),
('Kyle Robles', 2, 'kyle.baguio@jollikod.com', '09230000002', MD5('staff123'), 'staff');


-- ============================
-- SEED: CUSTOMERS
-- ============================
INSERT INTO customers (name, email, phone, password, address)
VALUES
('Juan Dela Cruz', 'juan@example.com', '09111111111', MD5('customer123'), 'Baguio City'),
('Maria Santos', 'maria@example.com', '09222222222', MD5('customer123'), 'La Trinidad'),
('Pedro Fernandez', 'pedro@example.com', '09333333333', MD5('customer123'), 'Benguet Province');


-- ============================
-- SEED: MENU CATEGORY
-- ============================
INSERT INTO menu_categories (name)
VALUES 
('Meals'),
('Snacks'),
('Drinks'),
('Desserts');


-- ============================
-- SEED: MENUS (1 per branch)
-- ============================
INSERT INTO menus (branch_id)
VALUES 
(1),  -- Menu for La Trinidad
(2);  -- Menu for Baguio


-- ============================
-- SEED: MENU ITEMS
-- FACTORY RULE: Each branch gets 4 items
-- ============================

-- Branch 1 (menu_id = 1)
INSERT INTO menu_items (menu_id, menu_category_id, name, description, price, image_path)
VALUES
(1, 1, 'Chicken Meal', 'Fried chicken with rice', 120.00, 'images/chicken_meal.jpg'),
(1, 2, 'French Fries', 'Crispy fries', 45.00, 'images/fries.jpg'),
(1, 3, 'Iced Tea', 'Refreshing iced tea', 30.00, 'images/iced_tea.jpg'),
(1, 4, 'Chocolate Sundae', 'Classic sundae dessert', 40.00, 'images/sundae.jpg');

-- Branch 2 (menu_id = 2)
INSERT INTO menu_items (menu_id, menu_category_id, name, description, price, image_path)
VALUES
(2, 1, 'Burger Steak Meal', '2pcs burger steak + rice', 140.00, 'images/burger_steak.jpg'),
(2, 2, 'Hash Brown', 'Crispy hash brown', 35.00, 'images/hashbrown.jpg'),
(2, 3, 'Pineapple Juice', 'Fresh pineapple juice', 35.00, 'images/pineapple_juice.jpg'),
(2, 4, 'Peach Mango Pie', 'Signature pie dessert', 45.00, 'images/pmp.jpg');


-- ============================
-- SEED: MENU INVENTORY
-- Each item gets starting stock: 20
-- ============================
INSERT INTO menu_inventories (menu_id, menu_item_id, stock_quantity, stock_status)
SELECT menu_id, menu_item_id, 20, 'in_stock'
FROM menu_items;


-- ============================
-- OPTIONAL TEST: ORDERS (Sample)
-- ============================
INSERT INTO orders (branch_id, customer_id, item_quantity, store_address, destination_address, status)
VALUES
(1, 1, 2, 'La Trinidad, Benguet', 'Baguio City', 'placed'),
(2, 2, 1, 'Baguio City', 'La Trinidad', 'placed');


-- ============================
-- OPTIONAL TEST: ORDER ITEMS
-- ============================
INSERT INTO order_items (order_id, menu_item_id, quantity, total, unit_price)
VALUES
(1, 1, 1, 120.00, 120.00),
(1, 2, 1, 45.00, 45.00),
(2, 5, 1, 140.00, 140.00);


-- ============================
-- OPTIONAL TEST: PAYMENTS
-- ============================
INSERT INTO payments (order_id, subtotal, vat, delivery_fee, total, wallet_provider, wallet_account, status)
VALUES
(1, 165.00, 20.00, 30.00, 215.00, 'GCash', '09123456789', 'paid'),
(2, 140.00, 15.00, 30.00, 185.00, 'Maya', '09987654321', 'pending');


-- ============================
-- OPTIONAL FEEDBACKS
-- ============================
INSERT INTO feedbacks (branch_id, customer_id, subject, message)
VALUES
(1, 1, 'Great Food!', 'Chicken meal was tasty.'),
(2, 2, 'Fast Delivery', 'Order arrived hot and quick.');
