<?php
$this->start('subMenu');
echo $this->element('item_menu');
$this->end();
?>
<div class="main-area">
	<h2>商品登録が完了しました</h2>
	<p>商品コード: <?= h($item['Item']['item_code']); ?> / 商品名: <?= h($item['Item']['item_name']); ?></p>
	<p>続けてフリー在庫、または各ショップの販売価格と在庫を登録できます。</p>

	<?= $this->Form->create(false); ?>
	<table class="shop-insert-table">
		<tr>
			<th>登録先</th>
			<th>販売価格</th>
			<th>粗利率(自動表示)</th>
			<th>在庫数</th>
			<th>登録</th>
		</tr>
		<tr>
			<td><?= $this->Form->input('ShopEntry.shop_id', ['type' => 'select', 'options' => ['free' => 'フリー在庫'] + Hash::combine($shops, '{n}.Shop.shop_id', '{n}.Shop.shop_name'), 'label' => false, 'id' => 'shop_id', 'value' => 'free']); ?></td>
			<td><?= $this->Form->input('ShopEntry.sale_price', ['type' => 'number', 'step' => '0.01', 'label' => false, 'id' => 'sale_price', 'after' => ' 円']); ?></td>
			<td><input type="text" id="margin_rate" readonly> %</td>
			<td><?= $this->Form->input('ShopEntry.stock_quantity', ['type' => 'number', 'step' => '0.01', 'label' => false, 'after' => ' 個']); ?></td>
			<td><?= $this->Form->submit('登録', ['class' => 'sbm-btn btn--orange']); ?></td>
		</tr>
	</table>
	<?= $this->Form->end(); ?>

	<?= $this->Form->create(false); ?>
	<?= $this->Form->hidden('skip', ['value' => 1]); ?>
	<?= $this->Form->submit('スキップして商品登録へ戻る', ['class' => 'btn btn--blue']); ?>
	<?= $this->Form->end(); ?>

	<table class="shop-insert-table">
		<tr>
			<th>登録先</th>
			<th>現在在庫</th>
			<th>販売価格</th>
			<th>粗利率</th>
		</tr>
		<tr>
			<td>フリー在庫</td>
			<td><?= h($freeStock); ?></td>
			<td>-</td>
			<td>-</td>
		</tr>
		<?php foreach ($shops as $shop): $sid = $shop['Shop']['shop_id']; ?>
			<tr>
				<td><?= h($shop['Shop']['shop_name']); ?></td>
				<td><?= isset($invMap[$sid]) ? h($invMap[$sid]) : 0; ?></td>
				<td><?= isset($priceMap[$sid]['sale_price']) ? h($priceMap[$sid]['sale_price']) : '-'; ?></td>
				<?php
				$displayPrice = isset($priceMap[$sid]['sale_price']) ? (float)$priceMap[$sid]['sale_price'] : 0;
				$feePercent = (float)$shop['Shop']['fee_percent'];
				$shippingCost = (!empty($shop['Shop']['is_shipping_included']) && !empty($shop['Shop']['default_shipping_fee'])) ? (float)($shippingFeeAmountMap[(int)$shop['Shop']['default_shipping_fee']] ?? 0) : 0;
				$feeAmount = $displayPrice * ($feePercent / 100);
				$displayMargin = $displayPrice > 0 ? (($displayPrice - $shippingCost - $feeAmount - (float)($item['Item']['base_price'] ?? 0)) / $displayPrice) * 100 : null;
				?>
				<td><?= $displayMargin !== null ? h(round($displayMargin, 2)) . '%' : '-'; ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>

	<script>
		(function() {
			var p = document.getElementById('sale_price');
			var shop = document.getElementById('shop_id');
			var out = document.getElementById('margin_rate');
			var cost = <?= json_encode((float)($item['Item']['base_price'] ?? 0)); ?>;
			var feePercents = <?= json_encode(Hash::combine($shops, '{n}.Shop.shop_id', '{n}.Shop.fee_percent')); ?>;
			var shippingIncluded = <?= json_encode(Hash::combine($shops, '{n}.Shop.shop_id', '{n}.Shop.is_shipping_included')); ?>;
			var defaultShippingFeeIds = <?= json_encode(Hash::combine($shops, '{n}.Shop.shop_id', '{n}.Shop.default_shipping_fee')); ?>;
			var shippingFeeAmounts = <?= json_encode($shippingFeeAmountMap); ?>;

			function calc() {
				var shopId = shop ? shop.value : 'free';
				if (shopId === 'free') {
					p.value = '';
					p.disabled = true;
					p.required = false;
					out.value = '';
					return;
				}
				p.disabled = false;
				p.required = true;
				var price = parseFloat(p.value || 0);
				if (price <= 0) {
					out.value = '';
					return;
				}
				var feePercent = parseFloat(feePercents[shopId] || 0);
				var defaultShippingFeeId = defaultShippingFeeIds[shopId] || '';
				var shippingCost = parseInt(shippingIncluded[shopId] || 0, 10) === 1 ? parseFloat(shippingFeeAmounts[defaultShippingFeeId] || 0) : 0;
				var feeAmount = price * (feePercent / 100);
				out.value = ((price - shippingCost - feeAmount - cost) / price * 100).toFixed(2);
			}
			if (p) {
				p.addEventListener('input', calc);
			}
			if (shop) {
				shop.addEventListener('change', calc);
			}
			calc();
		})();
	</script>
</div>
