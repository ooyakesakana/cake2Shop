<?php
App::uses('AppModel', 'Model');

class Attachment extends AppModel
{
	public $validate = [
		'target_type' => ['rule' => 'notBlank'],
		'target_id' => ['rule' => 'numeric'],
		'file_name' => ['rule' => 'notBlank'],
		'file_path' => ['rule' => 'notBlank'],
	];
}
