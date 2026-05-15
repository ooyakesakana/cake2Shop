<?php $this->start('subMenu');
echo $this->element('procurements_menu');
$this->end(); ?>
<div class="main-area">
	<h2>仕入一覧</h2>
	<p><?= $this->Html->link('仕入登録', ['action' => 'add_purchase'], ['class' => 'btn btn--orange']) ?></p>
	<table>
		<tr>
			<th>ID</th>
			<th>仕入日</th>
			<th>仕入先</th>
			<th>URL</th>
			<th>通貨</th>
			<th>合計仕入原価</th>
			<th>詳細</th>
			<th>商品化</th>
		</tr>
		<?php foreach ($purchases as $p): ?>
			<tr>
				<td><?= h($p['Purchase']['id']) ?></td>
				<td><?= h($p['Purchase']['purchase_date']) ?></td>
				<td><?= h($p['Purchase']['supplier_name']) ?></td>
				<td><?php if (!empty($p['Purchase']['supplier_url'])): ?><?= $this->Html->link('URL', h($p['Purchase']['supplier_url']), ['target' => '_blank']) ?><?php endif; ?></td>
				<td><?= h($p['Purchase']['currency_code']) ?></td>
				<td><?= number_format($p['Purchase']['total_purchase_cost']) ?></td>
				<td><?= $this->Html->link('仕入詳細', ['action' => 'purchase_detail', $p['Purchase']['id']]) ?></td>
				<td><?= $this->Html->link('商品化登録', ['action' => 'productize', $p['Purchase']['id']]) ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>