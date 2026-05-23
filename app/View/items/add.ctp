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
			<th class="item-name-column">商品名</th>
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
			<td class="item-name-cell"><?= $this->Form->input('item_name', [
					'type' => 'text',
					'id' => 'item_name',
					'class' => 'item-name-input',
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
				<button type="button" class="sbm-btn btn--blue js-open-template-modal">テンプレ文を追加</button>
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
				]); ?>
				<div id="sub-image-fields" class="sub-image-fields"></div>
				<button type="button" class="sbm-btn btn--blue" id="add-sub-image">商品画像を追加</button>
				<p class="form-note">追加画像は9枚まで登録できます。</p>
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

<div id="template-modal" class="template-modal" aria-hidden="true">
	<div class="template-modal__panel">
		<div class="template-modal__header">
			<h3>商品説明テンプレ文</h3>
			<button type="button" class="template-modal__close" id="close-template-modal">×</button>
		</div>
		<div class="template-modal__body">
			<?php if (empty($descriptionTemplates)): ?>
				<p>登録済みテンプレ文はありません。</p>
			<?php else: ?>
				<?php foreach ($descriptionTemplates as $template): ?>
					<button type="button" class="template-choice" data-template="<?= h($template['ItemDescriptionTemplate']['template_text']); ?>">
						<?= nl2br(h($template['ItemDescriptionTemplate']['template_text'])); ?>
					</button>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
</div>

<div class="description-template-form">
	<h3>商品説明テンプレ文登録</h3>
	<?= $this->Form->create('ItemDescriptionTemplate', ['url' => ['controller' => 'items', 'action' => 'add']]); ?>
	<?= $this->Form->input('template_text', [
		'type' => 'textarea',
		'label' => false,
		'placeholder' => '例）サイズは目安の為誤差があります',
	]); ?>
	<?= $this->Form->submit('テンプレ文を登録', ['class' => 'btn btn--green']); ?>
	<?= $this->Form->end(); ?>

	<details class="description-template-details">
		<summary>登録済みテンプレ文</summary>
		<?php if (empty($descriptionTemplates)): ?>
			<p>登録済みテンプレ文はありません。</p>
		<?php else: ?>
			<div class="description-template-list">
				<?php foreach ($descriptionTemplates as $template): ?>
					<div class="description-template-row">
						<?= $this->Form->create('ItemDescriptionTemplate', ['url' => ['controller' => 'items', 'action' => 'add']]); ?>
						<?= $this->Form->hidden('id', ['value' => $template['ItemDescriptionTemplate']['id']]); ?>
						<?= $this->Form->input('template_text', [
							'type' => 'textarea',
							'label' => false,
							'value' => $template['ItemDescriptionTemplate']['template_text'],
						]); ?>
						<div class="description-template-actions">
							<?= $this->Form->submit('編集', ['class' => 'sbm-btn btn--orange', 'div' => false]); ?>
							<?= $this->Form->button('削除', [
								'type' => 'submit',
								'class' => 'sbm-btn btn--red',
								'name' => 'delete_template',
								'value' => '1',
								'onclick' => "return confirm('このテンプレ文を削除しますか？');",
							]); ?>
						</div>
						<?= $this->Form->end(); ?>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</details>
</div>

<script>
(function () {
	var sizeInput = document.getElementById('item_size');
	var description = document.getElementById('description');
	var addSubImageButton = document.getElementById('add-sub-image');
	var subImageFields = document.getElementById('sub-image-fields');
	var modal = document.getElementById('template-modal');
	var openModalButton = document.querySelector('.js-open-template-modal');
	var closeModalButton = document.getElementById('close-template-modal');
	var lastDefaultLine = '';
	var maxSubImageCount = 9;

	function defaultSizeLine() {
		var size = sizeInput ? sizeInput.value.trim() : '';
		return 'サイズ：' + size;
	}

	function refreshDefaultSizeLine() {
		if (!description) {
			return;
		}
		var nextLine = defaultSizeLine();
		var current = description.value;
		if (current === '' || current === lastDefaultLine) {
			description.value = nextLine;
		} else if (lastDefaultLine !== '' && current.indexOf(lastDefaultLine + "\n") === 0) {
			description.value = nextLine + current.substring(lastDefaultLine.length);
		}
		lastDefaultLine = nextLine;
	}

	function appendTemplate(text) {
		if (!description || !text) {
			return;
		}
		refreshDefaultSizeLine();
		var current = description.value.replace(/\s+$/g, '');
		description.value = current + (current === '' ? '' : "\n") + text + "\n";
		description.focus();
	}

	function openModal() {
		if (modal) {
			modal.classList.add('is-open');
			modal.setAttribute('aria-hidden', 'false');
		}
	}

	function closeModal() {
		if (modal) {
			modal.classList.remove('is-open');
			modal.setAttribute('aria-hidden', 'true');
		}
	}

	if (sizeInput) {
		sizeInput.addEventListener('input', refreshDefaultSizeLine);
	}
	refreshDefaultSizeLine();

	if (addSubImageButton && subImageFields) {
		addSubImageButton.addEventListener('click', function () {
			if (subImageFields.children.length >= maxSubImageCount) {
				alert('追加画像は9枚までです。');
				return;
			}
			var wrapper = document.createElement('div');
			wrapper.className = 'sub-image-field';
			var input = document.createElement('input');
			input.type = 'file';
			input.name = 'data[Item][sub_images][]';
			input.accept = 'image/*';
			wrapper.appendChild(input);
			subImageFields.appendChild(wrapper);
		});
	}

	if (openModalButton) {
		openModalButton.addEventListener('click', openModal);
	}
	if (closeModalButton) {
		closeModalButton.addEventListener('click', closeModal);
	}
	if (modal) {
		modal.addEventListener('click', function (event) {
			if (event.target === modal) {
				closeModal();
			}
		});
	}
	document.querySelectorAll('.template-choice').forEach(function (button) {
		button.addEventListener('click', function () {
			appendTemplate(button.getAttribute('data-template'));
			closeModal();
		});
	});
})();
</script>
