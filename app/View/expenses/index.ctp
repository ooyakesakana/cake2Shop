<?php
$this->start('subMenu');
echo $this->element('expenses_menu');
$this->end();
?>
<div class="main-area">
	<h2>経費リスト</h2>
	<table class="shop-insert-table">
		<tr>
			<th>日付</th>
			<th>カテゴリ</th>
			<th>勘定科目</th>
			<th>金額</th>
			<th>内容</th>
			<th>メモ</th>
		</tr>
		<?php if (!empty($expenses)): ?>
			<?php foreach ($expenses as $expense): ?>
				<tr>
					<td><?= h($expense['Expense']['expense_date']); ?></td>
					<td><?= h($expense['Expense']['category_name']); ?></td>
					<td><?= h($expense['Expense']['tax_account_name']); ?></td>
					<td><?= h(number_format((float)$expense['Expense']['amount'])); ?> 円</td>
					<td><?= h($expense['Expense']['description']); ?></td>
					<td><?= !empty($expense['Expense']['memo']) ? nl2br(h($expense['Expense']['memo'])) : '-'; ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="6">登録済み経費はありません。</td>
			</tr>
		<?php endif; ?>
	</table>
	<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>
</div>
