<?php $this->start('subMenu');
echo $this->element('sales_menu');
$this->end(); ?>
<div class="main-area">
<h2>売上登録</h2>
<?= $this->Form->create('Sale'); ?>
<table class="shop-insert-table">
	<tr>
		<th>売れたプラットフォーム(必須)</th>
		<th>注文番号(任意)</th>
		<th>販売日</th>
		<th>顧客選択の配送方法(必須)</th>
		<th>実際に使用した配送方法</th>
		<th>郵便局持ち込み</th>
	</tr>
	<tr>
		<td><?= $this->Form->input('shop_id', ['type' => 'select', 'label' => false, 'options' => $shops, 'empty' => '選択してください', 'required' => true, 'id' => 'shop-id']); ?></td>
		<td><?= $this->Form->input('order_no', ['label' => false]); ?></td>
		<td>
			<?= $this->Form->input('sale_date', ['type' => 'date', 'label' => false, 'dateFormat' => 'YMD', 'empty' => false]); ?>
			<input type="date" id="sale-date-picker">
		</td>
		<td><?= $this->Form->input('shipping_fee_id', ['type' => 'select', 'label' => false, 'options' => $shippingFees, 'empty' => '選択してください', 'required' => true, 'id' => 'shipping-fee-id']); ?></td>
		<td><?= $this->Form->input('actual_shipping_fee_id', ['type' => 'select', 'label' => false, 'options' => $shippingFees, 'empty' => '選択してください', 'id' => 'actual-shipping-fee-id']); ?></td>
		<td><?= $this->Form->input('shipping_cost_pending', ['type' => 'checkbox', 'label' => '持ち込み（送料未確定）', 'id' => 'shipping-cost-pending']); ?></td>
	</tr>
</table>

<h3>売れた商品（型番・商品名で検索して追加）</h3>
<table class="shop-insert-table" id="sale-lines">
	<tr>
		<th>商品</th>
		<th>数量</th>
		<th>単価</th>
		<th>金額</th>
	</tr>
	<tr class="sale-line-row">
		<td class="item-picker-cell">
			<input type="hidden" name="data[SaleDetail][0][item_code]" class="sale-item-code">
			<input type="hidden" name="data[SaleDetail][0][unit_price]" class="sale-unit-price-value">
			<input type="text" class="sale-item-keyword" placeholder="型番・商品名で検索">
			<button type="button" class="sbm-btn btn--blue open-sale-item-search">検索</button>
			<div class="selected-item-summary sale-selected-item-summary">商品未選択</div>
		</td>
		<td><input type="number" name="data[SaleDetail][0][quantity]" min="1" class="sale-quantity" /> 個</td>
		<td><span class="sale-unit-price-display">ショップ選択後に自動反映</span></td>
		<td><span class="sale-line-total-display">0円</span></td>
	</tr>
</table>
<br>
<button type="button" class="sbm-btn btn--blue" id="add-line">行追加</button>
<table class="shop-insert-table sale-total-table">
	<tr>
		<th>商品合計</th>
		<th>送料</th>
		<th>登録合計</th>
	</tr>
	<tr>
		<td><span id="sale-subtotal-display">0円</span></td>
		<td><span id="sale-customer-shipping-display">0円</span></td>
		<td><strong id="sale-grand-total-display">0円</strong></td>
	</tr>
</table>
<br>
<?= $this->Form->submit('登録', ['class' => 'btn btn--orange']); ?>
<?= $this->Form->end(); ?>
<div class="flashMsg"><?= $this->Session->flash('success') ?><?= $this->Session->flash('errMsg') ?></div>
</div>

<div id="sale-item-search-modal" class="stock-item-modal" aria-hidden="true">
	<div class="stock-item-modal__panel">
		<div class="stock-item-modal__header">
			<h3>商品検索</h3>
			<button type="button" class="stock-item-modal__close" id="close-sale-item-search">×</button>
		</div>
		<div class="stock-item-modal__body">
			<div id="sale-item-search-results" class="item-search-results"></div>
		</div>
	</div>
</div>

<script>
	(function () {
	function pad2(value) {
		return ('0' + value).slice(-2);
	}
	function syncSaleDatePickerFromSelects() {
		var y = document.getElementById('SaleSaleDateYear');
		var m = document.getElementById('SaleSaleDateMonth');
		var d = document.getElementById('SaleSaleDateDay');
		var picker = document.getElementById('sale-date-picker');
		if (y && m && d && picker && y.value && m.value && d.value) {
			picker.value = y.value + '-' + pad2(m.value) + '-' + pad2(d.value);
		}
	}
	function syncSaleDateSelectsFromPicker() {
		var picker = document.getElementById('sale-date-picker');
		var y = document.getElementById('SaleSaleDateYear');
		var m = document.getElementById('SaleSaleDateMonth');
		var d = document.getElementById('SaleSaleDateDay');
		if (picker && y && m && d && picker.value) {
			var parts = picker.value.split('-');
			y.value = parts[0];
			m.value = parts[1];
			if (m.value !== parts[1]) {
				m.value = String(parseInt(parts[1], 10));
			}
			d.value = parts[2];
			if (d.value !== parts[2]) {
				d.value = String(parseInt(parts[2], 10));
			}
		}
	}
	syncSaleDatePickerFromSelects();
	['SaleSaleDateYear', 'SaleSaleDateMonth', 'SaleSaleDateDay'].forEach(function(id) {
		var select = document.getElementById(id);
		if (select) {
			select.addEventListener('change', syncSaleDatePickerFromSelects);
		}
	});
	var saleDatePicker = document.getElementById('sale-date-picker');
	if (saleDatePicker) {
		saleDatePicker.addEventListener('change', syncSaleDateSelectsFromPicker);
	}

	var shopShippingMap = <?= json_encode($shopShippingMap); ?>;
	document.getElementById('shop-id').addEventListener('change', function() {
		var val = this.value;
		var customerTarget = document.getElementById('shipping-fee-id');
		var actualTarget = document.getElementById('actual-shipping-fee-id');
		if (shopShippingMap[val]) {
			customerTarget.value = shopShippingMap[val];
			actualTarget.value = shopShippingMap[val];
		}
		syncShippingPendingState();
		updateAllSelectedPrices();
		updateSaleTotals();
	});
	document.getElementById('shipping-fee-id').addEventListener('change', updateSaleTotals);
	var pendingCheckbox = document.getElementById('shipping-cost-pending');
	var actualShippingSelect = document.getElementById('actual-shipping-fee-id');
	function syncShippingPendingState() {
		if (!pendingCheckbox || !actualShippingSelect) {
			return;
		}
		actualShippingSelect.disabled = pendingCheckbox.checked;
		if (pendingCheckbox.checked) {
			actualShippingSelect.value = '';
		}
	}
	if (pendingCheckbox) {
		pendingCheckbox.addEventListener('change', syncShippingPendingState);
		syncShippingPendingState();
	}
	var shippingFeeAmountMap = <?= json_encode($shippingFeeAmountMap); ?>;
	var shopInfoMap = <?= json_encode($shopInfoMap); ?>;
	var saleItems = <?= json_encode($saleItemRows, JSON_UNESCAPED_UNICODE); ?>;
	var activeSaleRow = null;
	var modal = document.getElementById('sale-item-search-modal');
	var results = document.getElementById('sale-item-search-results');
	var closeButton = document.getElementById('close-sale-item-search');
	var imageBasePath = <?= json_encode($this->Html->url('/img/items/')); ?>;
	var saleLineIndex = document.querySelectorAll('#sale-lines tr.sale-line-row').length;

	document.getElementById('add-line').addEventListener('click', function() {
		var table = document.getElementById('sale-lines');
		var row = table.insertRow(-1);
		row.className = 'sale-line-row';
		row.innerHTML =
			'<td class="item-picker-cell">' +
			'<input type="hidden" name="data[SaleDetail][' + saleLineIndex + '][item_code]" class="sale-item-code">' +
			'<input type="hidden" name="data[SaleDetail][' + saleLineIndex + '][unit_price]" class="sale-unit-price-value">' +
			'<input type="text" class="sale-item-keyword" placeholder="型番・商品名で検索"> ' +
			'<button type="button" class="sbm-btn btn--blue open-sale-item-search">検索</button>' +
			'<div class="selected-item-summary sale-selected-item-summary">商品未選択</div>' +
			'</td>' +
			'<td><input type="number" name="data[SaleDetail][' + saleLineIndex + '][quantity]" min="1" class="sale-quantity" /> 個</td>' +
			'<td><span class="sale-unit-price-display">ショップ選択後に自動反映</span></td>' +
			'<td><span class="sale-line-total-display">0円</span></td>';
		saleLineIndex++;
		updateSaleTotals();
	});

	function openModal(row) {
		activeSaleRow = row;
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

	function getActiveKeyword() {
		if (!activeSaleRow) {
			return '';
		}
		var input = activeSaleRow.querySelector('.sale-item-keyword');
		return input ? input.value : '';
	}

	function renderResults() {
		var q = normalize(getActiveKeyword()).trim();
		var matches = saleItems.filter(function (item) {
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
				if (!activeSaleRow) {
					return;
				}
				activeSaleRow.querySelector('.sale-item-code').value = item.item_code;
				activeSaleRow.dataset.selectedItemCode = item.item_code;
				activeSaleRow.querySelector('.sale-selected-item-summary').textContent = item.item_code + ' / ' + item.item_name;
				activeSaleRow.querySelector('.sale-item-keyword').value = item.item_name;
				updateRowPrice(activeSaleRow, item);
				updateSaleTotals();
				closeModal();
			});
			results.appendChild(row);
		});
	}

	function findItemByCode(itemCode) {
		for (var i = 0; i < saleItems.length; i++) {
			if (saleItems[i].item_code === itemCode) {
				return saleItems[i];
			}
		}
		return null;
	}

	function updateRowPrice(row, item) {
		var shopId = document.getElementById('shop-id').value;
		var display = row.querySelector('.sale-unit-price-display');
		var hidden = row.querySelector('.sale-unit-price-value');
		if (!shopId) {
			display.textContent = 'ショップを選択してください';
			hidden.value = '';
			return;
		}
		if (!item || !item.prices || typeof item.prices[shopId] === 'undefined') {
			display.textContent = 'このショップの価格未登録';
			hidden.value = '';
			return;
		}
		hidden.value = item.prices[shopId];
		display.textContent = Number(item.prices[shopId]).toLocaleString() + '円';
		updateLineTotal(row);
	}

	function updateAllSelectedPrices() {
		document.querySelectorAll('#sale-lines tr.sale-line-row').forEach(function (row) {
			var itemCode = row.dataset.selectedItemCode || row.querySelector('.sale-item-code').value;
			if (itemCode) {
				updateRowPrice(row, findItemByCode(itemCode));
			}
		});
		updateSaleTotals();
	}

	function numberValue(value) {
		var parsed = parseFloat(value);
		return isNaN(parsed) ? 0 : parsed;
	}

	function formatYen(value) {
		return Math.floor(value).toLocaleString() + '円';
	}

	function updateLineTotal(row) {
		var qtyInput = row.querySelector('.sale-quantity');
		var priceInput = row.querySelector('.sale-unit-price-value');
		var totalDisplay = row.querySelector('.sale-line-total-display');
		var lineTotal = numberValue(qtyInput ? qtyInput.value : 0) * numberValue(priceInput ? priceInput.value : 0);
		if (totalDisplay) {
			totalDisplay.textContent = formatYen(lineTotal);
		}
		return lineTotal;
	}

	function calculateCustomerShipping(subtotal) {
		var shopId = document.getElementById('shop-id').value;
		var shippingFeeId = document.getElementById('shipping-fee-id').value;
		var shopInfo = shopInfoMap[shopId] || {};
		var selectedShippingFee = numberValue(shippingFeeAmountMap[shippingFeeId]);
		var freeThreshold = numberValue(shopInfo.fee_shipping_threshold);
		var isShippingIncluded = Number(shopInfo.is_shipping_included || 0) === 1;
		if (isShippingIncluded) {
			return 0;
		}
		if (freeThreshold > 0 && subtotal >= freeThreshold) {
			return 0;
		}
		return selectedShippingFee;
	}

	function updateSaleTotals() {
		var subtotal = 0;
		document.querySelectorAll('#sale-lines tr.sale-line-row').forEach(function (row) {
			subtotal += updateLineTotal(row);
		});
		var customerShipping = calculateCustomerShipping(subtotal);
		document.getElementById('sale-subtotal-display').textContent = formatYen(subtotal);
		document.getElementById('sale-customer-shipping-display').textContent = formatYen(customerShipping);
		document.getElementById('sale-grand-total-display').textContent = formatYen(subtotal + customerShipping);
	}

	function escapeHtml(value) {
		return String(value == null ? '' : value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	document.getElementById('sale-lines').addEventListener('click', function (event) {
		if (event.target.classList.contains('open-sale-item-search')) {
			openModal(event.target.closest('tr'));
		}
	});
	document.getElementById('sale-lines').addEventListener('keydown', function (event) {
		if (event.target.classList.contains('sale-item-keyword') && event.key === 'Enter') {
			event.preventDefault();
			openModal(event.target.closest('tr'));
		}
	});
	document.getElementById('sale-lines').addEventListener('input', function (event) {
		if (event.target.classList.contains('sale-quantity')) {
			updateSaleTotals();
		}
	});
	updateSaleTotals();
	closeButton.addEventListener('click', closeModal);
	modal.addEventListener('click', function (event) {
		if (event.target === modal) {
			closeModal();
		}
	});
	})();
</script>
