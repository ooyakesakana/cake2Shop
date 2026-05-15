<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>在庫管理システム | <?php echo $titleForLayout; ?></title>
    <?php
    echo $this->Html->css('style');
    echo $this->fetch('css');
    echo $this->fetch('script');
    ?>

</head>

<body>
    <div class="l-reverse">
        <nav class="l-reverse__nav">
            <?php echo $this->fetch('subMenu'); ?>
        </nav>
        <div class="l-reverse__body">
            <nav class="l-reverse__localNav">
                <h2><?= $this->Html->link(
                        'メインメニュー',
                        ['controller' => 'dashboard', 'action' => 'index']
                    ) ?>
                </h2>
                <ul>
                    <li><?= $this->Html->link(
                            '商品管理',
                            ['controller' => 'items', 'action' => 'index'],
                            ['class' => 'btn btn--red']
                        ) ?></li>
                    <li><?= $this->Html->link(
                            '仕入れ管理',
                            ['controller' => 'procurements', 'action' => 'index'],
                            ['class' => 'btn btn--pink']
                        ) ?></li>
                    <li><?= $this->Html->link(
                            '売上管理',
                            ['controller' => 'sales', 'action' => 'index'],
                            ['class' => 'btn btn--blue']
                        ) ?></li>
                    <li><?= $this->Html->link(
                            'ショップ管理',
                            ['controller' => 'shops', 'action' => 'index'],
                            ['class' => 'btn btn--orange']
                        ) ?></li>
                    <li><?= $this->Html->link(
                            '経費管理',
                            ['controller' => 'expenses', 'action' => 'index'],
                            ['class' => 'btn btn--green']
                        ) ?></li>
                    <li><?= $this->Form->postLink(
                            'ログアウト',
                            ['controller' => 'auth', 'action' => 'loguout'],
                            [
                                'class' => 'btn btn--purple',
                                'confirm' => 'ログアウトしてよろしいですか？',
                            ]
                        ) ?></li>
                    <br>
                </ul>
            </nav>
            <div class="c-box l-reverse__content">
                <?php echo $this->fetch('content'); ?>
            </div>
        </div>
    </div>
</body>

</html>