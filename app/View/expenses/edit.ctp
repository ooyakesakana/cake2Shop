<?php
$this->start('subMenu');
echo $this->element('expenses_menu');
$this->end();
?>
<div class="main-area">
	<h2>経費編集</h2>
	<?= $this->Form->create('Expense', ['type' => 'file']); ?>
	<table class="shop-insert-table">
		<tr>
			<th>支払日</th>
			<th>到着日</th>
			<th>使用開始日</th>
			<th>支払先</th>
			<th>カテゴリ</th>
			<th>金額</th>
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
				<?= $this->Form->input('category_select', ['type' => 'select', 'label' => false, 'options' => $categoryOptions, 'empty' => '用途カテゴリを選択', 'id' => 'expense-category-select', 'value' => $this->request->data['Expense']['category_name'] ?? '']); ?>
				<?= $this->Form->input('category_name', ['label' => false, 'placeholder' => '新しいカテゴリを入力', 'id' => 'expense-category-name']); ?>
				<div id="expense-category-hint" class="selected-item-summary"></div>
			</td>
			<td><?= $this->Form->input('amount', ['type' => 'number', 'label' => false, 'min' => 0, 'step' => '0.01', 'after' => ' 円']); ?></td>
			<td><?= $this->Form->input('description', ['label' => false]); ?></td>
			<td><?= $this->Form->input('status', ['type' => 'select', 'label' => false, 'options' => $expenseStatuses]); ?></td>
		</tr>
	</table>
	<table class="shop-insert-table">
		<tr>
			<th>減価償却対象</th>
			<th>家事按分率</th>
			<th>証憑ファイル追加</th>
			<th>登録済み証憑</th>
			<th>メモ</th>
			<th>更新</th>
		</tr>
		<tr>
			<td><?= $this->Form->input('is_depreciation', ['type' => 'checkbox', 'label' => '対象', 'id' => 'is-depreciation']); ?></td>
			<td><?= $this->Form->input('business_use_rate', ['type' => 'number', 'label' => false, 'min' => 0, 'max' => 100, 'step' => '0.01', 'after' => ' %']); ?></td>
			<td><input type="file" name="data[Attachment][files][]" multiple accept="image/*,application/pdf"></td>
			<td>
				<?php if (!empty($expense['Attachment'])): ?>
					<?php foreach ($expense['Attachment'] as $attachment): ?>
						<?= $this->Html->link(h($attachment['original_name']), '/' . $attachment['file_path'], ['target' => '_blank']); ?><br>
					<?php endforeach; ?>
				<?php else: ?>
					-
				<?php endif; ?>
			</td>
			<td><?= $this->Form->input('memo', ['type' => 'textarea', 'label' => false, 'rows' => 2]); ?></td>
			<td><?= $this->Form->submit('更新', ['class' => 'sbm-btn btn--orange']); ?></td>
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
	function updateCategoryHint() {
		var meta = categoryMeta[expenseCategorySelect.value] || {};
		var hints = [];
		if (meta.accounting_type) {
			hints.push('分類候補: ' + meta.accounting_type);
		}
		if (Number(meta.is_asset_candidate || 0) === 1) {
			hints.push('減価償却候補');
		}
		expenseCategoryHint.textContent = hints.join(' / ');
	}
	if (expenseCategorySelect && expenseCategoryName) {
		expenseCategorySelect.addEventListener('change', function() {
			if (expenseCategorySelect.value) {
				expenseCategoryName.value = expenseCategorySelect.value;
			}
			updateCategoryHint();
		});
		updateCategoryHint();
	}
</script>
