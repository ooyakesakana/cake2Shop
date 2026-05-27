<h2>商品管理メニュー</h2>
<ul>
    <li><?= $this->Html->link('商品検索', ['controller' => 'items', 'action' => 'main'], ['class' => 'btn btn--red']) ?></li>
    <li><?= $this->Html->link('商品登録', ['controller' => 'items', 'action' => 'add'], ['class' => 'btn btn--red']) ?></li>
    <li><?= $this->Html->link('在庫調整', ['controller' => 'items', 'action' => 'stock_adjustment'], ['class' => 'btn btn--red']) ?></li>
</ul>
