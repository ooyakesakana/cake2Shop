<?php $this->start('subMenu');
echo $this->element('item_menu');
$this->end(); ?>
<div class="main-area">
	<h2>在庫調整（初期不良/返金対応）</h2>
	<?= $this->Form->create('InventoryAdjustment'); ?>
	<table>
		<tr>
			<th>商品</th>
			<th>減算数量</th>
			<th>減算元ショップ(未登録在庫を使うなら空欄)</th>
			<th>再送料</th>
			<th>理由</th>
			<th>メモ</th>
		</tr>
		<tr>
			<td class="item-picker-cell">
				<?= $this->Form->hidden('item_code', ['id' => 'selected-item-code', 'value' => $selectedItemCode]); ?>
				<input type="text" id="item-search-keyword" placeholder="型番・商品名で検索">
				<button type="button" class="sbm-btn btn--blue" id="open-item-search">検索</button>
				<div id="selected-item-summary" class="selected-item-summary">
					<?php if (!empty($selectedItem)): ?>
						<?= h($selectedItem['item_code']); ?> / <?= h($selectedItem['item_name']); ?>
					<?php else: ?>
						商品未選択
					<?php endif; ?>
				</div>
			</td>
			<td><?= $this->Form->input('adjust_qty', ['type' => 'number', 'step' => '0.01', 'label' => false, 'after' => ' 個']); ?></td>
			<td><?= $this->Form->input('shop_id', ['type' => 'select', 'options' => $shops, 'empty' => '未登録在庫から減算', 'label' => false]); ?></td>
			<td><?= $this->Form->input('shipping_loss', ['type' => 'number', 'step' => '0.01', 'label' => false, 'value' => 0, 'after' => ' 円']); ?></td>
			<td><?= $this->Form->input('reason', ['label' => false, 'value' => '初期不良/返金']); ?></td>
			<td><?= $this->Form->input('memo', ['label' => false]); ?></td>
		</tr>
	</table>
	<br>
	<?= $this->Form->submit('在庫減算と費用計上', ['class' => 'btn btn--orange']); ?><?= $this->Form->end(); ?>
	<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>

	<?php if (!empty($shopStockList)): ?>
		<h3>選択商品のショップ在庫一覧</h3>
		<table>
			<tr>
				<th>ショップ</th>
				<th>在庫数</th>
			</tr><?php foreach ($shopStockList as $r): ?><tr>
					<td><?= h($r['Shop']['shop_name']) ?></td>
					<td><?= h($r['ShopInventory']['stock_quantity']) ?></td>
				</tr><?php endforeach; ?>
		</table>
	<?php endif; ?>
</div>

<div id="item-search-modal" class="stock-item-modal" aria-hidden="true">
	<div class="stock-item-modal__panel">
		<div class="stock-item-modal__header">
			<h3>商品検索</h3>
			<button type="button" class="stock-item-modal__close" id="close-item-search">×</button>
		</div>
		<div class="stock-item-modal__body">
			<div id="item-search-results" class="item-search-results"></div>
		</div>
	</div>
</div>

<script>
(function () {
	var items = <?= json_encode($itemSearchRows, JSON_UNESCAPED_UNICODE); ?>;
	var keyword = document.getElementById('item-search-keyword');
	var modal = document.getElementById('item-search-modal');
	var results = document.getElementById('item-search-results');
	var selectedCode = document.getElementById('selected-item-code');
	var selectedSummary = document.getElementById('selected-item-summary');
	var openButton = document.getElementById('open-item-search');
	var closeButton = document.getElementById('close-item-search');
	var imageBasePath = <?= json_encode($this->Html->url('/img/items/')); ?>;

	function openModal() {
		renderResults();
		modal.classList.add('is-open');
		modal.setAttribute('aria-hidden', 'false');
	}

	function closeModal() {
		modal.classList.remove('is-open');
		modal.setAttribute('aria-hidden', 'true');
	}

	function normalize(value) {
		return String(value || '').toLowerCase();
	}

	function renderResults() {
		var q = normalize(keyword.value).trim();
		var matches = items.filter(function (item) {
			if (q === '') {
				return true;
			}
			return normalize(item.item_code).indexOf(q) !== -1 ||
				normalize(item.item_name).indexOf(q) !== -1 ||
				normalize(item.category).indexOf(q) !== -1;
		}).slice(0, 30);

		results.innerHTML = '';
		if (matches.length === 0) {
			results.innerHTML = '<p>該当商品はありません。</p>';
			return;
		}

		matches.forEach(function (item) {
			var row = document.createElement('div');
			row.className = 'item-search-result';
			row.innerHTML =
				'<img class="item-search-result__thumb" src="' + imageBasePath + encodeURIComponent(item.thumb_image || 'sample.png') + '" alt="thumb">' +
				'<div class="item-search-result__main">' +
				'<strong>' + escapeHtml(item.item_code) + ' / ' + escapeHtml(item.item_name) + '</strong>' +
				'<span>カテゴリ: ' + escapeHtml(item.category || '-') + ' / サイズ: ' + escapeHtml(item.item_size || '-') + '</span>' +
				'<span>総在庫: ' + escapeHtml(item.total_stock) + ' / フリー在庫: ' + escapeHtml(item.free_stock) + ' / 原価: ' + escapeHtml(item.base_price || 0) + '円</span>' +
				'<span>メモ: ' + escapeHtml(item.memo || '-') + '</span>' +
				'</div>' +
				'<button type="button" class="sbm-btn btn--orange">この商品を選択</button>';
			row.querySelector('button').addEventListener('click', function () {
				selectedCode.value = item.item_code;
				selectedSummary.textContent = item.item_code + ' / ' + item.item_name;
				closeModal();
			});
			results.appendChild(row);
		});
	}

	function escapeHtml(value) {
		return String(value == null ? '' : value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	openButton.addEventListener('click', openModal);
	closeButton.addEventListener('click', closeModal);
	modal.addEventListener('click', function (event) {
		if (event.target === modal) {
			closeModal();
		}
	});
	keyword.addEventListener('keydown', function (event) {
		if (event.key === 'Enter') {
			event.preventDefault();
			openModal();
		}
	});
})();
</script>
