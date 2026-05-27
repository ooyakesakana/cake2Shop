<h2>仕入れ管理メニュー</h2>
<ul>
	<li><?= $this->Html->link('仕入一覧', ['controller' => 'procurements', 'action' => 'purchases'], ['class' => 'btn btn--pink']) ?></li>
	<li><?= $this->Html->link('仕入登録', ['controller' => 'procurements', 'action' => 'add_purchase'], ['class' => 'btn btn--pink']) ?></li>
	<li><?= $this->Html->link('商品化履歴', ['controller' => 'procurements', 'action' => 'productization_history'], ['class' => 'btn btn--pink']) ?></li>
	<li><?= $this->Html->link('在庫ロット一覧', ['controller' => 'procurements', 'action' => 'inventory_lots'], ['class' => 'btn btn--pink']) ?></li>
	<li><?= $this->Html->link('仮商品情報', ['controller' => 'procurements', 'action' => 'provisional_items'], ['class' => 'btn btn--pink']) ?></li>
	<li><?= $this->Html->link('期間集計', ['controller' => 'procurements', 'action' => 'report'], ['class' => 'btn btn--pink']) ?></li>
</ul>
