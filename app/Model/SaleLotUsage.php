<?php
App::uses('AppModel', 'Model');

class SaleLotUsage extends AppModel
{
	public $belongsTo = [
		'SaleDetail' => ['className' => 'SaleDetail', 'foreignKey' => 'sale_detail_id'],
		'InventoryLot' => ['className' => 'InventoryLot', 'foreignKey' => 'inventory_lot_id'],
	];
}
