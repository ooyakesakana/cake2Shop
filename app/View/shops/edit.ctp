<?php
$this->start('subMenu');
echo $this->element('shop_menu');
$this->end();

echo $this->Form->create('Shop');
?>
<div class="main-area">
	<h2>ショップ編集</h2>
	<table class="shop-insert-table">
		<tr>
			<th>ショッププラットフォーム名</th>
			<th>販売手数料</th>
			<th>送料無料ライン</th>
			<th>基本送料</th>
			<th>全品送料込み</th>
			<th>状態</th>
		</tr>
		<tr>
			<td><?= $this->Form->input('shop_name', ['type' => 'text', 'label' => false, 'error' => false]); ?><?= $this->Form->error('shop_name', null, ['class' => 'error-text']); ?></td>
			<td><?= $this->Form->input('fee_percent', ['type' => 'number', 'label' => false, 'error' => false, 'step' => '0.01', 'min' => '0', 'after' => ' %']); ?></td>
			<td><?= $this->Form->input('fee_shipping_threshold', ['type' => 'number', 'label' => false, 'error' => false, 'after' => ' 円']); ?></td>
			<td><?= $this->Form->input('default_shipping_fee', ['type' => 'select', 'label' => false, 'error' => false, 'options' => $shipping_fee, 'empty' => '送料を選択']); ?></td>
			<td><?= $this->Form->input('is_shipping_included', ['type' => 'checkbox', 'label' => false, 'value' => 1]); ?></td>
			<td><?= $this->Form->input('is_active', ['type' => 'checkbox', 'label' => false, 'value' => 1]); ?> 使用中</td>
		</tr>
	</table>
	<?= $this->Form->submit('更新', ['class' => 'btn btn--orange']); ?>
	<?= $this->Html->link('一覧へ戻る', ['action' => 'main'], ['class' => 'btn btn--blue']); ?>
	<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('error') ?></div>
</div>
<?= $this->Form->end(); ?>
