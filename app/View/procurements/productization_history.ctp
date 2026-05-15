<?php $this->start('subMenu');
echo $this->element('procurements_menu');
$this->end(); ?>
<div class="main-area">
	<h2>商品化履歴</h2>
	<table>
		<tr>
			<th>ID</th>
			<th>仕入ID</th>
			<th>商品コード</th>
			<th>完成数</th>
			<th>1個原価</th>
			<th>在庫反映済み</th>
		</tr><?php foreach ($rows as $r): ?><tr>
				<td><?= h($r['Productization']['id']) ?></td>
				<td><?= h($r['Productization']['purchase_id']) ?></td>
				<td><?= h($r['Productization']['item_code']) ?></td>
				<td><?= h($r['Productization']['completed_qty']) ?></td>
				<td><?= h($r['Productization']['unit_cost']) ?></td>
				<td><?= h($r['Productization']['inventory_reflected']) ?></td>
			</tr><?php endforeach; ?>
	</table>
</div>