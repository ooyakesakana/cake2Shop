<?php
App::uses('AppModel', 'Model');
class Expense extends AppModel
{
	public $hasMany = [
		'Attachment' => [
			'className' => 'Attachment',
			'foreignKey' => 'target_id',
			'conditions' => ['Attachment.target_type' => 'expense'],
			'dependent' => false,
		],
	];
}
