<!-- ビューを使おう！ (1/3) -->
<!-- <html>
<head>
    <title>Index Page</title>
</head>
<body>
    <h1>Index Page</h1>
    <p>this is test View.</p>
</body>
</html> -->

<!-- ビューを使おう！ (2/3) -->
    <!-- <h1>Index Page</h1>
    <p>this is test View.</p> -->

<!-- ビューを使おう！ (3/3) -->
    <!-- <h1>Index Page</h1>
    <p>this is test View.</p>
    <p>message:<?= $msg ?></p>
    <p>  </p>
    <p>Data.</p>
    <div>
        <ul>
            <?php foreach($datas as $data): ?>
                <li><?= $data ?></li>
                <?php endforeach; ?>
        </ul>
    </div> -->

<!-- フォーム送信とForm Helper (1/5) -->
<!-- <h1>Index Page</h1>
<p>this is test View.</p>
<p>  </p>
<p>
    <form method="post" action="./form" name="form1">
        <div><input type="text" name="text1" id="text1"></div>
        <div><input type="checkbox" name="check1" id="check1">
        <label for="check1">check1</label></div>
        <div><input type="radio" value="radio_A" name="radio1" id="radio_a">
        <label for="radio_a">Radio A</label>
        <input type="radio" value="radio_B" name="radio1" id="radio_b">
        <label for="radio_b">Radio B</label></div>
        <div><input type="submit" value="送る">
    </form>
</p>
</div> -->

<!-- フォーム送信とForm Helper (3/5) -->
<!-- <h1>Index Page</h1>
<p>this is test View.</p>
<p><?= $result ?></p>
<p>
    <?= $this->Form->create(false,[ 'type'=>'post', 'action'=>'.']) ?>
    <?= $this->Form->text('text1') ?>
    <?= $this->Form->end("送信") ?>
</p> -->


<!-- フォーム送信とForm Helper (5/5) -->
<!-- <h1>Index Page</h1>
<p>this is test View.</p>
<p><?= $result ?></p>
<p>
    <?= $this->Form->create(false,['type'=>'post', 'action'=>'.']) ?>
    <?= $this->Form->label('text1','message') ?>
    <?= $this->Form->text('text1') ?>
    <?= $this->Form->checkbox('check1') ?>
    <?= $this->Form->label('check1','checkbox1') ?>
    <?= $this->Form->radio('radio1',['male'=>'男性','female'=>'女性']) ?>
    <?= $this->Form->select('select1',['Mac'=>'マック','Windows'=>'ウィンドウズ','Linux'=>'リナックス']) ?>
    <?= $this->Form->end('送信') ?>
</p>
</div> -->

<!-- レイアウトを作ろう！ (4/4) -->
<h1>Index Page</h1>
<p>this is test View.</p>
<p><?= $msg ?></p>