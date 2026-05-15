<?php
App::uses('AppModel', 'Model');

class Sale extends AppModel
{
	public $belongsTo = [
		'Shop' => ['className' => 'Shop', 'foreignKey' => 'shop_id'],
		'ShippingFee' => ['className' => 'ShippingFee', 'foreignKey' => 'shipping_fee_id'],
	];

	public $hasMany = [
		'SaleDetail' => ['className' => 'SaleDetail', 'foreignKey' => 'sale_id', 'dependent' => true],
	];

	public $validate = [
		'shop_id' => ['rule' => 'notEmpty', 'message' => 'ショップを選択してください'],
		'sale_date' => ['rule' => 'date', 'message' => '販売日を入力してください'],
	];
}
