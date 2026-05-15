<?php
App::uses('AppController', 'Controller');

class ProcurementsController extends AppController
{
	public function index()
	{
		$this->layout = 'layout';
		$this->set('titleForLayout', '仕入管理メニュー');
	}

	public function purchases()
	{
		$this->layout = 'layout';
		$this->loadModel('Purchase');
		$purchases = $this->Purchase->find('all', ['order' => ['Purchase.purchase_date' => 'DESC', 'Purchase.id' => 'DESC'], 'recursive' => -1]);
		$this->set(compact('purchases'));
	}

	public function add_purchase()
	{
		$this->layout = 'layout';
		$this->loadModel('Purchase');
		if ($this->request->is('post')) {
			$d = $this->request->data['Purchase'];
			$d['total_purchase_cost'] = (float)$d['item_amount'] + (float)$d['intl_shipping'] + (float)$d['customs_duty'] + (float)$d['import_tax'] + (float)$d['agency_fee'] + (float)$d['other_cost'];
			$this->Purchase->create();
			if ($this->Purchase->save($d)) {
				return $this->redirect(['action' => 'purchase_detail', $this->Purchase->id]);
			}
		}
	}

	public function purchase_detail($purchaseId = null)
	{
		$this->layout = 'layout';
		$this->loadModel('Purchase');
		$this->loadModel('PurchaseDetail');
		$purchase = $this->Purchase->findById($purchaseId);
		if (!$purchase) {
			throw new NotFoundException('仕入が見つかりません');
		}
		if ($this->request->is('post')) {
			$row = $this->request->data['PurchaseDetail'];
			$row['purchase_id'] = $purchaseId;
			$row['line_subtotal'] = (float)$row['quantity'] * (float)$row['unit_price'];
			$this->PurchaseDetail->create();
			$this->PurchaseDetail->save($row);
			return $this->redirect(['action' => 'purchase_detail', $purchaseId]);
		}
		$details = $this->PurchaseDetail->find('all', ['conditions' => ['purchase_id' => $purchaseId], 'recursive' => -1]);
		$this->set(compact('purchase', 'details'));
	}

	public function productize($purchaseId = null)
	{
		$this->layout = 'layout';
		$this->loadModel('Purchase');
		$this->loadModel('PurchaseDetail');
		$this->loadModel('Productization');
		$this->loadModel('ProductizationMaterial');
		$this->loadModel('InventoryLot');
		$this->loadModel('Item');
		$purchase = $this->Purchase->findById($purchaseId);
		if (!$purchase) {
			throw new NotFoundException('仕入が見つかりません');
		}
		$details = $this->PurchaseDetail->find('all', ['conditions' => ['purchase_id' => $purchaseId], 'recursive' => -1]);
		$items = $this->Item->find('list', ['fields' => ['Item.item_code', 'Item.item_name']]);

		// 原価配賦プレビュー（商品代金比率 or 数量比）
		$allocationMethod = $this->request->data('Productization.allocation_method') ?: ($purchase['Purchase']['allocation_method'] ?? 'value_ratio');
		$nonItemCost = (float)$purchase['Purchase']['total_purchase_cost'] - (float)$purchase['Purchase']['item_amount'];
		$baseTotal = 0.0;
		foreach ($details as $d) {
			$baseTotal += ($allocationMethod === 'qty_ratio') ? (float)$d['PurchaseDetail']['quantity'] : (float)$d['PurchaseDetail']['line_subtotal'];
		}
		$allocationPreview = [];
		foreach ($details as $d) {
			$base = ($allocationMethod === 'qty_ratio') ? (float)$d['PurchaseDetail']['quantity'] : (float)$d['PurchaseDetail']['line_subtotal'];
			$ratio = ($baseTotal > 0) ? ($base / $baseTotal) : 0;
			$allocationPreview[$d['PurchaseDetail']['id']] = round($nonItemCost * $ratio, 2);
		}

		if ($this->request->is('post')) {
			$head = $this->request->data['Productization'];
			$head['purchase_id'] = $purchaseId;
			$head['allocation_method'] = $allocationMethod;
			if (empty($head['unit_cost_manual']) && !empty($head['allocated_amount']) && !empty($head['completed_qty'])) {
				$head['unit_cost'] = (float)$head['allocated_amount'] / (float)$head['completed_qty'];
			} else {
				$head['unit_cost'] = (float)$head['unit_cost_manual'];
			}
			$head['inventory_reflected'] = 0;
			$this->Productization->create();
			if ($this->Productization->save($head)) {
				$pid = $this->Productization->id;
				foreach ((array)$this->request->data['ProductizationMaterial'] as $m) {
					if (empty($m['purchase_detail_id'])) {
						continue;
					}
					$m['productization_id'] = $pid;
					if (empty($m['allocated_amount']) && isset($allocationPreview[$m['purchase_detail_id']])) {
						$m['allocated_amount'] = $allocationPreview[$m['purchase_detail_id']];
					}
					$this->ProductizationMaterial->create();
					$this->ProductizationMaterial->save($m);
				}
				if ((int)$head['inventory_reflect_now'] === 1) {
					$this->_reflectInventoryLot($pid);
				}
				return $this->redirect(['action' => 'productization_history']);
			}
		}
		$this->set(compact('purchase', 'details', 'items', 'allocationMethod', 'allocationPreview', 'nonItemCost'));
	}

	private function _reflectInventoryLot($productizationId)
	{
		$this->loadModel('Productization');
		$this->loadModel('InventoryLot');
		$p = $this->Productization->findById($productizationId);
		if (!$p || (int)$p['Productization']['inventory_reflected'] === 1) {
			return;
		}
		$lot = ['item_code' => $p['Productization']['item_code'], 'purchase_id' => $p['Productization']['purchase_id'], 'productization_id' => $productizationId, 'quantity' => $p['Productization']['completed_qty'], 'remaining_qty' => $p['Productization']['completed_qty'], 'unit_cost' => $p['Productization']['unit_cost'], 'registered_date' => date('Y-m-d'), 'memo' => $p['Productization']['memo']];
		$this->InventoryLot->create();
		$this->InventoryLot->save($lot);
		$this->Productization->id = $productizationId;
		$this->Productization->saveField('inventory_reflected', 1);
	}

	public function productization_history()
	{
		$this->layout = 'layout';
		$this->loadModel('Productization');
		$rows = $this->Productization->find('all', ['order' => ['Productization.id' => 'DESC'], 'recursive' => -1]);
		$this->set('rows', $rows);
	}
	public function inventory_lots()
	{
		$this->layout = 'layout';
		$this->loadModel('InventoryLot');
		$rows = $this->InventoryLot->find('all', ['order' => ['InventoryLot.item_code' => 'ASC', 'InventoryLot.id' => 'ASC'], 'recursive' => -1]);
		$this->set('rows', $rows);
	}
	public function provisional_items()
	{
		$this->layout = 'layout';
		$this->loadModel('ProvisionalItem');
		if ($this->request->is('post')) {
			$this->ProvisionalItem->create();
			$this->ProvisionalItem->save($this->request->data['ProvisionalItem']);
			return $this->redirect(['action' => 'provisional_items']);
		}
		$rows = $this->ProvisionalItem->find('all', ['order' => ['ProvisionalItem.id' => 'DESC'], 'recursive' => -1]);
		$this->set('rows', $rows);
	}
	public function report()
	{
		$this->layout = 'layout';
		$from = $this->request->query('from') ?: date('Y-01-01');
		$to = $this->request->query('to') ?: date('Y-m-d');
		$this->loadModel('Sale');
		$this->loadModel('Purchase');
		$sales = $this->Sale->find('all', ['conditions' => ['Sale.sale_date >=' => $from, 'Sale.sale_date <=' => $to], 'recursive' => 1]);
		$purchases = $this->Purchase->find('all', ['conditions' => ['Purchase.purchase_date >=' => $from, 'Purchase.purchase_date <=' => $to], 'recursive' => -1]);
		$totalSales = 0;
		foreach ($sales as $s) {
			foreach ((array)$s['SaleDetail'] as $d) {
				$totalSales += ((float)$d['quantity'] * (float)$d['unit_price']);
			}
		}
		$totalPurchase = 0;
		foreach ($purchases as $p) {
			$totalPurchase += (float)$p['Purchase']['total_purchase_cost'];
		}
		$this->set(compact('from', 'to', 'totalSales', 'totalPurchase'));
	}
}
