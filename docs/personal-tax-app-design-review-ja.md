# 個人運用前提の設計見直し提案（現行コードベース）

> 前提: 対象ユーザーは本人1名、ローカル運用、目的は「確定申告を楽にする」。

## 1) いま最優先で直すべき点（実装コスト小・効果大）

### A. コントローラ命名ミスの修正（必須）
`app/Controller/SalesController.php` の中身が `class ProcurementsController` になっています。CakePHP2ではクラス名/ファイル名不整合はルーティング・読み込み不具合の原因になるため、まず修正必須です。

- 修正案: `class SalesController extends AppController` に変更。

### B. 仕入れ入力項目の意味ずれ修正（必須）
仕入れ画面ラベル「送料1/送料2/関税」と、保存キー `item_amount/intl_shipping/customs_duty` の意味が混線しています。特に `item_amount` が「商品代」でなく「送料1欄」に入る構成になっています。

- 現状画面: 「送料1」に `item_amount` がバインド。
- 保存ロジック: `total_purchase_cost = item_amount + intl_shipping + customs_duty + ...`。

**提案（個人向け簡潔版）**
- フィールドを以下へ統一（名称先行でよい）
  - `item_amount`（商品代）
  - `shipping_fee_1`（サプライヤー→配送センター）
  - `shipping_fee_2`（配送センター→日本/国内送料）
  - `customs_fee`（関税）
  - `actual_paid_total`（実支払額）
- 既存の `intl_shipping/customs_duty` は互換読込して新列へ寄せる。

### C. 「仮仕入れ→商品化不可」ガード（必須）
あなたの運用要件どおり、以下未入力なら商品化へ進ませないガードを `productize()` 前段に入れるべきです。

- 送料2（空欄は0でも可）
- 関税（空欄は0でも可）
- 実支払額（必須）

---

## 2) テーブル設計の見直し（個人特化・最小）

## 2.1 purchases は「1注文ヘッダ」として明確化
複数店舗同時購入を1注文で扱いたい要件に合うよう、`purchases` を「注文ヘッダ」に寄せる。

### 追加推奨列
- `shipping_fee_1` DECIMAL(12,2) NOT NULL DEFAULT 0
- `shipping_fee_2` DECIMAL(12,2) NOT NULL DEFAULT 0
- `customs_fee` DECIMAL(12,2) NOT NULL DEFAULT 0
- `actual_paid_total` DECIMAL(12,2) NULL
- `diff_amount` DECIMAL(12,2) NOT NULL DEFAULT 0
- `diff_reason_code` VARCHAR(30) NULL  -- `discount` / `payment_fee` / `fx_adjust`
- `diff_reason_note` VARCHAR(255) NULL
- `is_provisional` TINYINT(1) NOT NULL DEFAULT 0
- `provisional_resolved_at` DATETIME NULL

### 差額計算ルール（固定）
- `base_total = item_amount + shipping_fee_1 + shipping_fee_2 + customs_fee`
- `diff_amount = actual_paid_total - base_total`
  - `diff_amount > 0`: 決済手数料等（`payment_fee`）
  - `diff_amount < 0`: 値引き等（`discount`）

## 2.2 purchase_details は「在庫化対象/備品」を明確化
既存 `is_supply` は維持でOK。個人運用ならこれで十分。

追加推奨:
- `defect_qty` DECIMAL(12,2) NOT NULL DEFAULT 0  （検品で不良だった数）

## 2.3 productizations に「未完了」概念を追加
「一部だけ商品化登録を許可」要件に合わせ、ヘッダ単位か明細単位で未完了管理。

最小構成:
- `productizations.is_incomplete` TINYINT(1) NOT NULL DEFAULT 0
- `productizations.incomplete_note` VARCHAR(255) NULL

運用ルール:
- `is_incomplete=1` の間は `inventory_reflected=0` 強制（フリー在庫へ加算しない）。

## 2.4 sale_lot_usages は必須追加
確定申告用の原価根拠として最重要。これは前提どおり追加推奨。

## 2.5 monthly_closures は最小版で十分
本人1名運用なので、`closed_by` は固定ユーザーでも可。むしろ月次ロック実装を優先。

---

## 3) バリデーション仕様（あなたの運用向けに具体化）

### 3.1 実支払額の乖離チェック
誤入力検知として 5% ルールは妥当。以下で実装推奨。

- `ratio = abs(actual_paid_total - base_total) / NULLIF(base_total,0)`
- `ratio > 0.05` ならエラー
- ただし `override_gap_check=1`（チェックボックス）で保存許可 + 理由メモ必須

### 3.2 商品化可能条件
- `is_provisional=0`
- `actual_paid_total` が NULL でない
- 必須明細が1件以上

### 3.3 旧在庫（原価曖昧）フラグ
「このアプリ導入前在庫」フラグは**任意でなく強制**が安全。

- `items.cost_basis_type` を追加推奨:
  - `tracked`（仕入れ追跡あり）
  - `legacy_estimated`（導入前在庫）

---

## 4) 画面/操作フロー改善（個人利用向け）

1. **仕入登録**: 商品代・送料1・送料2・関税・実支払額を同一画面入力。
2. **仮仕入れ一覧**: 未確定コストのものだけ表示。
3. **商品化登録**: 未完了チェックを付けて部分登録可。
4. **未完了商品化一覧**: 完了まで追跡。
5. **在庫割り振り**: 完了分のみ割り振り対象。
6. **売上登録**: 店舗設定の送料を初期値表示、配送方法で上書き可。
7. **月次出力**: 売上/仕入/原価/在庫/経費CSVをワンクリック。

---

## 5) 今のコードで具体的に直す対象

- `app/Controller/SalesController.php`
  - クラス名修正（`SalesController`）。
- `app/View/procurements/add_purchase.ctp`
  - 入力ラベルとフィールド紐付け修正（商品代/送料1/送料2/関税/実支払額）。
- `app/Controller/ProcurementsController.php`
  - `add_purchase()` の項目正規化、差額計算、乖離5%チェック追加。
  - `productize()` の商品化可否ガード（仮仕入れ未解消時は拒否）。
- `app/Controller/ItemsController.php`
  - 在庫反映条件に `is_incomplete` を加味。

---

## 6) 機能の追加/削除提案（個人運用最適化）

### 追加推奨
- 「仮仕入れ未解消」ダッシュボード件数表示
- 「差額理由未入力」警告
- 売上登録時の納品書PDF出力（既存方針維持）

### 後回しでよい（30日目安を無視する前提）
- 汎用的な高度監査ログ
- 多人数権限管理
- 複雑な会計科目マスタの正規化

### 削ってよい
- 個人運用に不要な過剰な承認フロー
- 使わないCSV種類の先行実装

---

## 7) 最終的な実装順（あなた向け）
1. クラス名/項目名ズレ修正（壊れやすい箇所の安定化）
2. `purchases` 列拡張と差額計算
3. 仮仕入れガード + 商品化ガード
4. `sale_lot_usages` 追加（原価根拠）
5. 月次CSV 5種
6. 必要なら `monthly_closures`（最後でも可）

この順なら、最小工数で「日々の入力しやすさ」と「確定申告に必要な根拠出力」を両立できます。
