<?php
App::uses('AppModel', 'Model');

class Shop extends AppModel
{

    public $primaryKey = 'shop_id';

    public $hasMany = [
        'Sale' => [
            'className' => 'Sale',
            'foreignKey' => 'shop_id',
        ],
        'ShopInventory' => [
            'className' => 'ShopInventory',
            'foreignKey' => 'shop_id'
        ]
    ];

    public $belongsTo = [
        'ShippingFee' => [
            'className' => 'ShippingFee',
            'foreignKey' => 'default_shipping_fee',
        ]
    ];

    public function beforeValidate($options = [])
    {
        if (!empty($this->data[$this->alias]['fee_percent'])) {
            $this->data[$this->alias]['fee_percent'] = mb_convert_kana($this->data[$this->alias]['fee_percent'], 'n');
        }
        if (!empty($this->data[$this->alias]['fee_shipping_threshold'])) {
            $this->data[$this->alias]['fee_shipping_threshold'] = mb_convert_kana($this->data[$this->alias]['fee_shipping_threshold'], 'n');
        }
        if (!empty($this->data[$this->alias]['default_shipping_fee'])) {
            $this->data[$this->alias]['default_shipping_fee'] = mb_convert_kana($this->data[$this->alias]['default_shipping_fee'], 'n');
        }

        return true;
    }

    public $validate = [
        'shop_name' => [
            'notBlank' => [
                'rule' => 'notBlank',
                'message' => 'ショップ名を入力してください'
            ]
        ],
    ];
}
