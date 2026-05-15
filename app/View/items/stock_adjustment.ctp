<?php $this->start('subMenu');
echo $this->element('item_menu');
$this->end(); ?>
<div class="main-area">
	<h2>在庫調整（初期不良/返金対応）</h2>
	<?= $this->Form->create('InventoryAdjustment'); ?>
	<table>
		<tr>
			<th>商品</th>
			<th>減算数量</th>
			<th>減算元ショップ(未登録在庫を使うなら空欄)</th>
			<th>再送料</th>
			<th>理由</th>
			<th>メモ</th>
		</tr>
		<tr>
			<td><?= $this->Form->input('item_code', ['type' => 'select', 'options' => $items, 'label' => false, 'default' => $selectedItemCode]); ?></td>
			<td><?= $this->Form->input('adjust_qty', ['type' => 'number', 'step' => '0.01', 'label' => false]); ?></td>
			<td><?= $this->Form->input('shop_id', ['type' => 'select', 'options' => $shops, 'empty' => '未登録在庫から減算', 'label' => false]); ?></td>
			<td><?= $this->Form->input('shipping_loss', ['type' => 'number', 'step' => '0.01', 'label' => false, 'value' => 0]); ?></td>
			<td><?= $this->Form->input('reason', ['label' => false, 'value' => '初期不良/返金']); ?></td>
			<td><?= $this->Form->input('memo', ['label' => false]); ?></td>
		</tr>
	</table>
	<br>
	<?= $this->Form->submit('在庫減算と費用計上', ['class' => 'btn btn--orange']); ?><?= $this->Form->end(); ?>
	<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>

	<?php if (!empty($shopStockList)): ?>
		<h3>選択商品のショップ在庫一覧</h3>
		<table>
			<tr>
				<th>ショップ</th>
				<th>在庫数</th>
			</tr><?php foreach ($shopStockList as $r): ?><tr>
					<td><?= h($r['Shop']['shop_name']) ?></td>
					<td><?= h($r['ShopInventory']['stock_quantity']) ?></td>
				</tr><?php endforeach; ?>
		</table>
	<?php endif; ?>
</div>