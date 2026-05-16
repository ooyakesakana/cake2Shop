<<<<<<< ours
<<<<<<< ours
<?php $this->start('subMenu');
echo $this->element('sales_menu');
$this->end(); ?>
<p><?= $this->Html->link('売上登録', ['action' => 'add'], ['class' => 'btn btn--orange']); ?></p>
<table class="shop-insert-table">
	<tr>
		<th>販売日</th>
		<th>ショップ</th>
		<th>小計</th>
		<th>送料</th>
		<th>手数料</th>
		<th>実売上</th>
		<th>納品書</th>
	</tr>
	<?php foreach ($sales as $sale): ?>
		<tr>
			<td><?= h($sale['Sale']['sale_date']); ?></td>
			<td><?= h($sale['Shop']['shop_name']); ?></td>
			<td><?= number_format($sale['Sale']['subtotal']); ?></td>
			<td><?= number_format($sale['Sale']['actual_shipping']); ?></td>
			<td><?= number_format($sale['Sale']['fee_amount']); ?></td>
			<td><?= number_format($sale['Sale']['net_sales']); ?></td>
			<td><?= $this->Html->link('PDF', ['action' => 'invoice_pdf', $sale['Sale']['id']], ['class' => 'sbm-btn btn--blue']); ?></td>
		</tr>
	<?php endforeach; ?>
</table>
=======
=======
>>>>>>> theirs
<?php
// 売上履歴メニュー（/sales/main）からの表示互換用
// 実体は index と同じ一覧を表示
include dirname(__FILE__) . DS . 'index.ctp';
<<<<<<< ours
>>>>>>> theirs
=======
>>>>>>> theirs
