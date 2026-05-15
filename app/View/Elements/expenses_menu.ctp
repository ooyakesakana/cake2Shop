<h2>経費管理メニュー</h2>
<ul>
    <li>
        <?= $this->Html->link(
            '経費リスト',
            ['controller' => 'expenses', 'action' => 'main'],
            ['class' => 'btn btn--green'],
        ) ?>
    </li>
    <li>
        <?= $this->Html->link(
            '経費登録',
            ['controller' => 'expenses', 'action' => 'add'],
            ['class' => 'btn btn--green'],
        ) ?>
    </li>
</ul>