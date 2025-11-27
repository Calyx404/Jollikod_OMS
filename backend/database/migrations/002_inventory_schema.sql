CREATE TABLE IF NOT EXISTS inventory_categories (
    inventory_category_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory_stocks (
    inventory_stocks_id BIGINT AUTO_INCREMENT PRIMARY KEY,
    inventory_item_id BIGINT NOT NULL,
    stock_quantity INT DEFAULT 0,
    stock_status VARCHAR(50) DEFAULT 'in_stock',
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(inventory_item_id)
);

ALTER TABLE inventory_items
  DROP COLUMN category,
  DROP COLUMN stock_quantity,
  DROP COLUMN stock_status,
  DROP COLUMN updated_at;

ALTER TABLE inventory_items
  ADD COLUMN inventory_category_id BIGINT NOT NULL AFTER inventory_id;

ALTER TABLE inventory_items
  ADD CONSTRAINT fk_inventory_category
  FOREIGN KEY (inventory_category_id) 
  REFERENCES inventory_categories(inventory_category_id);
