<?php
App::uses('AppModel', 'Model');

class ItemDescriptionTemplate extends AppModel
{
    public $validate = [
        'template_text' => [
            'notBlank' => [
                'rule' => 'notBlank',
                'message' => 'テンプレ文を入力してください'
            ]
        ],
    ];
}
