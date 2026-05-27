<?php $this->start('subMenu');
echo $this->element('sales_menu');
$this->end(); ?>
<div class="main-area">
<h2>売上一覧</h2>
<p>
	<?= $this->Html->link('全件表示', ['action' => 'index'], ['class' => 'sbm-btn btn--blue']); ?>
	<?= $this->Html->link('仮登録のみ', ['action' => 'index', '?' => ['status' => 'provisional']], ['class' => 'sbm-btn btn--pink']); ?>
</p>
<?php if (empty($hasSaleStatus)): ?>
	<p>仮登録管理を使うには sales テーブルに status / shipping_cost_pending 列を追加してください。</p>
<?php endif; ?>
<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>
<table class="shop-insert-table">
	<tr>
		<th>状態</th>
		<th>販売日</th>
		<th>ショップ</th>
		<th>注文番号</th>
		<th>商品</th>
		<th>商品合計</th>
		<th>顧客送料</th>
		<th>実送料</th>
		<th>手数料</th>
		<th>利益</th>
		<th>操作</th>
	</tr>
	<?php if (empty($sales)): ?>
	<tr>
		<td colspan="11">売上データはありません。</td>
	</tr>
	<?php endif; ?>
	<?php foreach ($sales as $sale): ?>
	<?php
		$isProvisional = isset($sale['Sale']['status']) && $sale['Sale']['status'] === 'provisional';
		$items = [];
		foreach ((array)$sale['SaleDetail'] as $detail) {
			$name = isset($detail['Item']['item_name']) ? $detail['Item']['item_name'] : $detail['item_code'];
			$items[] = $name . ' x ' . (float)$detail['quantity'];
		}
	?>
	<tr>
		<td><?= $isProvisional ? '仮登録' : '確定'; ?></td>
		<td><?= h($sale['Sale']['sale_date']); ?></td>
		<td><?= h($sale['Shop']['shop_name'] ?? '-'); ?></td>
		<td><?= h($sale['Sale']['order_no'] ?? '-'); ?></td>
		<td><?= h(implode(' / ', $items)); ?></td>
		<td><?= number_format((float)$sale['Sale']['subtotal']); ?>円</td>
		<td><?= number_format((float)$sale['Sale']['actual_shipping']); ?>円</td>
		<td><?= $isProvisional ? '未確定' : number_format((float)$sale['Sale']['actual_shipping_cost']) . '円'; ?></td>
		<td><?= number_format((float)$sale['Sale']['fee_amount']); ?>円</td>
		<td><?= $isProvisional ? '-' : number_format((float)$sale['Sale']['net_sales']) . '円'; ?></td>
		<td><?= $this->Html->link($isProvisional ? '送料確定' : '編集', ['action' => 'edit', $sale['Sale']['id']], ['class' => 'sbm-btn btn--orange']); ?></td>
	</tr>
	<?php endforeach; ?>
</table>
</div>
