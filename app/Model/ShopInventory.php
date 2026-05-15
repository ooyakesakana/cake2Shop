<?php
App::uses('AppModel', 'Model');

class ShopInventory extends AppModel
{
	public $belongsTo = [
		'Shop' => ['className' => 'Shop', 'foreignKey' => 'shop_id'],
		'Item' => ['className' => 'Item', 'foreignKey' => 'item_code', 'bindingKey' => 'item_code'],
	];

	public $validate = [
		'shop_id' => ['rule' => 'notEmpty', 'message' => 'ショップを選択してください'],
		'item_code' => ['rule' => 'notEmpty', 'message' => '商品コードを入力してください'],
		'stock_quantity' => [
			'notBlank' => ['rule' => 'notEmpty', 'message' => '在庫数を入力してください'],
			'numeric' => ['rule' => 'numeric', 'message' => '在庫数は数字で入力してください']
		],
	];
}
