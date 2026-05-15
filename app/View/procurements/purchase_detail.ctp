<?php $this->start('subMenu');
echo $this->element('procurements_menu');
$this->end(); ?>
<h2>仕入詳細 #<?= h($purchase['Purchase']['id']) ?></h2>
<?= $this->Form->create('PurchaseDetail'); ?><table>
	<tr>
		<th>商品コード/パーツコード</th>
		<th>名称</th>
		<th>数量</th>
		<th>単価</th>
		<th>メモ</th>
		<th>追加</th>
	</tr>
	<tr>
		<td><?= $this->Form->input('part_code', ['label' => false]); ?></td>
		<td><?= $this->Form->input('name', ['label' => false]); ?></td>
		<td><?= $this->Form->input('quantity', ['label' => false]); ?></td>
		<td><?= $this->Form->input('unit_price', ['label' => false]); ?></td>
		<td><?= $this->Form->input('memo', ['label' => false]); ?></td>
		<td><?= $this->Form->submit('追加', ['class' => 'sbm-btn btn--orange']); ?></td>
	</tr>
</table><?= $this->Form->end(); ?>
<table>
	<tr>
		<th>ID</th>
		<th>コード</th>
		<th>名称</th>
		<th>数量</th>
		<th>単価</th>
		<th>小計</th>
	</tr><?php foreach ($details as $d): ?><tr>
			<td><?= h($d['PurchaseDetail']['id']) ?></td>
			<td><?= h($d['PurchaseDetail']['part_code']) ?></td>
			<td><?= h($d['PurchaseDetail']['name']) ?></td>
			<td><?= h($d['PurchaseDetail']['quantity']) ?></td>
			<td><?= h($d['PurchaseDetail']['unit_price']) ?></td>
			<td><?= h($d['PurchaseDetail']['line_subtotal']) ?></td>
		</tr><?php endforeach; ?>
</table>