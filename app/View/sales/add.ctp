<?php $this->start('subMenu');
echo $this->element('sales_menu');
$this->end(); ?>
<div class="main-area">
<h2>売上登録</h2>
<?= $this->Form->create('Sale'); ?>
<table class="shop-insert-table">
	<tr>
		<th>売れたプラットフォーム(必須)</th>
		<th>注文番号(任意)</th>
		<th>販売日</th>
		<th>顧客選択の配送方法</th>
		<th>実際に使用した配送方法</th>
	</tr>
	<tr>
		<td><?= $this->Form->input('shop_id', ['type' => 'select', 'label' => false, 'options' => $shops, 'empty' => '選択してください', 'required' => true, 'id' => 'shop-id']); ?></td>
		<td><?= $this->Form->input('order_no', ['label' => false]); ?></td>
		<td>
			<?= $this->Form->input('sale_date', ['type' => 'date', 'label' => false, 'dateFormat' => 'YMD', 'empty' => false]); ?>
			<input type="date" id="sale-date-picker">
		</td>
		<td><?= $this->Form->input('shipping_fee_id', ['type' => 'select', 'label' => false, 'options' => $shippingFees, 'empty' => '選択してください', 'id' => 'shipping-fee-id']); ?></td>
		<td><?= $this->Form->input('actual_shipping_fee_id', ['type' => 'select', 'label' => false, 'options' => $shippingFees, 'empty' => '選択してください', 'id' => 'actual-shipping-fee-id']); ?></td>
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
		<td><input type="number" name="data[SaleDetail][0][quantity]" min="1" /> 個</td>
		<td><input type="number" name="data[SaleDetail][0][unit_price]" min="0" /> 円</td>
	</tr>
</table>
<br>
<button type="button" class="sbm-btn btn--blue" id="add-line">行追加</button>
<br>
<?= $this->Form->submit('登録', ['class' => 'btn btn--orange']); ?>
<?= $this->Form->end(); ?>
<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>
<script>
	function pad2(value) {
		return ('0' + value).slice(-2);
	}
	function syncSaleDatePickerFromSelects() {
		var y = document.getElementById('SaleSaleDateYear');
		var m = document.getElementById('SaleSaleDateMonth');
		var d = document.getElementById('SaleSaleDateDay');
		var picker = document.getElementById('sale-date-picker');
		if (y && m && d && picker && y.value && m.value && d.value) {
			picker.value = y.value + '-' + pad2(m.value) + '-' + pad2(d.value);
		}
	}
	function syncSaleDateSelectsFromPicker() {
		var picker = document.getElementById('sale-date-picker');
		var y = document.getElementById('SaleSaleDateYear');
		var m = document.getElementById('SaleSaleDateMonth');
		var d = document.getElementById('SaleSaleDateDay');
		if (picker && y && m && d && picker.value) {
			var parts = picker.value.split('-');
			y.value = parts[0];
			m.value = parts[1];
			if (m.value !== parts[1]) {
				m.value = String(parseInt(parts[1], 10));
			}
			d.value = parts[2];
			if (d.value !== parts[2]) {
				d.value = String(parseInt(parts[2], 10));
			}
		}
	}
	syncSaleDatePickerFromSelects();
	['SaleSaleDateYear', 'SaleSaleDateMonth', 'SaleSaleDateDay'].forEach(function(id) {
		var select = document.getElementById(id);
		if (select) {
			select.addEventListener('change', syncSaleDatePickerFromSelects);
		}
	});
	document.getElementById('sale-date-picker').addEventListener('change', syncSaleDateSelectsFromPicker);

	var shopShippingMap = <?= json_encode($shopShippingMap); ?>;
	document.getElementById('shop-id').addEventListener('change', function() {
		var val = this.value;
		var customerTarget = document.getElementById('shipping-fee-id');
		var actualTarget = document.getElementById('actual-shipping-fee-id');
		if (shopShippingMap[val]) {
			customerTarget.value = shopShippingMap[val];
			actualTarget.value = shopShippingMap[val];
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
</div>