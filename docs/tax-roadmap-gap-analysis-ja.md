# ロードマップ整合のためのDB/コード差分提案（現行実装ベース）

## 1. 先に結論（優先順）
1. **月次締め統制**: `monthly_closures` を追加し、保存系処理で締め月ロック。
2. **COGSトレース**: `sale_lot_usages` を追加し、売上確定時にFIFO消費をトランザクション保存。
3. **期末在庫評価**: FIFO計算用の集計SQL/CSV出力を追加。
4. **証憑/差額理由/調整理由**: `evidence_files`、差額理由、在庫調整理由を最小追加。
5. **監査証跡**: 最低限 `audit_logs` を追加し、主要更新のみ記録。

---

## 2. 現行コード確認で見えたギャップ

### 2.1 仕入登録の項目不一致（重要）
`ProcurementsController::add_purchase()` では `intl_shipping` / `customs_duty` を使用していますが、提示DDLは `shipping_fee_international` / `customs_fee` です。保存キーの不一致で正しく入らない可能性があります。

- 現行コードキー: `intl_shipping`, `customs_duty`。
- DDLキー: `shipping_fee_international`, `customs_fee`。

**対応方針**
- どちらかに統一（推奨: DB列名に合わせる）。
- 互換期間は `COALESCE` 的に両方受けて最終列へ正規化。

### 2.2 月次締めロックが未実装
締め対象月への編集禁止判定が、保存系アクション（仕入・売上・経費・在庫調整）に未導入。

### 2.3 COGS確定の根拠テーブルが未実装
`inventory_lots` はあるが、売上時に「どのロットを何個消費したか」を残す `sale_lot_usages` が未確認。

### 2.4 差額理由の必須化が未実装
`actual_paid_total / purchase_fee / order_discount` は列追加案があるが、「なぜ差が出たか」の理由管理が未定義。

---

## 3. 追加推奨テーブル（最小構成DDL案）

### 3.1 monthly_closures
```sql
CREATE TABLE monthly_closures (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  target_year SMALLINT UNSIGNED NOT NULL,
  target_month TINYINT UNSIGNED NOT NULL,
  closed_at DATETIME NOT NULL,
  closed_by INT UNSIGNED NOT NULL,
  lock_note VARCHAR(255) DEFAULT NULL,
  created DATETIME NULL,
  modified DATETIME NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uk_monthly_closures_ym (target_year, target_month),
  KEY idx_monthly_closures_closed_at (closed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 3.2 sale_lot_usages（COGS根拠）
```sql
CREATE TABLE sale_lot_usages (
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
  KEY idx_slu_sale_date (sale_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

### 3.3 evidence_files（証憑）
```sql
CREATE TABLE evidence_files (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  target_type VARCHAR(30) NOT NULL, -- purchase / expense / sale / inventory_adjustment
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
```

### 3.4 audit_logs（最低限）
```sql
CREATE TABLE audit_logs (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  table_name VARCHAR(64) NOT NULL,
  record_id BIGINT UNSIGNED NOT NULL,
  action_type VARCHAR(16) NOT NULL, -- INSERT/UPDATE/DELETE
  changed_by INT UNSIGNED DEFAULT NULL,
  changed_at DATETIME NOT NULL,
  before_json JSON DEFAULT NULL,
  after_json JSON DEFAULT NULL,
  request_id VARCHAR(64) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_audit_table_record (table_name, record_id),
  KEY idx_audit_changed_at (changed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

---

## 4. 既存テーブルの修正提案

### 4.1 purchases
- 既に提案済みの `actual_paid_total / purchase_fee / order_discount` は有効。
- 追加で理由管理を最小化するなら以下のどちらか:
  - A. `purchases` に `gap_reason_code`, `gap_reason_note` を追加。
  - B. 正規化して `purchase_gap_reasons` テーブル新設。

最小工数は **A案**。

```sql
ALTER TABLE purchases
  ADD COLUMN gap_reason_code VARCHAR(30) DEFAULT NULL,
  ADD COLUMN gap_reason_note VARCHAR(255) DEFAULT NULL,
  ADD KEY idx_purchases_gap_reason_code (gap_reason_code);
```

### 4.2 inventory_adjustments（在庫調整）
在庫調整テーブルがある前提で `reason_code` を追加（廃棄/家事消費/返金/棚卸差異など）。

```sql
ALTER TABLE inventory_adjustments
  ADD COLUMN reason_code VARCHAR(30) NOT NULL DEFAULT 'other',
  ADD COLUMN reason_note VARCHAR(255) DEFAULT NULL,
  ADD KEY idx_inventory_adjustments_reason_code (reason_code);
```

---

## 5. コード修正ポイント（CakePHP2）

### 5.1 月次締めロック共通ガード
- 追加: `AppController` に `assertMonthNotClosed($date)` 相当の共通関数。
- 適用先: 保存系アクション（仕入、売上、経費、在庫調整）。
- 判定: `monthly_closures` に `(year,month)` が存在したら保存拒否。

### 5.2 仕入登録の項目名正規化
`ProcurementsController::add_purchase()` の入力キーをDB列へ正規化。

- `intl_shipping` → `shipping_fee_international`
- `customs_duty` → `customs_fee`

加えて `total_purchase_cost` 算出式を上記列で統一。

### 5.3 COGS自動計算
- 売上確定トランザクション内で FIFO 消費。
- `inventory_lots.remaining_qty` を減算。
- 消費明細を `sale_lot_usages` へINSERT。
- `used_qty * unit_cost` を集計して売上原価を確定。

### 5.4 月次CSV出力
最初は Controller + SQL 直書きで可（最小工数）。

- 売上CSV
- 仕入CSV
- 経費CSV
- 原価CSV（`sale_lot_usages`）
- 在庫評価CSV（期末時点の `inventory_lots.remaining_qty * unit_cost`）

### 5.5 証憑ZIP
- `evidence_files` から対象月の証憑を収集。
- CSV一式 + 証憑ファイルをZIP化してDL。

---

## 6. 実装順（30日プランに合わせる）
1. DDL適用（`monthly_closures`, `sale_lot_usages`, `evidence_files`, `audit_logs`）。
2. 仕入キー不一致修正（早期に事故防止）。
3. 月次ロック導入。
4. 売上時FIFO消費 + `sale_lot_usages` 保存。
5. 月次CSV 5種。
6. 期末在庫評価CSV。
7. 証憑紐付け + ZIP。

---

## 7. Done判定の具体化（検収条件）
- 任意月でCSV 5種を同時DL可能。
- 締め済み月の保存系操作がUI/API双方で拒否される。
- 原価CSVの1行から `sale_lot_usages -> inventory_lots -> purchase_details/purchases` へ追跡可能。
- 仕入/経費/在庫調整の明細から `evidence_files` へ辿れる。
