<?php

App::uses('AppModel', 'Model');

class ShippingFee extends AppModel
{
    public $useTable = 'shipping_fee';

    public $hasMany = [
        'Shop' => [
            'className' => 'Shop',
            'foreignKey' => 'default_shipping_fee',
        ]
    ];

    public function beforeValidate($options = array())
    {
        if (!empty($this->data[$this->alias]['shipping_fee'])) {
            $shipping_fee = $this->data[$this->alias]['shipping_fee'];
            $shipping_fee = mb_convert_kana($shipping_fee, 'n');
            $shipping_fee = str_replace(',', '', $shipping_fee);
            $this->data[$this->alias]['shipping_fee'] = $shipping_fee;
        }

        return true;
    }

    public $validate = [
        'shipping_fee_name' => [
            'notBlank' => [
                'rule' => 'notBlank',
                'message' => '設定名を入れてください'
            ]
        ],
        'shipping_fee' => [
            'notBlank' => [
                'rule' => 'notBlank',
                'message' => '送料を入れてください'
            ],
            'numeric' => [
                'rule' => 'numeric',
                'message' => '送料は半角数字で入力してください'
            ],
            'isUnique' => [
                'rule' => 'isUnique',
                'message' => '同じ送料はすでに登録されています'
            ]
        ],
    ];
}
