USE jollikod_oms;

ALTER TABLE menu_items
  ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER image_path;

CREATE TABLE IF NOT EXISTS menu_item_schedules (
  schedule_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  menu_item_id BIGINT NOT NULL,
  start_date DATE NULL,         -- optional start date for the schedule
  end_date DATE NULL,           -- optional end date for the schedule
  days_of_week VARCHAR(20) NULL, -- comma-separated days 0=Sunday..6=Saturday, NULL = every day
  start_time TIME NULL,         -- optional daily start time, NULL = all day
  end_time TIME NULL,           -- optional daily end time, NULL = all day
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (menu_item_id) REFERENCES menu_items(menu_item_id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_schedule_menu_item ON menu_item_schedules(menu_item_id);
