<?php
$this->start('subMenu');
echo $this->element('expenses_menu');
$this->end();
?>
<div class="main-area">
	<h2>経費リスト</h2>
	<?= $this->Form->create(false, ['type' => 'get']); ?>
	<table class="shop-insert-table">
		<tr>
			<th>支払日 From</th>
			<th>支払日 To</th>
			<th>カテゴリ</th>
			<th>状態</th>
			<th>減価償却</th>
			<th>キーワード</th>
			<th>検索</th>
		</tr>
		<tr>
			<td>
				<input type="text" name="date_from" id="expense-date-from-text" value="<?= h($dateFrom); ?>" placeholder="YYYY-MM-DD">
				<input type="date" id="expense-date-from-picker">
			</td>
			<td>
				<input type="text" name="date_to" id="expense-date-to-text" value="<?= h($dateTo); ?>" placeholder="YYYY-MM-DD">
				<input type="date" id="expense-date-to-picker">
			</td>
			<td><?= $this->Form->input('category', ['type' => 'select', 'label' => false, 'options' => $categoryOptions, 'empty' => 'すべて', 'value' => $category]); ?></td>
			<td><?= $this->Form->input('status', ['type' => 'select', 'label' => false, 'options' => $expenseStatuses, 'empty' => 'すべて', 'value' => $status]); ?></td>
			<td>
				<?= $this->Form->input('depreciation', [
					'type' => 'select',
					'label' => false,
					'options' => ['1' => '対象のみ', '0' => '対象外のみ', 'pending' => '仮登録のみ'],
					'empty' => 'すべて',
					'value' => $depreciation,
				]); ?>
			</td>
			<td><input type="text" name="keyword" value="<?= h($keyword); ?>" placeholder="支払先・内容・メモ"></td>
			<td>
				<?= $this->Form->submit('検索', ['class' => 'sbm-btn btn--blue']); ?>
				<?= $this->Html->link('クリア', ['action' => 'index'], ['class' => 'sbm-btn btn--green']); ?>
			</td>
		</tr>
	</table>
	<?= $this->Form->end(); ?>

	<table class="shop-insert-table">
		<tr>
			<th>支払日</th>
			<th>到着日</th>
			<th>使用開始日</th>
			<th>支払先</th>
			<th>カテゴリ</th>
			<th>分類候補</th>
			<th>実支払額</th>
			<th>購入総額</th>
			<th>値引/ポイント</th>
			<th>按分率</th>
			<th>状態</th>
			<th>減価償却</th>
			<th>証憑</th>
			<th>内容</th>
			<th>メモ</th>
			<th>操作</th>
		</tr>
		<?php if (!empty($expenses)): ?>
			<?php foreach ($expenses as $expense): ?>
				<?php
					$statusLabels = ['ordered' => '注文済', 'paid' => '支払済', 'received' => '到着済', 'active' => '使用開始'];
					$status = $expense['Expense']['status'] ?? '';
					$isDepreciationPending = !empty($expense['Expense']['is_depreciation']) && $status !== 'active';
				?>
				<tr class="<?= $isDepreciationPending ? 'expense-row--pending' : ''; ?>">
					<td><?= h($expense['Expense']['expense_date']); ?></td>
					<td><?= !empty($expense['Expense']['arrival_date']) ? h($expense['Expense']['arrival_date']) : '-'; ?></td>
					<td><?= !empty($expense['Expense']['use_start_date']) ? h($expense['Expense']['use_start_date']) : '-'; ?></td>
					<td><?= !empty($expense['Expense']['vendor_name']) ? h($expense['Expense']['vendor_name']) : '-'; ?></td>
					<td><?= h($expense['Expense']['category_name']); ?></td>
					<td><?= h($expense['Expense']['accounting_type'] ?? $expense['Expense']['tax_account_name'] ?? '-'); ?></td>
					<td><?= h(number_format((float)$expense['Expense']['amount'])); ?> 円</td>
					<td><?= isset($expense['Expense']['gross_amount']) && $expense['Expense']['gross_amount'] !== null ? h(number_format((float)$expense['Expense']['gross_amount'])) . ' 円' : '-'; ?></td>
					<td>
						<?php
							$coupon = isset($expense['Expense']['coupon_discount_amount']) ? (float)$expense['Expense']['coupon_discount_amount'] : 0;
							$point = isset($expense['Expense']['point_used_amount']) ? (float)$expense['Expense']['point_used_amount'] : 0;
						?>
						<?= ($coupon > 0 || $point > 0) ? 'クーポン ' . h(number_format($coupon)) . ' / ポイント ' . h(number_format($point)) . ' 円' : '-'; ?>
					</td>
					<td><?= isset($expense['Expense']['business_use_rate']) ? h((float)$expense['Expense']['business_use_rate']) . ' %' : '-'; ?></td>
					<td><?= h($statusLabels[$status] ?? ($status ?: '-')); ?></td>
					<td><?= !empty($expense['Expense']['is_depreciation']) ? ($isDepreciationPending ? '仮登録' : '対象') : '-'; ?></td>
					<td>
						<?php if (!empty($expense['Attachment'])): ?>
							<?php foreach ($expense['Attachment'] as $attachment): ?>
								<?php
									$fileUrl = $this->Html->url('/' . $attachment['file_path']);
									$ext = strtolower(pathinfo($attachment['file_path'], PATHINFO_EXTENSION));
									$isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
								?>
								<?php if ($isImage): ?>
									<a href="<?= h($fileUrl); ?>" target="_blank"><img src="<?= h($fileUrl); ?>" alt="<?= h($attachment['original_name']); ?>" class="expense-attachment-thumb" width="64" height="48"></a>
								<?php endif; ?>
								<?= $this->Html->link(h($attachment['original_name']), '/' . $attachment['file_path'], ['target' => '_blank']); ?>
								<?= !empty($attachment['memo']) ? '（' . h($attachment['memo']) . '）' : ''; ?><br>
							<?php endforeach; ?>
						<?php else: ?>
							-
						<?php endif; ?>
					</td>
					<td><?= h($expense['Expense']['description']); ?></td>
					<td><?= !empty($expense['Expense']['memo']) ? nl2br(h($expense['Expense']['memo'])) : '-'; ?></td>
					<td><?= $this->Html->link($isDepreciationPending ? '確定/編集' : '編集', ['action' => 'edit', $expense['Expense']['id']], ['class' => 'sbm-btn btn--orange']); ?></td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="16">登録済み経費はありません。</td>
			</tr>
		<?php endif; ?>
	</table>
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
	bindDatePicker('expense-date-from-text', 'expense-date-from-picker');
	bindDatePicker('expense-date-to-text', 'expense-date-to-picker');
</script>
