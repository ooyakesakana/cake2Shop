<?php
$this->start('subMenu');
echo $this->element('item_menu');
$this->end();
?>
<h2>商品編集</h2>
<?= $this->Form->create('Item'); ?>
<table class="item-insert-table">
	<tr>
		<th>商品コード</th>
		<th class="item-name-column">商品名</th>
		<th>サイズ</th>
		<th>カテゴリ</th>
		<th>原価</th>
		<th>メモ</th>
	</tr>
	<tr>
		<td><?= $this->Form->input('item_code', ['label' => false]); ?></td>
		<td class="item-name-cell"><?= $this->Form->input('item_name', ['label' => false, 'class' => 'item-name-input']); ?></td>
		<td><?= $this->Form->input('item_size', ['label' => false]); ?></td>
		<td><?= $this->Form->input('category', ['label' => false]); ?></td>
		<td><?= $this->Form->input('base_price', ['label' => false, 'after' => ' 円']); ?></td>
		<td><?= $this->Form->input('memo', ['type' => 'textarea', 'label' => false]); ?></td>
	</tr>
</table>
<?= $this->Form->submit('更新', ['class' => 'btn btn--orange']); ?>
<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>
<?= $this->Form->end(); ?>
