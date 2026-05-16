<?php $this->start('subMenu');
echo $this->element('procurements_menu');
$this->end(); ?>
<h2>商品化登録（仕入ID:<?= h($purchase['Purchase']['id']) ?>）</h2>
<p>配賦対象共通費（国際送料/関税等）: <?= number_format($nonItemCost, 2) ?></p>
<?= $this->Form->create('Productization'); ?>
<table>
	<tr>
		<th>配賦方式</th>
		<th>完成品商品コード</th>
		<th>完成数量</th>
		<th>配賦金額</th>
		<th>1個原価(手動可)</th>
		<th>メモ</th>
		<th>在庫反映</th>
	</tr>
	<tr>
		<td><?= $this->Form->input('allocation_method', ['type' => 'select', 'label' => false, 'options' => ['value_ratio' => '商品代金比率', 'qty_ratio' => '数量比'], 'default' => $allocationMethod]); ?></td>
		<td><?= $this->Form->input('item_code', ['type' => 'select', 'label' => false, 'options' => $items]); ?></td>
		<td><?= $this->Form->input('completed_qty', ['label' => false, 'after' => ' 個']); ?></td>
		<td><?= $this->Form->input('allocated_amount', ['label' => false, 'after' => ' 円']); ?></td>
		<td><?= $this->Form->input('unit_cost_manual', ['label' => false, 'after' => ' 円']); ?></td>
		<td><?= $this->Form->input('memo', ['label' => false]); ?></td>
		<td><?= $this->Form->input('inventory_reflect_now', ['type' => 'checkbox', 'label' => false, 'value' => 1]); ?></td>
	</tr>
</table>
<h3>使用した仕入明細（配賦プレビュー付き）</h3>
<table>
	<tr>
		<th>名称</th>
		<th>使用数</th>
		<th>不良数</th>
		<th>配賦額(初期値)</th>
	</tr>
	<?php foreach ($details as $i => $d): ?>
		<tr>
			<td><?= h($d['PurchaseDetail']['name']) ?>
				<?= $this->Form->input("ProductizationMaterial.$i.purchase_detail_id", ['type' => 'hidden', 'value' => $d['PurchaseDetail']['id']]); ?></td>
			<td><?= $this->Form->input("ProductizationMaterial.$i.used_qty", ['label' => false, 'after' => ' 個']); ?></td>
			<td><?= $this->Form->input("ProductizationMaterial.$i.defect_qty", ['label' => false, 'after' => ' 個']); ?></td>
			<td><?= $this->Form->input("ProductizationMaterial.$i.allocated_amount", ['label' => false, 'value' => $allocationPreview[$d['PurchaseDetail']['id']] ?? 0, 'after' => ' 円']); ?></td>
		</tr>
	<?php endforeach; ?>
</table>
<?= $this->Form->submit('登録', ['class' => 'btn btn--orange']); ?><?= $this->Form->end(); ?>
