<?php
App::uses('AppController', 'Controller');

class SalesController extends AppController
{
	public function index()
	{
		$this->layout = 'layout';
		$this->set('titleForLayout', '売上管理メニュー');
		$this->loadModel('Sale');

		$sales = $this->Sale->find('all', [
			'order' => ['Sale.sale_date' => 'DESC', 'Sale.id' => 'DESC'],
			'recursive' => 0,
		]);

		$this->set(compact('sales'));
	}


	public function main()
	{
		$this->layout = 'layout';
		$this->set('titleForLayout', '売上管理メニュー');
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
			'fields' => ['Shop.shop_id', 'Shop.default_shipping_fee'],
			'conditions' => ['Shop.is_active' => 1],
			'recursive' => -1,
		]);
		$shopShippingMap = [];
		foreach ($shopRows as $row) {
			$shopShippingMap[(int)$row['Shop']['shop_id']] = $row['Shop']['default_shipping_fee'];
		}

		$items = $this->Item->find('all', [
			'fields' => ['Item.item_code', 'Item.item_name'],
			'order' => ['Item.item_code' => 'ASC'],
			'recursive' => -1,
		]);

		if ($this->request->is('post')) {
			$data = $this->request->data;
			$sale = $data['Sale'];
			$details = isset($data['SaleDetail']) ? (array)$data['SaleDetail'] : [];

			$validDetails = [];
			$subtotal = 0;
			foreach ($details as $d) {
				$itemCode = trim((string)($d['item_code'] ?? ''));
				$qty = (float)($d['quantity'] ?? 0);
				$price = (float)($d['unit_price'] ?? 0);
				if ($itemCode === '' && $qty <= 0 && $price <= 0) {
					continue;
				}
				if ($itemCode === '' || $qty <= 0 || $price < 0) {
					$this->Session->setFlash('売上明細の 商品 / 数量 / 単価 は必須です。', 'default', [], 'errMsg');
					$this->set(compact('shops', 'items', 'shippingFees', 'shopShippingMap'));
					return;
				}
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
				$this->set(compact('shops', 'items', 'shippingFees', 'shopShippingMap'));
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
			$shippingFee = isset($shippingFeeAmountMap[$shippingFeeId]) ? $shippingFeeAmountMap[$shippingFeeId] : 0;
			$actualShippingFeeId = !empty($sale['actual_shipping_fee_id']) ? (int)$sale['actual_shipping_fee_id'] : $shippingFeeId;
			$actualShippingCost = isset($shippingFeeAmountMap[$actualShippingFeeId]) ? $shippingFeeAmountMap[$actualShippingFeeId] : $shippingFee;

			$actualShipping = $isShippingIncluded ? 0 : (($freeThreshold > 0 && $subtotal >= $freeThreshold) ? 0 : $shippingFee);
			$feeBaseAmount = $subtotal + $actualShipping;
			$feeAmount = floor($feeBaseAmount * ($feePercent / 100));
			$netSales = $subtotal + $actualShipping - $actualShippingCost - $feeAmount;

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

		$this->set(compact('shops', 'items', 'shippingFees', 'shopShippingMap'));
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
