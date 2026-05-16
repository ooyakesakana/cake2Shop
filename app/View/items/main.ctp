<?php $this->start('subMenu');
echo $this->element('item_menu');
$this->end(); ?>
<div class="main-area">
	<h2>商品検索</h2>
	<?= $this->Form->create(false, ['type' => 'get']); ?>
	<table class="shop-insert-table">
		<tr>
			<th>商品名</th>
			<th>カテゴリ</th>
			<th>在庫条件</th>
			<th>在庫数</th>
			<th>検索</th>
		</tr>
		<tr>
			<td><?= $this->Form->input('keyword', ['label' => false, 'value' => $keyword]); ?></td>
			<td><?= $this->Form->input('category', ['type' => 'select', 'label' => false, 'options' => $categoryList, 'empty' => 'すべて', 'value' => $category]); ?></td>
			<td><?= $this->Form->input('stock_compare', ['type' => 'select', 'label' => false, 'options' => ['gte' => '以上', 'lte' => '以下', 'eq' => '一致'], 'empty' => '条件なし', 'value' => $stockCompare]); ?></td>
			<td><?= $this->Form->input('stock_value', ['label' => false, 'value' => $stockValue, 'after' => ' 個']); ?></td>
			<td><?= $this->Form->submit('検索', ['class' => 'sbm-btn btn--blue']); ?></td>
		</tr>
	</table><?= $this->Form->end(); ?>

	<?php foreach ($resultItems as $item):
		$code = $item['Item']['item_code'];
		$rowLow = !empty($item['Item']['is_total_low']);
		$shopStockMap = !empty($item['Item']['shop_stock_map']) ? $item['Item']['shop_stock_map'] : [];
		$avgCost = (float)$item['Item']['avg_cost'];
	?>
		<table class="shop-insert-table" style="margin-bottom:18px; <?= $rowLow ? 'background:#ffe5e5;' : '' ?>">
			<tbody>
				<tr>
					<td rowspan="4" style="width:150px;"><img src="<?= $this->Html->url('/img/items/' . h($item['Item']['thumb_image'])) ?>" alt="thumb" style="width:140px;height:140px;object-fit:cover"></td>
					<th>商品コード</th>
					<th>商品名</th>
					<th>カテゴリ</th>
					<th>トータル在庫</th>
					<th>フリー在庫</th>
					<th>サイズ</th>
					<th>メモ</th>
					<th>編集</th>
				</tr>
				<tr>
					<td><?= h($item['Item']['item_code']); ?></td>
					<td><?= h($item['Item']['item_name']); ?></td>
					<td><?= h($item['Item']['category']); ?></td>
					<td><?= h($item['Item']['lot_total_stock']); ?></td>
					<td><?= h($item['Item']['free_stock']); ?></td>
					<td><?= !empty($item['Item']['size']) ? h($item['Item']['size']) : '-'; ?></td>
					<td><?= !empty($item['Item']['memo']) ? h($item['Item']['memo']) : '-'; ?></td>
					<td>
						<?= $this->Html->link('編集', ['action' => 'edit', $item['Item']['id']], ['class' => 'sbm-btn btn--orange']); ?>
						<?= $this->Html->link('確定', ['action' => 'edit', $item['Item']['id']], ['class' => 'sbm-btn btn--purple']); ?>
						<?php if ((float)$item['Item']['lot_total_stock'] == 0.0): ?>
							<span class="sbm-btn btn--red">削除</span>
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<?php foreach ($shops as $shop): ?>
						<th colspan="2"><?= h($shop['Shop']['shop_name']); ?></th>
					<?php endforeach; ?>
				</tr>
				<tr>
					<?php foreach ($shops as $shop):
						$sid = (int)$shop['Shop']['shop_id'];
						$currentStock = isset($shopStockMap[$sid]) ? (int)$shopStockMap[$sid] : 0;
						$priceRow = !empty($priceMap[$code][$sid]) ? $priceMap[$code][$sid] : null;
						$salePrice = $priceRow ? (float)$priceRow['sale_price'] : 0;
						$feePercent = (float)$shop['Shop']['fee_percent'];
						$isShippingIncluded = (int)$shop['Shop']['is_shipping_included'] === 1;
						$profitRate = 0;
						if ($salePrice > 0) {
							$profitRate = (($salePrice - ($salePrice * ($feePercent / 100)) - $avgCost) / $salePrice) * 100;
						}
					?>
						<td style="<?= $currentStock <= 5 ? 'background:#fff3cd;' : '' ?>">
							在庫数: <?= h($currentStock); ?><br>
							<?= $this->Form->create(false, ['url' => ['action' => 'quick_update_stock']]); ?>
							<?= $this->Form->input('item_code', ['type' => 'hidden', 'value' => $code]); ?>
							<?= $this->Form->input('shop_id', ['type' => 'hidden', 'value' => $sid]); ?>
							<?= $this->Form->input('stock_quantity', ['type' => 'select', 'label' => false, 'options' => array_combine(range(0, 200), range(0, 200)), 'value' => $currentStock, 'after' => ' 個']); ?>
							<?= $this->Form->submit('確定', ['class' => 'sbm-btn btn--purple']); ?>
							<?= $this->Form->end(); ?>
						</td>
						<td>
							販売価格: <?= $salePrice > 0 ? h(number_format($salePrice)) . '円' : '-'; ?><br>
							送料込: <?= $isShippingIncluded ? '注文時送料を差引' : '-'; ?><br>
							利益率: <?= $salePrice > 0 ? h(number_format($profitRate, 1)) . '%' : '-'; ?>
						</td>
					<?php endforeach; ?>
				</tr>
			</tbody>
		</table>
	<?php endforeach; ?>
</div>
