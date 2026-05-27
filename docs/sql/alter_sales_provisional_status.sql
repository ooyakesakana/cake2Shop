SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'status') = 0,
  'ALTER TABLE sales ADD COLUMN status ENUM(''provisional'',''confirmed'') NOT NULL DEFAULT ''confirmed'' AFTER net_sales',
  'SELECT ''sales.status already exists'''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND COLUMN_NAME = 'shipping_cost_pending') = 0,
  'ALTER TABLE sales ADD COLUMN shipping_cost_pending TINYINT(1) NOT NULL DEFAULT 0 AFTER status',
  'SELECT ''sales.shipping_cost_pending already exists'''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND INDEX_NAME = 'idx_sales_status') = 0,
  'ALTER TABLE sales ADD KEY idx_sales_status (status)',
  'SELECT ''idx_sales_status already exists'''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF(
  (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales' AND INDEX_NAME = 'idx_sales_shipping_cost_pending') = 0,
  'ALTER TABLE sales ADD KEY idx_sales_shipping_cost_pending (shipping_cost_pending)',
  'SELECT ''idx_sales_shipping_cost_pending already exists'''
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
