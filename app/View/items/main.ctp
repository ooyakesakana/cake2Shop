<?php $this->start('subMenu');
echo $this->element('item_menu');
$this->end(); ?>
<div class="main-area item-main-area">
	<h2 class="compact-page-title">商品検索</h2>
	<?= $this->Form->create(false, ['type' => 'get']); ?>
	<table class="shop-insert-table">
		<tr>
			<th>商品名</th>
			<th>カテゴリ</th>
			<th>在庫数</th>
			<th>在庫条件</th>
			<th>表示件数</th>
			<th>検索</th>
		</tr>
		<tr>
			<td><?= $this->Form->input('keyword', ['label' => false, 'value' => $keyword]); ?></td>
			<td><?= $this->Form->input('category', ['type' => 'select', 'label' => false, 'options' => $categoryList, 'empty' => 'すべて', 'value' => $category]); ?></td>
			<td><?= $this->Form->input('stock_value', ['label' => false, 'value' => $stockValue, 'after' => ' 個']); ?></td>
			<td><?= $this->Form->input('stock_compare', ['type' => 'select', 'label' => false, 'options' => ['gte' => '以上', 'lte' => '以下', 'eq' => '一致'], 'empty' => '条件なし', 'value' => $stockCompare]); ?></td>
			<td><?= $this->Form->input('limit', ['type' => 'select', 'label' => false, 'options' => $allowedLimits, 'value' => $limit]); ?></td>
			<td><?= $this->Form->submit('検索', ['class' => 'sbm-btn btn--blue']); ?></td>
		</tr>
	</table><?= $this->Form->end(); ?>

	<?php if (empty($pagedResultItems)): ?>
		<p>登録済み商品はありません。</p>
	<?php endif; ?>

	<?php if (!empty($pagedResultItems)): ?>
		<div class="item-pager">
			<span><?= h($totalItems); ?>件中 <?= h(($page - 1) * $limit + 1); ?> - <?= h(min($page * $limit, $totalItems)); ?>件表示</span>
			<?php
				$queryBase = [
					'keyword' => $keyword,
					'category' => $category,
					'stock_value' => $stockValue,
					'stock_compare' => $stockCompare,
					'limit' => $limit,
				];
			?>
			<?= $page > 1 ? $this->Html->link('前へ', ['action' => 'main', '?' => $queryBase + ['page' => $page - 1]], ['class' => 'sbm-btn btn--blue']) : '<span class="sbm-btn btn--disabled">前へ</span>'; ?>
			<span><?= h($page); ?> / <?= h($totalPages); ?></span>
			<?= $page < $totalPages ? $this->Html->link('次へ', ['action' => 'main', '?' => $queryBase + ['page' => $page + 1]], ['class' => 'sbm-btn btn--blue']) : '<span class="sbm-btn btn--disabled">次へ</span>'; ?>
		</div>
	<?php endif; ?>

	<?php foreach ($pagedResultItems as $item):
		$code = $item['Item']['item_code'];
		$formId = 'item-edit-' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $code);
		$rowLow = !empty($item['Item']['is_total_low']);
		$shopStockMap = !empty($item['Item']['shop_stock_map']) ? $item['Item']['shop_stock_map'] : [];
		$avgCost = (float)$item['Item']['avg_cost'];
	?>
		<form id="<?= h($formId); ?>" method="post" action="<?= $this->Html->url(['controller' => 'items', 'action' => 'quick_update_item']); ?>"></form>
		<table class="shop-insert-table item-list-table js-item-row <?= $rowLow ? 'is-low-stock' : '' ?>">
			<tr>
				<td rowspan="2" class="item-list-thumb-cell">
					<img src="<?= $this->Html->url('/img/items/' . h($item['Item']['thumb_image'])) ?>" alt="thumb" class="item-list-thumb">
				</td>
				<th>商品コード</th>
				<th class="item-list-name-col">商品名</th>
				<th>カテゴリ</th>
				<th>在庫</th>
				<th>サイズ</th>
				<th>原価</th>
				<th>メモ</th>
				<th>編集</th>
			</tr>
			<tr>
				<td>
					<?= h($item['Item']['item_code']); ?>
					<input type="hidden" name="item_code" value="<?= h($item['Item']['item_code']); ?>" form="<?= h($formId); ?>">
				</td>
				<td class="item-list-name-cell" title="<?= h($item['Item']['item_name']); ?>">
					<span class="item-display-value"><?= h($item['Item']['item_name']); ?></span>
					<input class="item-edit-field" type="text" name="data[Item][item_name]" value="<?= h($item['Item']['item_name']); ?>" form="<?= h($formId); ?>" disabled>
				</td>
				<td>
					<span class="item-display-value"><?= h($item['Item']['category']); ?></span>
					<input class="item-edit-field" type="text" name="data[Item][category]" value="<?= h($item['Item']['category']); ?>" form="<?= h($formId); ?>" disabled>
				</td>
				<td><?= h($item['Item']['lot_total_stock']); ?> / フリー <?= h($item['Item']['free_stock']); ?></td>
				<td>
					<span class="item-display-value"><?= !empty($item['Item']['item_size']) ? h($item['Item']['item_size']) : '-'; ?></span>
					<input class="item-edit-field" type="text" name="data[Item][item_size]" value="<?= !empty($item['Item']['item_size']) ? h($item['Item']['item_size']) : ''; ?>" form="<?= h($formId); ?>" disabled>
				</td>
				<td>
					<span class="item-display-value"><?= h(number_format((float)$item['Item']['base_price'])); ?>円</span>
					<input class="item-edit-field item-edit-price" type="number" step="0.01" name="data[Item][base_price]" value="<?= h($item['Item']['base_price']); ?>" form="<?= h($formId); ?>" disabled>
				</td>
				<td class="item-list-memo-cell" title="<?= !empty($item['Item']['memo']) ? h($item['Item']['memo']) : ''; ?>">
					<span class="item-display-value"><?= !empty($item['Item']['memo']) ? h($item['Item']['memo']) : '-'; ?></span>
					<textarea class="item-edit-field item-edit-memo" name="data[Item][memo]" form="<?= h($formId); ?>" disabled><?= !empty($item['Item']['memo']) ? h($item['Item']['memo']) : ''; ?></textarea>
				</td>
				<td class="item-list-actions">
					<button type="button" class="sbm-btn btn--orange js-edit-item">編集</button>
					<button type="submit" class="sbm-btn btn--purple item-confirm-button" form="<?= h($formId); ?>" disabled>確定</button>
					<?php if ((float)$item['Item']['lot_total_stock'] == 0.0): ?>
						<form method="post" action="<?= $this->Html->url(['controller' => 'items', 'action' => 'deactivate', $item['Item']['item_code']]); ?>" class="inline-delete-form" onsubmit="return confirm('この商品を削除しますか？');">
							<button type="submit" class="sbm-btn btn--red">削除</button>
						</form>
					<?php else: ?>
						<span class="sbm-btn btn--disabled">削除</span>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<td colspan="9" class="item-shop-grid-cell">
					<div class="item-shop-grid">
						<?php foreach ($shops as $shop):
							$sid = (int)$shop['Shop']['shop_id'];
							$currentStock = isset($shopStockMap[$sid]) ? (int)$shopStockMap[$sid] : 0;
							$priceRow = !empty($priceMap[$code][$sid]) ? $priceMap[$code][$sid] : null;
							$salePrice = $priceRow ? (float)$priceRow['sale_price'] : 0;
							$feePercent = (float)$shop['Shop']['fee_percent'];
							$isShippingIncluded = (int)$shop['Shop']['is_shipping_included'] === 1;
							$defaultShippingFeeId = !empty($shop['Shop']['default_shipping_fee']) ? (int)$shop['Shop']['default_shipping_fee'] : 0;
							$shippingCostForMargin = ($isShippingIncluded && $defaultShippingFeeId > 0) ? (float)($shippingFeeAmountMap[$defaultShippingFeeId] ?? 0) : 0;
							$profitRate = 0;
							if ($salePrice > 0) {
								$profitRate = (($salePrice - $shippingCostForMargin - ($salePrice * ($feePercent / 100)) - $avgCost) / $salePrice) * 100;
							}
						?>
							<div class="item-shop-panel <?= $currentStock <= 5 ? 'is-low-stock' : '' ?>">
								<div class="item-shop-panel__name"><?= h($shop['Shop']['shop_name']); ?></div>
								<div class="item-shop-panel__body">
									<?= $this->Form->create(false, ['url' => ['action' => 'quick_update_stock'], 'class' => 'stock-update-form']); ?>
									<?= $this->Form->input('item_code', ['type' => 'hidden', 'value' => $code]); ?>
									<?= $this->Form->input('shop_id', ['type' => 'hidden', 'value' => $sid]); ?>
									<span class="item-shop-panel__label">在庫 <?= h($currentStock); ?></span>
									<?= $this->Form->input('stock_quantity', ['type' => 'select', 'label' => false, 'options' => array_combine(range(0, 200), range(0, 200)), 'value' => $currentStock, 'after' => ' 個']); ?>
									<?= $this->Form->submit('確定', ['class' => 'sbm-btn btn--purple']); ?>
									<?= $this->Form->end(); ?>
									<div class="item-shop-panel__price">
										<span><?= $salePrice > 0 ? h(number_format($salePrice)) . '円' . ($isShippingIncluded ? '（送込）' : '') : '-'; ?></span>
										<span>利益率 <?= $salePrice > 0 ? h(number_format($profitRate, 1)) . '%' : '-'; ?></span>
									</div>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</td>
			</tr>
		</table>
	<?php endforeach; ?>
</div>

<script>
(function () {
	document.querySelectorAll('.js-edit-item').forEach(function (button) {
		button.addEventListener('click', function () {
			var row = button.closest('.js-item-row');
			if (!row) {
				return;
			}
			row.classList.add('is-editing');
			row.querySelectorAll('.item-edit-field').forEach(function (field) {
				field.disabled = false;
			});
			var confirmButton = row.querySelector('.item-confirm-button');
			if (confirmButton) {
				confirmButton.disabled = false;
			}
		});
	});
})();
</script>
