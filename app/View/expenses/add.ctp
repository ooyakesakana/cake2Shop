<?php
$this->start('subMenu');
echo $this->element('expenses_menu');
$this->end();
?>
<div class="main-area">
	<h2>経費登録</h2>
	<?= $this->Form->create('Expense', ['type' => 'file']); ?>
	<table class="shop-insert-table">
		<tr>
			<th>支払日</th>
			<th>到着日</th>
			<th>使用開始日</th>
			<th>支払先</th>
			<th>カテゴリ</th>
			<th>実支払額</th>
			<th>内容</th>
			<th>状態</th>
		</tr>
		<tr>
			<td>
				<?= $this->Form->input('expense_date', ['type' => 'text', 'label' => false, 'placeholder' => 'YYYY-MM-DD', 'id' => 'expense-date-text']); ?>
				<input type="date" id="expense-date-picker">
			</td>
			<td>
				<?= $this->Form->input('arrival_date', ['type' => 'text', 'label' => false, 'placeholder' => 'YYYY-MM-DD', 'id' => 'arrival-date-text']); ?>
				<input type="date" id="arrival-date-picker">
			</td>
			<td>
				<?= $this->Form->input('use_start_date', ['type' => 'text', 'label' => false, 'placeholder' => 'YYYY-MM-DD', 'id' => 'use-start-date-text']); ?>
				<input type="date" id="use-start-date-picker">
			</td>
			<td><?= $this->Form->input('vendor_name', ['label' => false, 'placeholder' => '購入先']); ?></td>
			<td>
				<?= $this->Form->input('category_select', ['type' => 'select', 'label' => false, 'options' => $categoryOptions, 'empty' => '用途カテゴリを選択', 'id' => 'expense-category-select']); ?>
				<?= $this->Form->input('category_name', ['label' => false, 'placeholder' => '新しいカテゴリを入力', 'id' => 'expense-category-name']); ?>
				<div id="expense-category-hint" class="selected-item-summary"></div>
			</td>
			<td><?= $this->Form->input('amount', ['type' => 'number', 'label' => false, 'min' => 0, 'step' => '0.01', 'required' => true, 'id' => 'expense-amount', 'after' => ' 円']); ?></td>
			<td><?= $this->Form->input('description', ['label' => false]); ?></td>
			<td><?= $this->Form->input('status', ['type' => 'select', 'label' => false, 'options' => $expenseStatuses, 'value' => 'paid']); ?></td>
		</tr>
	</table>
	<details class="expense-amount-detail">
		<summary>ポイント・クーポン・値引きを入力する</summary>
		<table class="shop-insert-table">
			<tr>
				<th>購入総額</th>
				<th>クーポン値引き額</th>
				<th>ポイント利用額</th>
				<th>計算上の実支払額</th>
			</tr>
			<tr>
				<td><?= $this->Form->input('gross_amount', ['type' => 'number', 'label' => false, 'min' => 0, 'step' => '0.01', 'id' => 'gross-amount', 'after' => ' 円']); ?></td>
				<td><?= $this->Form->input('coupon_discount_amount', ['type' => 'number', 'label' => false, 'min' => 0, 'step' => '0.01', 'id' => 'coupon-discount-amount', 'after' => ' 円']); ?></td>
				<td><?= $this->Form->input('point_used_amount', ['type' => 'number', 'label' => false, 'min' => 0, 'step' => '0.01', 'id' => 'point-used-amount', 'after' => ' 円']); ?></td>
				<td>
					<span id="calculated-payment-amount">0円</span>
					<div id="payment-amount-warning" class="selected-item-summary"></div>
				</td>
			</tr>
		</table>
	</details>
	<table class="shop-insert-table">
		<tr>
			<th>減価償却対象</th>
			<th>家事按分率</th>
			<th>証憑ファイル</th>
			<th>証憑メモ</th>
			<th>メモ</th>
			<th>登録</th>
		</tr>
		<tr>
			<td><?= $this->Form->input('is_depreciation', ['type' => 'checkbox', 'label' => '対象', 'id' => 'is-depreciation']); ?></td>
			<td><?= $this->Form->input('business_use_rate', ['type' => 'number', 'label' => false, 'min' => 0, 'max' => 100, 'step' => '0.01', 'value' => 100, 'after' => ' %']); ?></td>
			<td><input type="file" name="data[Attachment][files][]" multiple accept="image/*,application/pdf"><div class="selected-item-summary">複数選択可。後から編集画面で追加できます。</div></td>
			<td><input type="text" name="data[Attachment][memo]" placeholder="支払い証明、納品書など"></td>
			<td><?= $this->Form->input('memo', ['type' => 'textarea', 'label' => false, 'rows' => 2]); ?></td>
			<td><?= $this->Form->submit('登録', ['class' => 'sbm-btn btn--orange']); ?></td>
		</tr>
	</table>
	<?= $this->Form->end(); ?>
	<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>
</div>
<script>
	function bindDatePicker(textId, pickerId) {
		var text = document.getElementById(textId);
		var picker = document.getElementById(pickerId);
		if (!text || !picker) {
			return;
		}
		if (text.value) {
			picker.value = text.value;
		}
		picker.addEventListener('change', function() {
			text.value = picker.value;
		});
		text.addEventListener('change', function() {
			picker.value = text.value;
		});
	}
	bindDatePicker('expense-date-text', 'expense-date-picker');
	bindDatePicker('arrival-date-text', 'arrival-date-picker');
	bindDatePicker('use-start-date-text', 'use-start-date-picker');

	var expenseCategorySelect = document.getElementById('expense-category-select');
	var expenseCategoryName = document.getElementById('expense-category-name');
	var expenseCategoryHint = document.getElementById('expense-category-hint');
	var categoryMeta = <?= json_encode($categoryMeta, JSON_UNESCAPED_UNICODE); ?>;
	if (expenseCategorySelect && expenseCategoryName) {
		expenseCategorySelect.addEventListener('change', function() {
			if (expenseCategorySelect.value) {
				expenseCategoryName.value = expenseCategorySelect.value;
			}
			var meta = categoryMeta[expenseCategorySelect.value] || {};
			var hints = [];
			if (meta.accounting_type) {
				hints.push('分類候補: ' + meta.accounting_type);
			}
			if (Number(meta.is_asset_candidate || 0) === 1) {
				hints.push('減価償却候補');
			}
			expenseCategoryHint.textContent = hints.join(' / ');
		});
	}

	function numberValue(id) {
		var element = document.getElementById(id);
		var value = element ? parseFloat(element.value || '0') : 0;
		return isNaN(value) ? 0 : value;
	}
	function formatYen(value) {
		return Math.round(value).toLocaleString() + '円';
	}
	function updatePaymentCalc() {
		var gross = numberValue('gross-amount');
		var coupon = numberValue('coupon-discount-amount');
		var point = numberValue('point-used-amount');
		var actual = numberValue('expense-amount');
		var calculated = Math.max(0, gross - coupon - point);
		var calcDisplay = document.getElementById('calculated-payment-amount');
		var warning = document.getElementById('payment-amount-warning');
		if (calcDisplay) {
			calcDisplay.textContent = formatYen(calculated);
		}
		if (warning) {
			if (gross > 0 && Math.abs(calculated - actual) >= 1) {
				warning.textContent = '実支払額と一致しません。送料・手数料・端数調整がある場合は問題ありません。';
			} else {
				warning.textContent = '';
			}
		}
	}
	['gross-amount', 'coupon-discount-amount', 'point-used-amount', 'expense-amount'].forEach(function(id) {
		var element = document.getElementById(id);
		if (element) {
			element.addEventListener('input', updatePaymentCalc);
		}
	});
	updatePaymentCalc();
</script>
