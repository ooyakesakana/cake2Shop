SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expense_categories' AND COLUMN_NAME = 'default_accounting_type') = 0,
  'ALTER TABLE expense_categories ADD COLUMN default_accounting_type VARCHAR(50) DEFAULT NULL AFTER tax_account_name',
  'SELECT ''expense_categories.default_accounting_type already exists''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expense_categories' AND COLUMN_NAME = 'is_asset_candidate') = 0,
  'ALTER TABLE expense_categories ADD COLUMN is_asset_candidate TINYINT(1) NOT NULL DEFAULT 0 AFTER default_accounting_type',
  'SELECT ''expense_categories.is_asset_candidate already exists''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expense_categories' AND COLUMN_NAME = 'sort_order') = 0,
  'ALTER TABLE expense_categories ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER is_asset_candidate',
  'SELECT ''expense_categories.sort_order already exists''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expense_categories' AND COLUMN_NAME = 'is_active') = 0,
  'ALTER TABLE expense_categories ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER sort_order',
  'SELECT ''expense_categories.is_active already exists''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

INSERT INTO expense_categories (category_name, tax_account_name, default_accounting_type, is_asset_candidate, sort_order, is_active)
VALUES
  ('梱包・発送用品', '消耗品費', 'consumable', 0, 10, 1),
  ('発送費', '荷造運賃', 'shipping', 0, 20, 1),
  ('制作用消耗品', '消耗品費', 'consumable', 0, 30, 1),
  ('機材・工具', '工具器具備品', 'fixed_asset', 1, 40, 1),
  ('ソフト・サービス', '支払手数料', 'service', 0, 50, 1),
  ('広告・販売促進', '広告宣伝費', 'advertising', 0, 60, 1),
  ('手数料', '支払手数料', 'fee', 0, 70, 1),
  ('交通費', '旅費交通費', 'transport', 0, 80, 1),
  ('書籍・資料', '新聞図書費', 'books', 0, 90, 1),
  ('修理・メンテ', '修繕費', 'repair', 0, 100, 1),
  ('家賃・作業場', '地代家賃', 'rent', 0, 110, 1),
  ('電気・通信', '水道光熱費', 'utility', 0, 120, 1),
  ('その他', '雑費', 'misc', 0, 999, 1)
ON DUPLICATE KEY UPDATE
  tax_account_name = VALUES(tax_account_name),
  default_accounting_type = VALUES(default_accounting_type),
  is_asset_candidate = VALUES(is_asset_candidate),
  sort_order = VALUES(sort_order),
  is_active = VALUES(is_active);

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'arrival_date') = 0,
  'ALTER TABLE expenses ADD COLUMN arrival_date DATE DEFAULT NULL AFTER expense_date',
  'SELECT ''expenses.arrival_date already exists''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'use_start_date') = 0,
  'ALTER TABLE expenses ADD COLUMN use_start_date DATE DEFAULT NULL AFTER arrival_date',
  'SELECT ''expenses.use_start_date already exists''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'vendor_name') = 0,
  'ALTER TABLE expenses ADD COLUMN vendor_name VARCHAR(255) DEFAULT NULL AFTER use_start_date',
  'SELECT ''expenses.vendor_name already exists''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'accounting_type') = 0,
  'ALTER TABLE expenses ADD COLUMN accounting_type VARCHAR(50) DEFAULT NULL AFTER tax_account_name',
  'SELECT ''expenses.accounting_type already exists''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'status') = 0,
  'ALTER TABLE expenses ADD COLUMN status ENUM(''ordered'',''paid'',''received'',''active'') NOT NULL DEFAULT ''paid'' AFTER memo',
  'SELECT ''expenses.status already exists''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'is_depreciation') = 0,
  'ALTER TABLE expenses ADD COLUMN is_depreciation TINYINT(1) NOT NULL DEFAULT 0 AFTER status',
  'SELECT ''expenses.is_depreciation already exists''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'business_use_rate') = 0,
  'ALTER TABLE expenses ADD COLUMN business_use_rate DECIMAL(5,2) NOT NULL DEFAULT 100.00 AFTER is_depreciation',
  'SELECT ''expenses.business_use_rate already exists''');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS attachments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  target_type VARCHAR(30) NOT NULL,
  target_id INT UNSIGNED NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  original_name VARCHAR(255) DEFAULT NULL,
  file_path VARCHAR(500) NOT NULL,
  mime_type VARCHAR(100) DEFAULT NULL,
  file_size INT UNSIGNED DEFAULT NULL,
  memo TEXT DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_attachments_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
