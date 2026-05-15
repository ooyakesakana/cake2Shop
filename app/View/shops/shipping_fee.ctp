<?php
$this->start('subMenu');
echo $this->element('shop_menu');
$this->end();

echo $this->Form->create('ShippingFee', []);
?>
<div style="display:block;">
	<div class="shipping-fee-wrapper">
		<div class="registration">
			<table class="shop-insert-table">
				<tr>
					<th>配送方法名</th>
					<th>送料</th>
					<th>追加</th>
				</tr>
				<tr>
					<td><?= $this->Form->input('shipping_fee_name', [
							'label' => false,
							'class' => 'shipping_fee_name',
							'id' => 'shipping_fee_name',
							'placeholder' => '(例)普通郵便 140円',
							'error' => false,
						]) ?>
						<?= $this->Form->error('shipping_fee_name', null, ['class' => 'error-text']); ?></td>
					<td><?= $this->Form->input('shipping_fee', [
							'label' => false,
							'class' => 'shipping_fee',
							'id' => 'shipping_fee',
							'placeholder' => '(例)140',
							'error' => false,
							'after' => '円',
						]) ?>
						<?= $this->Form->error('shipping_fee', null, ['class' => 'error-text']); ?></td>
					<td>
						<?= $this->Form->submit('登録', [
							'class' => 'sbm-btn btn--orange',
							'name' => 'registration',
						]) ?>
					</td>
				</tr>
			</table>
			<?= $this->Form->end(); ?>
		</div>
		<br><br>

		<div class="change-sipping-fee">
			<table class="shop-insert-table">
				<tr>
					<th colspan="3">登録済み送料設定一覧</th>
				</tr>
				<tr>
					<th>配送方法名</th>
					<th>送料</th>
					<th>変更/削除</th>
				</tr>
				<?php foreach ($shipping_fee_list as $list): ?>
					<tr>
						<td><?= h($list['ShippingFee']['shipping_fee_name']) ?></td>
						<td><?= h($list['ShippingFee']['shipping_fee']) ?> 円</td>
						<td><?= $this->Html->link(
								'変更',
								[
									'action' => 'shipping_fee',
									'?' => ['edit_id' => $list['ShippingFee']['id']],
								],
								['class' => 'sbm-btn btn--blue']
							) ?>
							<?= $this->Form->postLink(
								'削除',
								['action' => 'delete_shipping_fee', $list['ShippingFee']['id']],
								[
									'class' => 'sbm-btn btn--red',
									'confirm' => '削除してもよろしいですか？'
								]
							) ?>
					</tr>
					<?php if ($edit_id === (int)$list['ShippingFee']['id']): ?>
						<tr>
							<td colspan="3">
								<?= $this->Form->create('ShippingFee', [
									'style' => 'display:flex; align-items:center; justify-content:space-evenly; width100%;'
								]) ?>
								<?= $this->Form->hidden('id', ['value' => $list['ShippingFee']['id']]) ?>
								<?= $this->Form->input('shipping_fee_name', [
									'label' => '配送方法名　',
									'value' => $list['ShippingFee']['shipping_fee_name']
								]) ?>
								<?= $this->Form->input('shipping_fee', [
									'label' => '送料　',
									'value' => $list['ShippingFee']['shipping_fee'],
									'after' => '円'
								]) ?>
								<?= $this->Form->submit('確定', ['name' => 'update', 'class' => 'sbm-btn btn--orange']) ?>
								<?= $this->Form->end() ?>
							</td>
						</tr>
					<?php endif; ?>
				<?php endforeach; ?>
			</table>
		</div>
	</div><br><br>
	<div class="flashMsg "><?= $this->Session->flash('success') ?><?= $this->Session->flash('error') ?><?= $this->Session->flash('delete_msg') ?></div>
</div>