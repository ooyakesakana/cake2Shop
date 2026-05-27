<?php $this->start('subMenu');
echo $this->element('sales_menu');
$this->end(); ?>
<div class="main-area">
<h2>売上編集</h2>
<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>

<table class="shop-insert-table">
	<tr>
		<th>販売日</th>
		<th>ショップ</th>
		<th>注文番号</th>
		<th>商品合計</th>
		<th>顧客送料</th>
	</tr>
	<tr>
		<td><?= h($sale['Sale']['sale_date']); ?></td>
		<td><?= h($sale['Shop']['shop_name'] ?? '-'); ?></td>
		<td><?= h($sale['Sale']['order_no'] ?? '-'); ?></td>
		<td><?= number_format((float)$sale['Sale']['subtotal']); ?>円</td>
		<td><?= number_format((float)$sale['Sale']['actual_shipping']); ?>円</td>
	</tr>
</table>

<h3>明細</h3>
<table class="shop-insert-table">
	<tr>
		<th>商品</th>
		<th>数量</th>
		<th>単価</th>
		<th>小計</th>
	</tr>
	<?php foreach ((array)$sale['SaleDetail'] as $detail): ?>
	<tr>
		<td><?= h($detail['Item']['item_name'] ?? $detail['item_code']); ?></td>
		<td><?= h($detail['quantity']); ?></td>
		<td><?= number_format((float)$detail['unit_price']); ?>円</td>
		<td><?= number_format((float)$detail['line_amount']); ?>円</td>
	</tr>
	<?php endforeach; ?>
</table>

<h3>実送料の確定</h3>
<?= $this->Form->create('Sale'); ?>
<table class="shop-insert-table">
	<tr>
		<th>実際に使用した配送方法</th>
		<th>郵便局持ち込み</th>
	</tr>
	<tr>
		<td><?= $this->Form->input('actual_shipping_fee_id', ['type' => 'select', 'label' => false, 'options' => $shippingFees, 'empty' => '選択してください', 'id' => 'edit-actual-shipping-fee-id']); ?></td>
		<td><?= $this->Form->input('shipping_cost_pending', ['type' => 'checkbox', 'label' => '持ち込み（送料未確定）のままにする', 'id' => 'edit-shipping-cost-pending']); ?></td>
	</tr>
</table>
<br>
<?= $this->Form->submit('確定', ['class' => 'btn btn--orange']); ?>
<?= $this->Form->end(); ?>
</div>

<script>
(function () {
	var pending = document.getElementById('edit-shipping-cost-pending');
	var actual = document.getElementById('edit-actual-shipping-fee-id');
	function syncState() {
		if (!pending || !actual) {
			return;
		}
		actual.disabled = pending.checked;
		if (pending.checked) {
			actual.value = '';
		}
	}
	if (pending) {
		pending.addEventListener('change', syncState);
		syncState();
	}
})();
</script>
