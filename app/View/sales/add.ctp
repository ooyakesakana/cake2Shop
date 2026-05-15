<?php $this->start('subMenu');
echo $this->element('sales_menu');
$this->end(); ?>
<h2>売上登録</h2>
<?= $this->Form->create('Sale'); ?>
<table class="shop-insert-table">
	<tr>
		<th>売れたプラットフォーム(必須)</th>
		<th>注文番号(任意)</th>
		<th>販売日</th>
		<th>送料</th>
	</tr>
	<tr>
		<td><?= $this->Form->input('shop_id', ['type' => 'select', 'label' => false, 'options' => $shops, 'empty' => '選択してください', 'required' => true, 'id' => 'shop-id']); ?></td>
		<td><?= $this->Form->input('order_no', ['label' => false]); ?></td>
		<td><?= $this->Form->input('sale_date', ['type' => 'date', 'label' => false, 'dateFormat' => 'YMD', 'empty' => false]); ?></td>
		<td><?= $this->Form->input('shipping_fee_id', ['type' => 'select', 'label' => false, 'options' => $shippingFees, 'empty' => '選択してください', 'id' => 'shipping-fee-id']); ?></td>
	</tr>
</table>

<h3>売れた商品（商品コード/商品名で検索して追加）</h3>
<input type="text" id="item-search" placeholder="商品コードor商品名で検索" />
<table class="shop-insert-table" id="sale-lines">
	<tr>
		<th>商品</th>
		<th>数量</th>
		<th>単価</th>
	</tr>
	<tr>
		<td><select name="data[SaleDetail][0][item_code]" class="item-select"><?php foreach ($items as $it) { ?><option value="<?= h($it['Item']['item_code']) ?>"><?= h($it['Item']['item_code'] . ' : ' . $it['Item']['item_name']) ?></option><?php } ?></select></td>
		<td><input type="number" name="data[SaleDetail][0][quantity]" min="1" /></td>
		<td><input type="number" name="data[SaleDetail][0][unit_price]" min="0" /></td>
	</tr>
</table>
<button type="button" class="sbm-btn btn--blue" id="add-line">行追加</button>
<?= $this->Form->submit('登録', ['class' => 'btn btn--orange']); ?>
<?= $this->Form->end(); ?>
<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>
<script>
	var shopShippingMap = <?= json_encode($shopShippingMap); ?>;
	document.getElementById('shop-id').addEventListener('change', function() {
		var val = this.value;
		var target = document.getElementById('shipping-fee-id');
		if (shopShippingMap[val]) {
			target.value = shopShippingMap[val];
		}
	});
	document.getElementById('add-line').addEventListener('click', function() {
		var table = document.getElementById('sale-lines');
		var idx = table.rows.length - 1;
		var row = table.insertRow(-1);
		row.innerHTML = table.rows[1].innerHTML.replace(/\[0\]/g, '[' + idx + ']');
	});
	document.getElementById('item-search').addEventListener('keyup', function() {
		var q = this.value.toLowerCase();
		document.querySelectorAll('.item-select').forEach(function(sel) {
			Array.prototype.forEach.call(sel.options, function(op) {
				op.hidden = q && op.text.toLowerCase().indexOf(q) === -1;
			});
		});
	});
</script>