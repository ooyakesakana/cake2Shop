<h2>ショップ管理メニュー</h2>
<ul>
    <li>
        <?= $this->Html->link(
            'ショップ一覧',
            ['controller' => 'shops', 'action' => 'main'],
            ['class' => 'btn btn--orange'],
        ) ?>
    </li>
    <li>
        <?= $this->Html->link(
            'ショップ新規登録',
            ['controller' => 'shops', 'action' => 'add'],
            ['class' => 'btn btn--orange'],
        ) ?>
    </li>
    <li>
        <?= $this->Html->link(
            '送料設定追加',
            ['controller' => 'shops', 'action' => 'shipping_fee'],
            ['class' => 'btn btn--orange'],
        ) ?>
    </li>
</ul>