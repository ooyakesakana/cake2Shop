-- =========================================================
-- Cake2Shop 個人運用向け 完全版SQL（確定申告ラク化）
-- 作成日: 2026-05-15
-- 前提: MySQL 8.0 / InnoDB / utf8mb4
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------
-- マスタ
-- -----------------------------
CREATE TABLE IF NOT EXISTS shops (
  shop_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  shop_name VARCHAR(100) NOT NULL,
  fee_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
  free_shipping_threshold DECIMAL(12,2) DEFAULT NULL,
  default_shipping_fee DECIMAL(12,2) DEFAULT NULL,
  is_shipping_included TINYINT(1) NOT NULL DEFAULT 0,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (shop_id),
  UNIQUE KEY uk_shops_name (shop_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS items (
  item_code VARCHAR(30) NOT NULL,
  item_name VARCHAR(255) NOT NULL,
  item_size VARCHAR(100) DEFAULT NULL,
  category VARCHAR(100) DEFAULT NULL,
  base_price DECIMAL(12,2) DEFAULT NULL,
  description TEXT DEFAULT NULL,
  memo TEXT DEFAULT NULL,
  main_image VARCHAR(255) DEFAULT NULL,
  cost_basis_type ENUM('tracked','legacy_estimated') NOT NULL DEFAULT 'tracked',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (item_code),
  KEY idx_items_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS item_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  item_code VARCHAR(30) NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  image_order INT NOT NULL DEFAULT 1,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_item_images_item_code_order (item_code, image_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS item_description_templates (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  template_text TEXT NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_item_description_templates_active_order (is_active, sort_order, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS shipping_fees (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  shipping_name VARCHAR(100) NOT NULL,
  fee_amount DECIMAL(12,2) NOT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_shipping_fees_name (shipping_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS expense_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_name VARCHAR(100) NOT NULL,
  tax_account_name VARCHAR(100) DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_expense_categories_name (category_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------
-- 仕入れ
-- -----------------------------
CREATE TABLE IF NOT EXISTS purchases (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  purchase_no VARCHAR(50) DEFAULT NULL,
  purchase_date DATE NOT NULL,
  supplier_name VARCHAR(255) NOT NULL,
  supplier_url VARCHAR(1000) DEFAULT NULL,
  currency_code VARCHAR(10) NOT NULL DEFAULT 'JPY',
  fx_rate DECIMAL(18,6) NOT NULL DEFAULT 1.000000,

  item_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  shipping_fee_1 DECIMAL(12,2) NOT NULL DEFAULT 0,
  shipping_fee_2 DECIMAL(12,2) NOT NULL DEFAULT 0,
  customs_fee DECIMAL(12,2) NOT NULL DEFAULT 0,

  actual_paid_total DECIMAL(12,2) DEFAULT NULL,
  diff_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  diff_reason_code VARCHAR(30) DEFAULT NULL,
  diff_reason_note VARCHAR(255) DEFAULT NULL,

  allocation_method VARCHAR(20) NOT NULL DEFAULT 'equal',
  is_provisional TINYINT(1) NOT NULL DEFAULT 0,
  provisional_resolved_at DATETIME DEFAULT NULL,
  is_stock_registered TINYINT(1) NOT NULL DEFAULT 0,

  total_purchase_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  memo TEXT DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,

  PRIMARY KEY (id),
  UNIQUE KEY uk_purchases_purchase_no (purchase_no),
  KEY idx_purchases_date (purchase_date),
  KEY idx_purchases_provisional (is_provisional),
  KEY idx_purchases_diff_reason (diff_reason_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS purchase_details (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  purchase_id INT UNSIGNED NOT NULL,
  part_code VARCHAR(30) DEFAULT NULL,
  source_item_name VARCHAR(255) DEFAULT NULL,
  name VARCHAR(255) NOT NULL,
  quantity DECIMAL(12,2) NOT NULL,
  defect_qty DECIMAL(12,2) NOT NULL DEFAULT 0,
  unit_price DECIMAL(12,2) NOT NULL,
  line_subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
  item_code VARCHAR(30) DEFAULT NULL,
  is_supply TINYINT(1) NOT NULL DEFAULT 0,
  is_stock_registered TINYINT(1) NOT NULL DEFAULT 0,
  stock_registered_qty DECIMAL(12,2) NOT NULL DEFAULT 0,
  memo TEXT DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_purchase_details_purchase_id (purchase_id),
  KEY idx_purchase_details_item_code (item_code),
  CONSTRAINT fk_purchase_details_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
  CONSTRAINT fk_purchase_details_item FOREIGN KEY (item_code) REFERENCES items(item_code) ON DELETE SET NULL,
  CONSTRAINT chk_purchase_details_qty_nonneg CHECK (quantity >= 0),
  CONSTRAINT chk_purchase_details_defect_nonneg CHECK (defect_qty >= 0),
  CONSTRAINT chk_purchase_details_stock_registered_nonneg CHECK (stock_registered_qty >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS provisional_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  purchase_id INT UNSIGNED DEFAULT NULL,
  provisional_code VARCHAR(50) NOT NULL,
  name VARCHAR(255) NOT NULL,
  quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  memo TEXT DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_provisional_items_code (provisional_code),
  KEY idx_provisional_items_purchase_id (purchase_id),
  CONSTRAINT fk_provisional_items_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------
-- 商品化 / 在庫
-- -----------------------------
CREATE TABLE IF NOT EXISTS productizations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  purchase_id INT UNSIGNED NOT NULL,
  item_code VARCHAR(30) NOT NULL,
  completed_qty DECIMAL(12,2) NOT NULL,
  allocated_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  unit_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  allocation_method VARCHAR(20) NOT NULL DEFAULT 'equal',
  is_incomplete TINYINT(1) NOT NULL DEFAULT 0,
  incomplete_note VARCHAR(255) DEFAULT NULL,
  inventory_reflected TINYINT(1) NOT NULL DEFAULT 0,
  memo TEXT DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_productizations_purchase_id (purchase_id),
  KEY idx_productizations_item_code (item_code),
  KEY idx_productizations_incomplete (is_incomplete),
  CONSTRAINT fk_productizations_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
  CONSTRAINT fk_productizations_item FOREIGN KEY (item_code) REFERENCES items(item_code) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS productization_materials (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  productization_id INT UNSIGNED NOT NULL,
  purchase_detail_id INT UNSIGNED NOT NULL,
  allocated_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  consumed_qty DECIMAL(12,2) NOT NULL DEFAULT 0,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_pm_productization_id (productization_id),
  KEY idx_pm_purchase_detail_id (purchase_detail_id),
  CONSTRAINT fk_pm_productization FOREIGN KEY (productization_id) REFERENCES productizations(id) ON DELETE CASCADE,
  CONSTRAINT fk_pm_purchase_detail FOREIGN KEY (purchase_detail_id) REFERENCES purchase_details(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS inventory_lots (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  item_code VARCHAR(30) NOT NULL,
  purchase_id INT UNSIGNED DEFAULT NULL,
  productization_id INT UNSIGNED DEFAULT NULL,
  quantity DECIMAL(12,2) NOT NULL,
  remaining_qty DECIMAL(12,2) NOT NULL,
  unit_cost DECIMAL(12,2) NOT NULL,
  cost_basis_type ENUM('tracked','legacy_estimated') NOT NULL DEFAULT 'tracked',
  registered_date DATE NOT NULL,
  memo TEXT DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_inventory_lots_item_code (item_code),
  KEY idx_inventory_lots_registered_date (registered_date),
  KEY idx_inventory_lots_cost_basis (cost_basis_type, item_code, remaining_qty),
  CONSTRAINT fk_inventory_lots_item FOREIGN KEY (item_code) REFERENCES items(item_code) ON DELETE RESTRICT,
  CONSTRAINT fk_inventory_lots_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE SET NULL,
  CONSTRAINT fk_inventory_lots_productization FOREIGN KEY (productization_id) REFERENCES productizations(id) ON DELETE SET NULL,
  CONSTRAINT chk_inventory_lots_qty_nonneg CHECK (quantity >= 0),
  CONSTRAINT chk_inventory_lots_remaining_nonneg CHECK (remaining_qty >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS inventory_adjustments (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  item_code VARCHAR(30) NOT NULL,
  adjust_date DATE NOT NULL,
  adjust_qty DECIMAL(12,2) NOT NULL,
  unit_cost DECIMAL(12,2) DEFAULT NULL,
  reason_code VARCHAR(30) NOT NULL DEFAULT 'other',
  reason_note VARCHAR(255) DEFAULT NULL,
  memo TEXT DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_inventory_adjustments_item_date (item_code, adjust_date),
  KEY idx_inventory_adjustments_reason (reason_code),
  CONSTRAINT fk_inventory_adjustments_item FOREIGN KEY (item_code) REFERENCES items(item_code) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS shop_inventories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  shop_id INT UNSIGNED NOT NULL,
  item_code VARCHAR(30) NOT NULL,
  stock_quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_shop_inventory (shop_id, item_code),
  KEY idx_shop_inventories_item_code (item_code),
  CONSTRAINT fk_shop_inventories_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id) ON DELETE CASCADE,
  CONSTRAINT fk_shop_inventories_item FOREIGN KEY (item_code) REFERENCES items(item_code) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS shop_item_prices (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  shop_id INT UNSIGNED NOT NULL,
  item_code VARCHAR(30) NOT NULL,
  sale_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  margin_rate DECIMAL(7,2) NOT NULL DEFAULT 0,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_shop_item_prices_shop_item (shop_id, item_code),
  KEY idx_shop_item_prices_item_code (item_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------
-- 売上 / 原価
-- -----------------------------
CREATE TABLE IF NOT EXISTS sales (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  sale_no VARCHAR(50) DEFAULT NULL,
  shop_id INT UNSIGNED NOT NULL,
  sale_date DATE NOT NULL,
  customer_name VARCHAR(255) DEFAULT NULL,
  shipping_method VARCHAR(100) DEFAULT NULL,
  customer_shipping_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  actual_shipping_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  platform_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_items_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_received_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_cogs_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_profit_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  memo TEXT DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_sales_sale_no (sale_no),
  KEY idx_sales_shop_date (shop_id, sale_date),
  CONSTRAINT fk_sales_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS sale_details (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  sale_id INT UNSIGNED NOT NULL,
  item_code VARCHAR(30) NOT NULL,
  quantity DECIMAL(12,2) NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL,
  line_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  memo TEXT DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_sale_details_sale_id (sale_id),
  KEY idx_sale_details_item_code (item_code),
  CONSTRAINT fk_sale_details_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
  CONSTRAINT fk_sale_details_item FOREIGN KEY (item_code) REFERENCES items(item_code) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS sale_lot_usages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  sale_id INT UNSIGNED NOT NULL,
  sale_detail_id INT UNSIGNED NOT NULL,
  inventory_lot_id INT UNSIGNED NOT NULL,
  item_code VARCHAR(30) NOT NULL,
  used_qty DECIMAL(12,2) NOT NULL,
  unit_cost DECIMAL(12,2) NOT NULL,
  cogs_amount DECIMAL(12,2) NOT NULL,
  sale_date DATE NOT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_slu_sale_id (sale_id),
  KEY idx_slu_sale_detail_id (sale_detail_id),
  KEY idx_slu_lot_id (inventory_lot_id),
  KEY idx_slu_sale_date (sale_date),
  CONSTRAINT fk_slu_sale FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
  CONSTRAINT fk_slu_sale_detail FOREIGN KEY (sale_detail_id) REFERENCES sale_details(id) ON DELETE CASCADE,
  CONSTRAINT fk_slu_lot FOREIGN KEY (inventory_lot_id) REFERENCES inventory_lots(id) ON DELETE RESTRICT,
  CONSTRAINT fk_slu_item FOREIGN KEY (item_code) REFERENCES items(item_code) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------
-- 経費 / 締め / 証憑 / 監査
-- -----------------------------
CREATE TABLE IF NOT EXISTS expenses (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  expense_date DATE DEFAULT NULL,
  expense_category_id INT UNSIGNED DEFAULT NULL,
  category_name VARCHAR(100) DEFAULT NULL,
  tax_account_name VARCHAR(100) DEFAULT NULL,
  amount DECIMAL(12,2) NOT NULL,
  description VARCHAR(255) DEFAULT NULL,
  memo TEXT DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_expenses_expense_date (expense_date),
  KEY idx_expenses_expense_category_id (expense_category_id),
  CONSTRAINT fk_expenses_expense_category_id FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS monthly_closures (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  target_year SMALLINT UNSIGNED NOT NULL,
  target_month TINYINT UNSIGNED NOT NULL,
  closed_at DATETIME NOT NULL,
  closed_by INT UNSIGNED NOT NULL DEFAULT 1,
  lock_note VARCHAR(255) DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_monthly_closures_ym (target_year, target_month)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS evidence_files (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  target_type VARCHAR(30) NOT NULL,
  target_id INT UNSIGNED NOT NULL,
  original_name VARCHAR(255) NOT NULL,
  stored_path VARCHAR(500) NOT NULL,
  mime_type VARCHAR(100) DEFAULT NULL,
  file_size BIGINT UNSIGNED DEFAULT NULL,
  uploaded_by INT UNSIGNED DEFAULT NULL,
  uploaded_at DATETIME NOT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_evidence_target (target_type, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  table_name VARCHAR(64) NOT NULL,
  record_id BIGINT UNSIGNED NOT NULL,
  action_type VARCHAR(16) NOT NULL,
  changed_by INT UNSIGNED DEFAULT NULL,
  changed_at DATETIME NOT NULL,
  before_json JSON DEFAULT NULL,
  after_json JSON DEFAULT NULL,
  request_id VARCHAR(64) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_audit_table_record (table_name, record_id),
  KEY idx_audit_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

SET FOREIGN_KEY_CHECKS = 1;
