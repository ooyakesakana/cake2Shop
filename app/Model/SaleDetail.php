<?php
App::uses('AppModel', 'Model');

class SaleDetail extends AppModel
{
	public $belongsTo = [
		'Sale' => ['className' => 'Sale', 'foreignKey' => 'sale_id'],
		'Item' => ['className' => 'Item', 'foreignKey' => 'item_code', 'bindingKey' => 'item_code'],
	];
}
