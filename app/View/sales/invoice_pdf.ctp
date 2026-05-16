<h2>納品書</h2>
<p>注文番号: <?= h($sale['Sale']['order_no']); ?></p>
<p>販売日: <?= h($sale['Sale']['sale_date']); ?> / プラットフォーム: <?= h($sale['Shop']['shop_name']); ?></p>
<table border="1" cellpadding="6" cellspacing="0" width="100%">
	<tr>
		<th>商品コード</th>
		<th>商品名</th>
		<th>数量</th>
		<th>単価</th>
		<th>金額</th>
	</tr>
	<?php foreach ($sale['SaleDetail'] as $d): ?>
		<tr>
			<td><?= h($d['item_code']); ?></td>
			<td><?= h($d['Item']['item_name']); ?></td>
			<td><?= h($d['quantity']); ?></td>
			<td><?= number_format($d['unit_price']); ?></td>
			<td><?= number_format($d['quantity'] * $d['unit_price']); ?></td>
		</tr>
	<?php endforeach; ?>
</table>
<p>小計: <?= number_format($subtotal); ?> 円</p>
<p>送料: <?= number_format($actualShipping); ?> 円</p>
<p><strong>合計: <?= number_format($total); ?> 円</strong></p>
