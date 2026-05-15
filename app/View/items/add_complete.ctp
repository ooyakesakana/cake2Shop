<?php
$this->start('subMenu');
echo $this->element('item_menu');
$this->end();
?>
<div class="main-area">
	<h2>商品登録が完了しました</h2>
	<p>商品コード: <?= h($item['Item']['item_code']); ?> / 商品名: <?= h($item['Item']['item_name']); ?></p>
	<p>続けて各ショップの販売価格と在庫を登録できます。</p>

	<?= $this->Form->create(false); ?>
	<table class="shop-insert-table">
		<tr>
			<th>ショップ</th>
			<th>販売価格</th>
			<th>粗利率(自動表示)</th>
			<th>在庫数</th>
			<th>登録</th>
		</tr>
		<tr>
			<td><?= $this->Form->input('ShopEntry.shop_id', ['type' => 'select', 'options' => Hash::combine($shops, '{n}.Shop.shop_id', '{n}.Shop.shop_name'), 'label' => false, 'empty' => '選択']); ?></td>
			<td><?= $this->Form->input('ShopEntry.sale_price', ['type' => 'number', 'step' => '0.01', 'label' => false, 'id' => 'sale_price']); ?></td>
			<td><input type="text" id="margin_rate" readonly></td>
			<td><?= $this->Form->input('ShopEntry.stock_quantity', ['type' => 'number', 'step' => '0.01', 'label' => false]); ?></td>
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
			<th>ショップ名</th>
			<th>現在在庫</th>
			<th>販売価格</th>
			<th>粗利率</th>
		</tr>
		<?php foreach ($shops as $shop): $sid = $shop['Shop']['shop_id']; ?>
			<tr>
				<td><?= h($shop['Shop']['shop_name']); ?></td>
				<td><?= isset($invMap[$sid]) ? h($invMap[$sid]) : 0; ?></td>
				<td><?= isset($priceMap[$sid]['sale_price']) ? h($priceMap[$sid]['sale_price']) : '-'; ?></td>
				<td><?= isset($priceMap[$sid]['margin_rate']) ? h(round($priceMap[$sid]['margin_rate'], 2)) . '%' : '-'; ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>

	<script>
		(function() {
			var p = document.getElementById('sale_price');
			var out = document.getElementById('margin_rate');
			var cost = <?= json_encode((float)($item['Item']['base_price'] ?? 0)); ?>;

			function calc() {
				var price = parseFloat(p.value || 0);
				if (price <= 0) {
					out.value = '';
					return;
				}
				out.value = ((price - cost) / price * 100).toFixed(2) + '%';
			}
			if (p) {
				p.addEventListener('input', calc);
			}
		})();
	</script>
</div>