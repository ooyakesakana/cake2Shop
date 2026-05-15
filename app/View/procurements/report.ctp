<?php $this->start('subMenu');
echo $this->element('procurements_menu');
$this->end(); ?>
<div class="main-area">
	<h2>期間集計</h2>
	<?= $this->Form->create(false, ['type' => 'get']); ?>
	開始<?= $this->Form->input('from', ['type' => 'date', 'label' => false, 'value' => $from]); ?>終了<?= $this->Form->input('to', ['type' => 'date', 'label' => false, 'value' => $to]); ?><?= $this->Form->submit('集計', ['class' => 'sbm-btn btn--blue']); ?><?= $this->Form->end(); ?>
	<p>売上合計: <?= number_format($totalSales, 2) ?></p>
	<p>仕入合計: <?= number_format($totalPurchase, 2) ?></p>
	<p>粗ベース差額: <?= number_format($totalSales - $totalPurchase, 2) ?></p>
</div>