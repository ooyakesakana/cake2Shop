<?php
$this->start('subMenu');
echo $this->element('expenses_menu');
$this->end();
?>
<div class="main-area">
	<h2>経費登録</h2>
	<?= $this->Form->create('Expense'); ?>
	<table class="shop-insert-table">
		<tr>
			<th>日付</th>
			<th>カテゴリ</th>
			<th>金額</th>
			<th>内容</th>
			<th>メモ</th>
			<th>登録</th>
		</tr>
		<tr>
			<td>
				<?= $this->Form->input('expense_date', ['type' => 'text', 'label' => false, 'placeholder' => 'YYYY-MM-DD', 'id' => 'expense-date-text']); ?>
				<input type="date" id="expense-date-picker">
			</td>
			<td>
				<?= $this->Form->input('category_select', ['type' => 'select', 'label' => false, 'options' => $expenseCategories, 'empty' => '既存カテゴリから選択', 'id' => 'expense-category-select']); ?>
				<?= $this->Form->input('category_name', ['label' => false, 'placeholder' => '新しいカテゴリを入力', 'id' => 'expense-category-name']); ?>
			</td>
			<td><?= $this->Form->input('amount', ['type' => 'number', 'label' => false, 'min' => 0, 'step' => '0.01', 'after' => ' 円']); ?></td>
			<td><?= $this->Form->input('description', ['label' => false]); ?></td>
			<td><?= $this->Form->input('memo', ['type' => 'textarea', 'label' => false, 'rows' => 2]); ?></td>
			<td><?= $this->Form->submit('登録', ['class' => 'sbm-btn btn--orange']); ?></td>
		</tr>
	</table>
	<?= $this->Form->end(); ?>
	<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>
</div>
<script>
	var expenseDateText = document.getElementById('expense-date-text');
	var expenseDatePicker = document.getElementById('expense-date-picker');
	if (expenseDateText && expenseDatePicker) {
		if (expenseDateText.value) {
			expenseDatePicker.value = expenseDateText.value;
		}
		expenseDatePicker.addEventListener('change', function() {
			expenseDateText.value = expenseDatePicker.value;
		});
		expenseDateText.addEventListener('change', function() {
			expenseDatePicker.value = expenseDateText.value;
		});
	}
	var expenseCategorySelect = document.getElementById('expense-category-select');
	var expenseCategoryName = document.getElementById('expense-category-name');
	if (expenseCategorySelect && expenseCategoryName) {
		expenseCategorySelect.addEventListener('change', function() {
			if (expenseCategorySelect.value) {
				expenseCategoryName.value = expenseCategorySelect.value;
			}
		});
	}
</script>
