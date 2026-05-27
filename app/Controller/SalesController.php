<?php
App::uses('AppController', 'Controller');

class SalesController extends AppController
{
	public function index()
	{
		$this->layout = 'layout';
		$this->set('titleForLayout', '売上管理メニュー');
		$this->loadModel('Sale');

		$status = $this->request->query('status');
		$saleFields = array_keys($this->Sale->schema());
		$hasSaleStatus = in_array('status', $saleFields, true);
		$conditions = [];
		if ($status === 'provisional' && $hasSaleStatus) {
			$conditions['Sale.status'] = 'provisional';
		}
		$sales = $this->Sale->find('all', [
			'conditions' => $conditions,
			'order' => ['Sale.sale_date' => 'DESC', 'Sale.id' => 'DESC'],
			'recursive' => 2,
		]);

		$this->set(compact('sales', 'status', 'hasSaleStatus'));
	}


	public function main()
	{
		return $this->redirect(['action' => 'index']);
	}

	public function add()
	{
		$this->layout = 'layout';
		$this->set('titleForLayout', '売上登録');
		$this->loadModel('Sale');
		$this->loadModel('SaleDetail');
		$this->loadModel('Shop');
		$this->loadModel('Item');
		$this->loadModel('ShippingFee');
		$this->loadModel('ShopInventory');
		$this->loadModel('InventoryLot');
		$this->loadModel('SaleLotUsage');
		$this->loadModel('ShopItemPrice');

		$shops = $this->Shop->find('list', [
			'fields' => ['Shop.shop_id', 'Shop.shop_name'],
			'conditions' => ['Shop.is_active' => 1],
			'order' => ['Shop.shop_name' => 'ASC'],
		]);

		$shippingFeeRows = $this->ShippingFee->find('all', [
			'fields' => ['ShippingFee.id', 'ShippingFee.shipping_fee_name', 'ShippingFee.shipping_fee'],
			'order' => ['ShippingFee.shipping_fee' => 'ASC'],
			'recursive' => -1,
		]);
		$shippingFees = [];
		$shippingFeeAmountMap = [];
		foreach ($shippingFeeRows as $row) {
			$id = (int)$row['ShippingFee']['id'];
			$shippingFeeAmount = (float)$row['ShippingFee']['shipping_fee'];
			$shippingFees[$id] = $row['ShippingFee']['shipping_fee_name'] . ' (' . number_format($shippingFeeAmount) . '円)';
			$shippingFeeAmountMap[$id] = $shippingFeeAmount;
		}

		$shopRows = $this->Shop->find('all', [
			'fields' => ['Shop.shop_id', 'Shop.default_shipping_fee', 'Shop.fee_shipping_threshold', 'Shop.is_shipping_included'],
			'conditions' => ['Shop.is_active' => 1],
			'recursive' => -1,
		]);
		$shopShippingMap = [];
		$shopInfoMap = [];
		foreach ($shopRows as $row) {
			$shopId = (int)$row['Shop']['shop_id'];
			$shopShippingMap[$shopId] = $row['Shop']['default_shipping_fee'];
			$shopInfoMap[$shopId] = [
				'fee_shipping_threshold' => (float)($row['Shop']['fee_shipping_threshold'] ?? 0),
				'is_shipping_included' => (int)($row['Shop']['is_shipping_included'] ?? 0),
			];
		}

		$itemRows = $this->Item->find('all', [
			'fields' => ['Item.item_code', 'Item.item_name', 'Item.category', 'Item.item_size', 'Item.memo', 'Item.base_price'],
			'conditions' => ['Item.is_active' => 1],
			'order' => ['Item.item_code' => 'ASC'],
			'recursive' => -1,
		]);
		$saleItemRows = [];
		$thumbMap = [];
		$sources = $this->Item->getDataSource()->listSources();
		if (in_array('item_images', $sources, true)) {
			$this->loadModel('ItemImage');
			$imageRows = $this->ItemImage->find('all', [
				'fields' => ['ItemImage.item_code', 'ItemImage.file_name'],
				'conditions' => ['ItemImage.image_order' => 1],
				'recursive' => -1,
			]);
			foreach ($imageRows as $row) {
				$thumbMap[$row['ItemImage']['item_code']] = $row['ItemImage']['file_name'];
			}
		}
		$lotRows = $this->InventoryLot->find('all', [
			'fields' => ['InventoryLot.item_code', 'SUM(InventoryLot.remaining_qty) AS total_stock'],
			'group' => ['InventoryLot.item_code'],
			'recursive' => -1,
		]);
		$lotMap = [];
		foreach ($lotRows as $row) {
			$lotMap[$row['InventoryLot']['item_code']] = (float)$row[0]['total_stock'];
		}
		$shopStockRows = $this->ShopInventory->find('all', [
			'fields' => ['ShopInventory.item_code', 'SUM(ShopInventory.stock_quantity) AS shop_stock'],
			'group' => ['ShopInventory.item_code'],
			'recursive' => -1,
		]);
		$shopStockMap = [];
		foreach ($shopStockRows as $row) {
			$shopStockMap[$row['ShopInventory']['item_code']] = (float)$row[0]['shop_stock'];
		}
		$priceRows = $this->ShopItemPrice->find('all', [
			'fields' => ['ShopItemPrice.shop_id', 'ShopItemPrice.item_code', 'ShopItemPrice.sale_price'],
			'recursive' => -1,
		]);
		$priceMap = [];
		foreach ($priceRows as $row) {
			$code = $row['ShopItemPrice']['item_code'];
			$shopId = (int)$row['ShopItemPrice']['shop_id'];
			$priceMap[$code][$shopId] = (float)$row['ShopItemPrice']['sale_price'];
		}
		foreach ($itemRows as $row) {
			$code = $row['Item']['item_code'];
			$totalStock = isset($lotMap[$code]) ? $lotMap[$code] : 0;
			$registeredStock = isset($shopStockMap[$code]) ? $shopStockMap[$code] : 0;
			$saleItemRows[] = [
				'item_code' => $code,
				'item_name' => $row['Item']['item_name'],
				'category' => $row['Item']['category'],
				'item_size' => $row['Item']['item_size'],
				'memo' => $row['Item']['memo'],
				'base_price' => $row['Item']['base_price'],
				'thumb_image' => isset($thumbMap[$code]) ? $thumbMap[$code] : 'sample.png',
				'total_stock' => $totalStock,
				'free_stock' => $totalStock - $registeredStock,
				'prices' => isset($priceMap[$code]) ? $priceMap[$code] : [],
			];
		}

		if ($this->request->is('post')) {
			$data = $this->request->data;
			$sale = $data['Sale'];
			$details = isset($data['SaleDetail']) ? (array)$data['SaleDetail'] : [];
			$shopId = (int)($sale['shop_id'] ?? 0);

			$validDetails = [];
			$subtotal = 0;
			foreach ($details as $d) {
				$itemCode = trim((string)($d['item_code'] ?? ''));
				$qty = (float)($d['quantity'] ?? 0);
				if ($itemCode === '' && $qty <= 0) {
					continue;
				}
				if ($itemCode === '' || $qty <= 0) {
					$this->Session->setFlash('売上明細の 商品 / 数量 は必須です。', 'default', [], 'errMsg');
					$this->set(compact('shops', 'saleItemRows', 'shippingFees', 'shippingFeeAmountMap', 'shopShippingMap', 'shopInfoMap'));
					return;
				}
				if ($shopId <= 0 || !isset($priceMap[$itemCode][$shopId])) {
					$this->Session->setFlash('選択ショップの販売価格が未登録の商品があります。商品登録後の在庫登録画面で販売価格を設定してください。', 'default', [], 'errMsg');
					$this->set(compact('shops', 'saleItemRows', 'shippingFees', 'shippingFeeAmountMap', 'shopShippingMap', 'shopInfoMap'));
					return;
				}
				$price = $priceMap[$itemCode][$shopId];
				$line = $qty * $price;
				$subtotal += $line;
				$validDetails[] = [
					'item_code' => $itemCode,
					'quantity' => $qty,
					'unit_price' => $price,
					'line_amount' => $line,
				];
			}

			if (empty($validDetails)) {
				$this->Session->setFlash('有効な売上明細がありません。', 'default', [], 'errMsg');
				$this->set(compact('shops', 'saleItemRows', 'shippingFees', 'shippingFeeAmountMap', 'shopShippingMap', 'shopInfoMap'));
				return;
			}

			$shop = $this->Shop->find('first', [
				'conditions' => ['Shop.shop_id' => (int)$sale['shop_id']],
				'recursive' => -1,
			]);
			$freeThreshold = $shop ? (float)($shop['Shop']['fee_shipping_threshold'] ?? 0) : 0;
			$feePercent = $shop ? (float)($shop['Shop']['fee_percent'] ?? 0) : 0;
			$isShippingIncluded = $shop ? (int)($shop['Shop']['is_shipping_included'] ?? 0) === 1 : false;
			$shippingFeeId = !empty($sale['shipping_fee_id']) ? (int)$sale['shipping_fee_id'] : 0;
			if ($shippingFeeId <= 0) {
				$this->Session->setFlash('顧客選択の配送方法は必須です。', 'default', [], 'errMsg');
				$this->set(compact('shops', 'saleItemRows', 'shippingFees', 'shippingFeeAmountMap', 'shopShippingMap', 'shopInfoMap'));
				return;
			}
			$shippingFee = isset($shippingFeeAmountMap[$shippingFeeId]) ? $shippingFeeAmountMap[$shippingFeeId] : 0;
			$isShippingCostPending = !empty($sale['shipping_cost_pending']) ? 1 : 0;
			$actualShippingFeeId = $isShippingCostPending ? 0 : (!empty($sale['actual_shipping_fee_id']) ? (int)$sale['actual_shipping_fee_id'] : $shippingFeeId);
			$actualShippingCost = $isShippingCostPending ? 0 : (isset($shippingFeeAmountMap[$actualShippingFeeId]) ? $shippingFeeAmountMap[$actualShippingFeeId] : $shippingFee);

			$actualShipping = $isShippingIncluded ? 0 : (($freeThreshold > 0 && $subtotal >= $freeThreshold) ? 0 : $shippingFee);
			$feeBaseAmount = $subtotal + $actualShipping;
			$feeAmount = floor($feeBaseAmount * ($feePercent / 100));
			$netSales = $subtotal + $actualShipping - $actualShippingCost - $feeAmount;
			$status = $isShippingCostPending ? 'provisional' : 'confirmed';

			$saveSale = [
				'shop_id' => (int)$sale['shop_id'],
				'order_no' => trim((string)($sale['order_no'] ?? '')),
				'sale_date' => $sale['sale_date'],
				'shipping_fee_id' => $shippingFeeId > 0 ? $shippingFeeId : null,
				'actual_shipping_fee_id' => $actualShippingFeeId > 0 ? $actualShippingFeeId : null,
				'subtotal' => $subtotal,
				'actual_shipping' => $actualShipping,
				'actual_shipping_cost' => $actualShippingCost,
				'fee_amount' => $feeAmount,
				'net_sales' => $netSales,
				'status' => $status,
				'shipping_cost_pending' => $isShippingCostPending,
			];

			$ds = $this->Sale->getDataSource();
			$ds->begin();
			try {
				$this->Sale->create();
				if (!$this->Sale->save($saveSale)) {
					throw new Exception('売上ヘッダ保存に失敗しました。');
				}
				$saleId = $this->Sale->id;

				foreach ($validDetails as $d) {
					$d['sale_id'] = $saleId;
					$this->SaleDetail->create();
					if (!$this->SaleDetail->save($d)) {
						throw new Exception('売上明細保存に失敗しました。');
					}
					$saleDetailId = $this->SaleDetail->id;

					$this->_consumeInventoryLots(
						$saleId,
						$saleDetailId,
						$d['item_code'],
						(float)$d['quantity'],
						$saveSale['sale_date']
					);

					$this->ShopInventory->updateAll(
						['ShopInventory.stock_quantity' => 'ShopInventory.stock_quantity - ' . (float)$d['quantity']],
						['ShopInventory.shop_id' => (int)$saveSale['shop_id'], 'ShopInventory.item_code' => $d['item_code']]
					);
				}

				$ds->commit();
				$this->Session->setFlash('売上を登録しました。', 'default', [], 'success');
				return $this->redirect(['action' => 'index']);
			} catch (Exception $e) {
				$ds->rollback();
				$this->Session->setFlash('売上登録に失敗しました: ' . $e->getMessage(), 'default', [], 'errMsg');
			}
		}

		$this->set(compact('shops', 'saleItemRows', 'shippingFees', 'shippingFeeAmountMap', 'shopShippingMap', 'shopInfoMap'));
	}

	public function edit($id = null)
	{
		$this->layout = 'layout';
		$this->set('titleForLayout', '売上編集');
		$this->loadModel('Sale');
		$this->loadModel('ShippingFee');
		$this->loadModel('Shop');

		$sale = $this->Sale->find('first', [
			'conditions' => ['Sale.id' => $id],
			'recursive' => 2,
		]);
		if (!$sale) {
			throw new NotFoundException('売上データが見つかりません。');
		}

		$shippingFeeRows = $this->ShippingFee->find('all', [
			'fields' => ['ShippingFee.id', 'ShippingFee.shipping_fee_name', 'ShippingFee.shipping_fee'],
			'order' => ['ShippingFee.shipping_fee' => 'ASC'],
			'recursive' => -1,
		]);
		$shippingFees = [];
		$shippingFeeAmountMap = [];
		foreach ($shippingFeeRows as $row) {
			$shippingFeeId = (int)$row['ShippingFee']['id'];
			$amount = (float)$row['ShippingFee']['shipping_fee'];
			$shippingFees[$shippingFeeId] = $row['ShippingFee']['shipping_fee_name'] . ' (' . number_format($amount) . '円)';
			$shippingFeeAmountMap[$shippingFeeId] = $amount;
		}

		if ($this->request->is(['post', 'put'])) {
			$data = $this->request->data['Sale'];
			$isShippingCostPending = !empty($data['shipping_cost_pending']) ? 1 : 0;
			$actualShippingFeeId = $isShippingCostPending ? 0 : (!empty($data['actual_shipping_fee_id']) ? (int)$data['actual_shipping_fee_id'] : 0);
			if (!$isShippingCostPending && $actualShippingFeeId <= 0) {
				$this->Session->setFlash('実際に使用した配送方法を選択してください。', 'default', [], 'errMsg');
				return $this->redirect(['action' => 'edit', $sale['Sale']['id']]);
			}

			$subtotal = (float)$sale['Sale']['subtotal'];
			$customerShipping = (float)$sale['Sale']['actual_shipping'];
			$actualShippingCost = $isShippingCostPending ? 0 : (isset($shippingFeeAmountMap[$actualShippingFeeId]) ? $shippingFeeAmountMap[$actualShippingFeeId] : 0);
			$shop = $this->Shop->find('first', [
				'conditions' => ['Shop.shop_id' => (int)$sale['Sale']['shop_id']],
				'recursive' => -1,
			]);
			$feePercent = $shop ? (float)($shop['Shop']['fee_percent'] ?? 0) : 0;
			$feeAmount = floor(($subtotal + $customerShipping) * ($feePercent / 100));
			$netSales = $subtotal + $customerShipping - $actualShippingCost - $feeAmount;

			$this->Sale->id = $sale['Sale']['id'];
			if ($this->Sale->save([
				'actual_shipping_fee_id' => $actualShippingFeeId > 0 ? $actualShippingFeeId : null,
				'actual_shipping_cost' => $actualShippingCost,
				'fee_amount' => $feeAmount,
				'net_sales' => $netSales,
				'status' => $isShippingCostPending ? 'provisional' : 'confirmed',
				'shipping_cost_pending' => $isShippingCostPending,
			])) {
				$this->Session->setFlash('売上を更新しました。', 'default', [], 'success');
				return $this->redirect(['action' => 'index']);
			}
			$this->Session->setFlash('売上更新に失敗しました。', 'default', [], 'errMsg');
		}

		$this->request->data = $sale;
		$this->set(compact('sale', 'shippingFees'));
	}

	private function _consumeInventoryLots($saleId, $saleDetailId, $itemCode, $quantity, $saleDate)
	{
		$remaining = (float)$quantity;
		$lots = $this->InventoryLot->find('all', [
			'conditions' => [
				'InventoryLot.item_code' => $itemCode,
				'InventoryLot.remaining_qty >' => 0,
			],
			'order' => [
				"CASE WHEN InventoryLot.cost_basis_type = 'legacy_estimated' THEN 0 ELSE 1 END ASC",
				'InventoryLot.id ASC',
			],
			'recursive' => -1,
		]);

		foreach ($lots as $lot) {
			if ($remaining <= 0) {
				break;
			}

			$lotQty = (float)$lot['InventoryLot']['remaining_qty'];
			$usedQty = min($remaining, $lotQty);
			if ($usedQty <= 0) {
				continue;
			}

			$this->_saveLotUsage($saleId, $saleDetailId, $lot['InventoryLot'], $usedQty, $saleDate);

			$this->InventoryLot->id = $lot['InventoryLot']['id'];
			$this->InventoryLot->saveField('remaining_qty', $lotQty - $usedQty);
			$remaining -= $usedQty;
		}

		if ($remaining > 0) {
			$this->loadModel('Item');
			$item = $this->Item->find('first', [
				'conditions' => ['Item.item_code' => $itemCode],
				'fields' => ['Item.item_code', 'Item.base_price'],
				'recursive' => -1,
			]);
			$unitCost = $item ? (float)($item['Item']['base_price'] ?? 0) : 0;

			$this->InventoryLot->create();
			if (!$this->InventoryLot->save([
				'item_code' => $itemCode,
				'purchase_id' => null,
				'productization_id' => null,
				'quantity' => $remaining,
				'remaining_qty' => $remaining,
				'unit_cost' => $unitCost,
				'cost_basis_type' => 'legacy_estimated',
				'registered_date' => $saleDate,
				'memo' => '売上登録時の不足分自動作成（推定原価）',
			])) {
				throw new Exception('推定原価ロットの作成に失敗しました。');
			}
			$createdLot = $this->InventoryLot->find('first', [
				'conditions' => ['InventoryLot.id' => $this->InventoryLot->id],
				'recursive' => -1,
			]);
			$this->_saveLotUsage($saleId, $saleDetailId, $createdLot['InventoryLot'], $remaining, $saleDate);
			$this->InventoryLot->id = $createdLot['InventoryLot']['id'];
			$this->InventoryLot->saveField('remaining_qty', 0);
		}
	}

	private function _saveLotUsage($saleId, $saleDetailId, $lot, $usedQty, $saleDate)
	{
		$unitCost = (float)$lot['unit_cost'];
		$this->SaleLotUsage->create();
		if (!$this->SaleLotUsage->save([
			'sale_id' => $saleId,
			'sale_detail_id' => $saleDetailId,
			'inventory_lot_id' => $lot['id'],
			'item_code' => $lot['item_code'],
			'used_qty' => $usedQty,
			'unit_cost' => $unitCost,
			'cogs_amount' => $usedQty * $unitCost,
			'sale_date' => $saleDate,
		])) {
			throw new Exception('売上原価履歴の保存に失敗しました。');
		}
	}

	public function invoice_pdf($id = null)
	{
		$this->layout = 'pdf';
		$this->loadModel('Sale');
		$sale = $this->Sale->find('first', [
			'conditions' => ['Sale.id' => $id],
			'recursive' => 2,
		]);

		if (!$sale) {
			throw new NotFoundException('売上データが見つかりません。');
		}

		$subtotal = 0;
		foreach ((array)$sale['SaleDetail'] as $d) {
			$subtotal += (float)$d['quantity'] * (float)$d['unit_price'];
		}
		$actualShipping = (float)$sale['Sale']['actual_shipping'];
		$total = $subtotal + $actualShipping;

		$this->set(compact('sale', 'subtotal', 'actualShipping', 'total'));
	}
}
