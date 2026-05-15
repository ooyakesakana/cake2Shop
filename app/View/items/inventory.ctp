<?php
$this->start('subMenu');
echo $this->element('item_menu');
$this->end();
echo $this->Form->create('ShopInventory');
?>
<div class="main-area">
	<table class="shop-insert-table">
		<tr>
			<th>ショップ</th>
			<th>商品</th>
			<th>在庫数</th>
			<th>登録</th>
		</tr>
		<tr>
			<td><?= $this->Form->input('shop_id', ['type' => 'select', 'options' => $shops, 'label' => false, 'empty' => '選択してください', 'default' => $selectedItemCode]); ?></td>
			<td><?= $this->Form->input('item_code', ['type' => 'select', 'options' => $items, 'label' => false, 'empty' => '選択してください', 'default' => $selectedItemCode]); ?></td>
			<td><?= $this->Form->input('stock_quantity', ['type' => 'number', 'label' => false]); ?></td>
			<td><?= $this->Form->submit('保存', ['class' => 'sbm-btn btn--orange']); ?></td>
		</tr>
	</table>
	<?= $this->Form->end(); ?>
	<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>

	<table class="shop-insert-table">
		<tr>
			<th>ショップ</th>
			<th>商品コード</th>
			<th>商品名</th>
			<th>在庫数</th>
		</tr>
		<?php foreach ($inventoryList as $inv): ?>
			<tr>
				<td><?= h($inv['Shop']['shop_name']); ?></td>
				<td><?= h($inv['Item']['item_code']); ?></td>
				<td><?= h($inv['Item']['item_name']); ?></td>
				<td><?= h($inv['ShopInventory']['stock_quantity']); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
</div>