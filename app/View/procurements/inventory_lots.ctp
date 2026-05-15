<?php $this->start('subMenu');
echo $this->element('procurements_menu');
$this->end(); ?>
<div class="main-area">
	<h2>在庫ロット一覧</h2>
	<table>
		<tr>
			<th>ロットID</th>
			<th>商品コード</th>
			<th>仕入ID</th>
			<th>商品化ID</th>
			<th>数量</th>
			<th>残数量</th>
			<th>1個原価</th>
		</tr><?php foreach ($rows as $r): ?><tr>
				<td><?= h($r['InventoryLot']['id']) ?></td>
				<td><?= h($r['InventoryLot']['item_code']) ?></td>
				<td><?= h($r['InventoryLot']['purchase_id']) ?></td>
				<td><?= h($r['InventoryLot']['productization_id']) ?></td>
				<td><?= h($r['InventoryLot']['quantity']) ?></td>
				<td><?= h($r['InventoryLot']['remaining_qty']) ?></td>
				<td><?= h($r['InventoryLot']['unit_cost']) ?></td>
			</tr><?php endforeach; ?>
	</table>
</div>