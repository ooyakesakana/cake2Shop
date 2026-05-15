<?php $this->start('subMenu');
echo $this->element('procurements_menu');
$this->end(); ?>
<h2>仮商品情報</h2>
<?= $this->Form->create('ProvisionalItem'); ?>
<table>
	<tr>
		<th>仮コード</th>
		<th>名称</th>
		<th>数量</th>
		<th>単価</th>
		<th>メモ</th>
		<th>登録</th>
	</tr>
	<tr>
		<td><?= $this->Form->input('provisional_code', ['label' => false]); ?></td>
		<td><?= $this->Form->input('name', ['label' => false]); ?></td>
		<td><?= $this->Form->input('quantity', ['label' => false]); ?></td>
		<td><?= $this->Form->input('unit_price', ['label' => false]); ?></td>
		<td><?= $this->Form->input('memo', ['label' => false]); ?></td>
		<td><?= $this->Form->submit('登録', ['class' => 'sbm-btn btn--orange']); ?></td>
	</tr>
</table><?= $this->Form->end(); ?>
<table>
	<tr>
		<th>ID</th>
		<th>仮コード</th>
		<th>名称</th>
		<th>数量</th>
		<th>単価</th>
	</tr><?php foreach ($rows as $r): ?><tr>
			<td><?= h($r['ProvisionalItem']['id']) ?></td>
			<td><?= h($r['ProvisionalItem']['provisional_code']) ?></td>
			<td><?= h($r['ProvisionalItem']['name']) ?></td>
			<td><?= h($r['ProvisionalItem']['quantity']) ?></td>
			<td><?= h($r['ProvisionalItem']['unit_price']) ?></td>
		</tr><?php endforeach; ?>
</table>