<?php
$this->start('subMenu');
echo $this->element('shop_menu');
$this->end();
?>
<div class="main-area">
	<h2>ショップ一覧</h2>
	<table class="shop-insert-table">
		<tr>
			<th>ショップ名</th>
			<th>販売手数料</th>
			<th>送料無料ライン</th>
			<th>基本送料</th>
			<th>全品送料込み</th>
			<th>状態</th>
			<th>操作</th>
		</tr>
		<?php if (!empty($shops)): ?>
			<?php foreach ($shops as $shop): ?>
				<tr<?= empty($shop['Shop']['is_active']) ? ' style="background:#eee;color:#777;"' : ''; ?>>
					<td><?= h($shop['Shop']['shop_name']); ?></td>
					<td><?= h(number_format((float)$shop['Shop']['fee_percent'], 2)); ?> %</td>
					<td><?= $shop['Shop']['fee_shipping_threshold'] !== null ? h(number_format((float)$shop['Shop']['fee_shipping_threshold'])) . ' 円' : '-'; ?></td>
					<td><?= !empty($shop['ShippingFee']['shipping_fee_name']) ? h($shop['ShippingFee']['shipping_fee_name']) . ' (' . h(number_format((float)$shop['ShippingFee']['shipping_fee'])) . ' 円)' : '-'; ?></td>
					<td><?= !empty($shop['Shop']['is_shipping_included']) ? 'はい' : 'いいえ'; ?></td>
					<td><?= !empty($shop['Shop']['is_active']) ? '使用中' : '使用不可'; ?></td>
					<td>
						<?= $this->Html->link('編集', ['action' => 'edit', $shop['Shop']['shop_id']], ['class' => 'sbm-btn btn--blue']); ?>
						<?php if (!empty($shop['Shop']['is_active'])): ?>
							<?= $this->Form->postLink(
								'削除',
								['action' => 'deactivate', $shop['Shop']['shop_id']],
								['class' => 'sbm-btn btn--red', 'confirm' => 'DBからは削除せず、今後の登録で使用不可にします。よろしいですか？']
							); ?>
						<?php else: ?>
							<?= $this->Form->postLink(
								'再開',
								['action' => 'activate', $shop['Shop']['shop_id']],
								['class' => 'sbm-btn btn--orange', 'confirm' => 'このショップを再び使用可能にしますか？']
							); ?>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		<?php else: ?>
			<tr>
				<td colspan="7">登録済みショップはありません。</td>
			</tr>
		<?php endif; ?>
	</table>
	<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('error') ?></div>
</div>
