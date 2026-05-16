<?php

$this->start('subMenu');
echo $this->element('item_menu');
$this->end();

echo $this->Form->create('Item', [
	'type' => 'file'
])
?>
<div class="main-area">
	<table class="item-insert-table">
		<tr>
			<th>商品コード</th>
			<th>商品名</th>
			<th>商品サイズ</th>
			<th>カテゴリー</th>
		</tr>

		<tr>
			<td><?= $this->Form->input('item_code', [
					'type' => 'text',
					'id' => 'item_code',
					'label' => false,
					'error' => false,
				]); ?>
				<?= $this->Form->error('item_code', null, [
					'class' => 'error-text'
				]); ?></td>
			<td><?= $this->Form->input('item_name', [
					'type' => 'text',
					'id' => 'item_name',
					'label' => false,
					'error' => false,
				]); ?>
				<?= $this->Form->error('item_name', null, [
					'class' => 'error-text'
				]); ?>
			</td>
			<td><?= $this->Form->input('item_size', [
					'type' => 'text',
					'id' => 'item_size',
					'label' => false,
					'error' => false,
					'placeholder' =>  '(例)10mm',
				]); ?>
				<?= $this->Form->error('item_size', null, [
					'class' => 'error-text'
				]); ?>
			</td>
			<td><?= $this->Form->input('category_select', [
					'type' => 'select',
					'id' => 'category_select',
					'label' => false,
					'error' => false,
					'options' => $category ?? '',
					'empty' => '既存カテゴリーから選択'
				]); ?>
				<br>
				<?= $this->Form->input('category_text', [
					'type' => 'text',
					'id' => 'category_text',
					'label' => false,
					'error' => false,
					'placeholder' => '新しいカテゴリーを入力'
				]); ?>
				<?= $this->Form->error('category_select', null, [
					'class' => 'error-text'
				]); ?>
			</td>
		</tr>
		<tr>
			<th>原価</th>
			<th>商品説明</th>
			<th>仕入れ先等のメモ</th>
			<th>商品写真</th>
		</tr>
		<tr>
			<td><?= $this->Form->input('base_price', [
					'type' => 'text',
					'id' => 'base_price',
					'label' => false,
					'error' => false,
					'after' => ' 円',
				]); ?>
				<?= $this->Form->error('base_price', null, [
					'class' => 'error-text'
				]); ?><br>
				<label>推定原価フラグ <?= $this->Form->input('is_estimated_cost', ['type' => 'checkbox', 'label' => false, 'value' => 1]); ?></label>
			</td>
			<td><?= $this->Form->input('description', [
					'type' => 'textarea',
					'id' => 'description',
					'label' => false,
					'error' => false,
				]); ?>
				<?= $this->Form->error('description', null, [
					'class' => 'error-text'
				]); ?></td>
			<td><?= $this->Form->input('memo', [
					'type' => 'textarea',
					'id' => 'memo',
					'label' => false,
					'error' => false,
				]); ?>
				<?= $this->Form->error('memo', null, [
					'class' => 'error-text'
				]); ?></td>
			<td><?= $this->Form->input('main_image', [
					'type' => 'file',
					'id' => 'main_image',
					'label' => false,
					'error' => false,
					'accept' => 'image/*'
				]); ?>
				<?= $this->Form->error('main_image', null, [
					'class' => 'error-text'
				]); ?></td>
			<br>追加画像(2枚目以降): <?= $this->Form->input('sub_images', ['type' => 'file', 'multiple' => true, 'name' => 'data[Item][sub_images][]', 'label' => false, 'accept' => 'image/*']); ?>
			</td>
		</tr>


	</table>
	<br>
	<?= $this->Form->submit('登録', [
		'class' => 'btn btn--red',
	]); ?><br>
	<div class="flashMsg">
		<?= $this->Session->flash('success') ?>
		<?= $this->Session->flash('errMsg') ?></div>
	<?= $this->Form->end(); ?>
</div>
