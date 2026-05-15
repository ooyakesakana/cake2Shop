<?php $this->start('subMenu');
echo $this->element('procurements_menu');
$this->end(); ?>
<h2>仕入登録</h2>
<?= $this->Form->create('Purchase'); ?>
<table class="shop-insert-table">
	<tr>
		<th>仕入れ日(空なら当日)</th>
		<th>仕入先名</th>
		<th>注文番号</th>
		<th>送料1</th>
		<th>送料2</th>
		<th>関税</th>
		<th>仮仕入れ</th>
	</tr>
	<tr>
		<td><?= $this->Form->input('purchase_date', ['type' => 'text', 'label' => false, 'placeholder' => 'YYYY-MM-DD']); ?></td>
		<td><?= $this->Form->input('supplier_name', ['label' => false]); ?></td>
		<td><?= $this->Form->input('purchase_no', ['label' => false]); ?></td>
		<td><?= $this->Form->input('item_amount', ['label' => false, 'value' => '0']); ?></td>
		<td><?= $this->Form->input('intl_shipping', ['label' => false, 'value' => '0']); ?></td>
		<td><?= $this->Form->input('customs_duty', ['label' => false, 'value' => '0']); ?></td>
		<td><?= $this->Form->checkbox('is_temporary'); ?></td>
	</tr>
	<tr>
		<th colspan="7">メモ / 仕入先URL</th>
	</tr>
	<tr>
		<td colspan="7"><?= $this->Form->input('memo', ['type' => 'textarea', 'label' => false, 'rows' => 2]); ?><?= $this->Form->input('supplier_url', ['label' => false, 'placeholder' => 'https://...']); ?></td>
	</tr>
</table>

<h3>仕入れ明細</h3>
<table class="shop-insert-table" id="detail-table">
	<tr>
		<th>仕入れ元商品名*</th>
		<th>単価*</th>
		<th>数量*</th>
		<th>販売商品コード</th>
		<th>備品</th>
		<th>メモ</th>
	</tr>
	<?php for ($i = 0; $i < 3; $i++): ?>
		<tr>
			<td><?= $this->Form->input("PurchaseDetail.$i.source_item_name", ['label' => false]); ?></td>
			<td><?= $this->Form->input("PurchaseDetail.$i.unit_price", ['label' => false, 'type' => 'number', 'step' => '0.01', 'min' => '0']); ?></td>
			<td><?= $this->Form->input("PurchaseDetail.$i.quantity", ['label' => false, 'type' => 'number', 'step' => '1', 'min' => '0']); ?></td>
			<td><?= $this->Form->input("PurchaseDetail.$i.item_code", ['label' => false]); ?></td>
			<td><?= $this->Form->checkbox("PurchaseDetail.$i.is_supply"); ?></td>
			<td><?= $this->Form->input("PurchaseDetail.$i.memo", ['label' => false]); ?></td>
		</tr>
	<?php endfor; ?>
</table>
<button type="button" class="btn btn--blue" onclick="addDetailRow()">明細行を追加</button>
<br><br>
<?= $this->Form->submit('仕入れを保存', ['class' => 'btn btn--orange']); ?>
<?= $this->Form->end(); ?>

<script>
	let rowIndex = 3;

	function addDetailRow() {
		const table = document.getElementById('detail-table');
		const tr = document.createElement('tr');
		tr.innerHTML = `
<td><input name="data[PurchaseDetail][${rowIndex}][source_item_name]" type="text"></td>
<td><input name="data[PurchaseDetail][${rowIndex}][unit_price]" type="number" step="0.01" min="0"></td>
<td><input name="data[PurchaseDetail][${rowIndex}][quantity]" type="number" step="1" min="0"></td>
<td><input name="data[PurchaseDetail][${rowIndex}][item_code]" type="text"></td>
<td><input name="data[PurchaseDetail][${rowIndex}][is_supply]" type="checkbox" value="1"></td>
<td><input name="data[PurchaseDetail][${rowIndex}][memo]" type="text"></td>`;
		table.appendChild(tr);
		rowIndex++;
	}
</script>