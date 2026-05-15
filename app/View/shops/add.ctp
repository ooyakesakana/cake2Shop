<?php

$this->start('subMenu');
echo $this->element('shop_menu');
$this->end();

echo $this->Form->create('Shop');
?>
<table class="shop-insert-table">
	<tr>
		<th>ショッププラットフォーム名</th>
		<th>ショップ管理コード</th>
		<th>販売手数料</th>
		<th>送料無料ライン</th>
		<th>基本送料</th>
	</tr>
	<tr>
		<td><?= $this->Form->input('shop_name', ['type' => 'text', 'label' => false, 'error' => false]); ?><?= $this->Form->error('shop_name', null, ['class' => 'error-text']); ?></td>
		<td><?= $this->Form->input('platform_code', ['type' => 'text', 'label' => false, 'error' => false]); ?><?= $this->Form->error('platform_code', null, ['class' => 'error-text']); ?></td>
		<td><?= $this->Form->input('fee_percent', ['type' => 'number', 'label' => false, 'error' => false, 'step' => '0.01', 'min' => '0']); ?></td>
		<td><?= $this->Form->input('free_shipping_threshold', ['type' => 'number', 'label' => false, 'error' => false]); ?></td>
		<td><?= $this->Form->input('default_shipping_fee', ['type' => 'select', 'label' => false, 'error' => false, 'options' => $shipping_fee, 'empty' => '送料を選択']); ?></td>
	</tr>
</table>
<button type="submit" class="btn btn--orange">登録</button>
<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('error') ?></div>
<?= $this->Form->end(); ?>