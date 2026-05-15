<?php
    $this->Html->css('style', null, ['block' => 'css']);
?>
<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <title>サンプルフォーム</title>
        <?php
        echo $this->Html->css('style');
        echo $this->fetch('css');
        echo $this->fetch('script');
        ?>
    </head>
    <body>
        <div class="wrapper">
            <?php echo $this->fetch('content'); ?>
        </div>
    </body>
</html>