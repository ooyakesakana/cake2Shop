現在のテーブル構成
 
 -- =========================
 -- shop_inventories（在庫割当専用）
 -- =========================
 CREATE TABLE shop_inventories (
   id INT UNSIGNED NOT NULL AUTO_INCREMENT,
   shop_id INT UNSIGNED NOT NULL,
   item_code VARCHAR(30) NOT NULL,
   stock_quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
   created DATETIME NULL,
   modified DATETIME NULL,
   PRIMARY KEY (id),
   UNIQUE KEY uk_shop_inventory (shop_id, item_code),
   KEY idx_shop_inventories_item_code (item_code),
   CONSTRAINT fk_shop_inventories_shop
     FOREIGN KEY (shop_id) REFERENCES shops(shop_id)
     ON DELETE CASCADE,
   CONSTRAINT fk_shop_inventories_item
     FOREIGN KEY (item_code) REFERENCES items(item_code)
     ON DELETE CASCADE
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
 
 -- =========================
 -- purchases
 -- =========================
-CREATE TABLE purchases (
-  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
+CREATE TABLE purchases (
+  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
   purchase_no VARCHAR(50) DEFAULT NULL,
   purchase_date DATE NOT NULL,
   supplier_name VARCHAR(255) NOT NULL,
   supplier_url VARCHAR(1000) DEFAULT NULL,
   currency_code VARCHAR(10) NOT NULL DEFAULT 'JPY',
   fx_rate DECIMAL(18,6) NOT NULL DEFAULT 1.000000,
   item_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
   shipping_fee_domestic DECIMAL(12,2) NOT NULL DEFAULT 0,
   shipping_fee_international DECIMAL(12,2) NOT NULL DEFAULT 0,
   customs_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
   import_tax DECIMAL(12,2) NOT NULL DEFAULT 0,
-  agency_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
-  other_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
-  total_purchase_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
+  agency_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
+  other_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
+  actual_paid_total DECIMAL(12,2) NULL DEFAULT NULL,
+  purchase_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
+  order_discount DECIMAL(12,2) NOT NULL DEFAULT 0,
+  total_purchase_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
   allocation_method VARCHAR(20) NOT NULL DEFAULT 'value_ratio',
   is_temporary TINYINT(1) NOT NULL DEFAULT 0,
   is_stock_registered TINYINT(1) NOT NULL DEFAULT 0,
   memo TEXT DEFAULT NULL,
   created DATETIME NULL,
   modified DATETIME NULL,
   PRIMARY KEY (id),
   UNIQUE KEY uk_purchases_purchase_no (purchase_no)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
 
 -- =========================
 -- purchase_details（現行コード互換）
 -- =========================
 CREATE TABLE purchase_details (
   id INT UNSIGNED NOT NULL AUTO_INCREMENT,
   purchase_id INT UNSIGNED NOT NULL,
   part_code VARCHAR(30) DEFAULT NULL,
   source_item_name VARCHAR(255) DEFAULT NULL,
   name VARCHAR(255) NOT NULL,
   quantity DECIMAL(12,2) NOT NULL,
   unit_price DECIMAL(12,2) NOT NULL,
   line_subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
   item_code VARCHAR(30) DEFAULT NULL,
   is_supply TINYINT(1) NOT NULL DEFAULT 0,
   is_stock_registered TINYINT(1) NOT NULL DEFAULT 0,
@@ -396,26 +399,26 @@ CREATE TABLE expenses (
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
   KEY idx_expenses_expense_category_id (expense_category_id),
   CONSTRAINT fk_expenses_expense_category_id
     FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id)
     ON DELETE SET NULL
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
 
 ALTER TABLE purchase_details
   ADD COLUMN stock_registered_qty DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER is_stock_registered;
 
 ALTER TABLE purchase_details
   ADD CONSTRAINT chk_purchase_details_qty_nonneg CHECK (quantity >= 0),
   ADD CONSTRAINT chk_purchase_details_stock_registered_qty_nonneg CHECK (stock_registered_qty >= 0);
 
 ALTER TABLE inventory_lots
   ADD CONSTRAINT chk_inventory_lots_remaining_nonneg CHECK (remaining_qty >= 0),
-  ADD CONSTRAINT chk_inventory_lots_original_nonneg CHECK (quantity >= 0);
\ No newline at end of file
+  ADD CONSTRAINT chk_inventory_lots_original_nonneg CHECK (quantity >= 0);
-- CakeShop: 現時点で必要なDBセットアップSQL
-- 1) まず既存定義を適用
--    source table.txt;
--
-- 2) table.txt に未収録/運用上必要な差分を適用

-- --------------------------------------------------
-- provisional_items（仮商品情報）
-- --------------------------------------------------
CREATE TABLE IF NOT EXISTS provisional_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  provisional_code VARCHAR(50) NOT NULL,
  name VARCHAR(255) NOT NULL,
  quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  memo TEXT DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  KEY idx_provisional_items_code (provisional_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------
-- purchases: 実支払額と差額(手数料調整)を保持する拡張
-- MySQL 8.0.29+ なら IF NOT EXISTS が利用可能
-- --------------------------------------------------
ALTER TABLE purchases
  ADD COLUMN IF NOT EXISTS actual_paid_total DECIMAL(12,2) NULL DEFAULT NULL COMMENT '実際の請求/支払総額',
  ADD COLUMN IF NOT EXISTS purchase_fee DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT '実支払額が計算合計を超えた差額(購入手数料)',
  ADD COLUMN IF NOT EXISTS order_discount DECIMAL(12,2) NOT NULL DEFAULT 0 COMMENT '実支払額が計算合計を下回った差額(注文値引き)';

-- 参考:
-- もし MySQL が ADD COLUMN IF NOT EXISTS 非対応の場合は、
-- information_schema.columns で列存在確認してから個別ALTERしてください。