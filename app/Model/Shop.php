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
        if (!empty($this->data[$this->alias]['platform_code'])) {
            $platform_code = $this->data[$this->alias]['platform_code'];
            $platform_code = mb_convert_kana($platform_code, 'as');
        }
        if (!empty($this->data[$this->alias]['fee_percent'])) {
            $fee_percent = $this->data[$this->alias]['fee_percent'];
            $fee_percent = mb_convert_kana($fee_percent, 'n');
        }
        if (!empty($this->data[$this->alias]['fee_shipping_threshold'])) {
            $fee_shipping_threshold = $this->data[$this->alias]['fee_sipping_threshold'];
            $fee_shipping_threshold = mb_convert_kana($fee_shipping_threshold, 'n');
        }
        if (!empty($this->data[$this->alias]['default_shipping_fee'])) {
            $default_shipping_fee = $this->data[$this->alias]['default_shipping_fee'];
            $default_shipping_fee = mb_convert_kana($default_shipping_fee, 'n');
        }

        return true;
    }

    public $validate = [
        'shop_name' => [
            'notBlank' => [
                'rule' => 'notEmpty',
                'message' => 'ショップ名を入力してください'
            ]
        ],
        'platform_code' => [
            'notBlank' => [
                'rule' => 'notEmpty',
                'message' => 'ショップ管理コードを入力してください'
            ]
        ],
    ];
}
