<?php
App::uses('AppModel', 'Model');

class Item extends AppModel
{
    public $primaryKey = 'item_code';

    public $hasMany = [
        'Sale' => [
            'className' => 'Sale',
            'foreignKey' => 'item_code',
            'bindingKey' => 'item_code',
        ],
        'ShopInventory' => [
            'className' => 'ShopInventory',
            'foreignKey' => 'item_code',
            'bindingKey' => 'item_code',
        ]
    ];

    public function beforeValidate($options = [])
    {
        if (!empty($this->data[$this->alias]['item_code'])) {
            $this->data[$this->alias]['item_code'] = mb_convert_kana($this->data[$this->alias]['item_code'], 'as');
        }
        if (!empty($this->data[$this->alias]['base_price'])) {
            $this->data[$this->alias]['base_price'] = mb_convert_kana($this->data[$this->alias]['base_price'], 'n');
        }

        return true;
    }

    public $validate = [
        'item_code' => [
            'notBlank' => [
                'rule' => 'notBlank',
                'message' => '商品コードを入力してください'
            ],
            'maxLength' => [
                'rule' => ['maxLength', 10],
                'message' => '商品コードは10文字以内で入力してください'
            ],
            'isUnique' => [
                'rule' => 'isUnique',
                'message' => 'この商品コードは既に登録されています'
            ],
        ],
        'item_name' => [
            'notBlank' => [
                'rule' => 'notBlank',
                'message' => '商品名を入力してください'
            ]
        ],
    ];


    public function beforeSave($option = [])
    {
        $data = &$this->data[$this->alias];

        if (!$this->id) {
            foreach ($data as $key => $value) {
                if (trim((string)$value, " \t\n\r\0\x0B　") === '') {
                    unset($data[$key]);
                }
            }
        }
        return true;
    }
}
