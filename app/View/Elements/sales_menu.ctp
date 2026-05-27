<h2>売上管理メニュー</h2>
<ul>
    <li>
        <?= $this->Html->link(
            '売上履歴',
            ['controller' => 'sales', 'action' => 'index'],
            ['class' => 'btn btn--blue'],
        ) ?>
    </li>
    <li>
        <?= $this->Html->link(
            '売上登録',
            ['controller' => 'sales', 'action' => 'add'],
            ['class' => 'btn btn--blue'],
        ) ?>
    </li>
</ul>
