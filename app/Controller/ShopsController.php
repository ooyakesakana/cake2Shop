<?php
App::uses('AppController', 'Controller');

class ShopsController extends AppController
{
    public function index()
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', 'ショップ管理メニュー');
    }

    public function main()
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', 'ショップ管理メニュー');
    }

    public function add()
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', 'ショップ管理メニュー');

        $this->loadModel('ShippingFee');
        $shipping_fee = $this->ShippingFee->find('list', ['fields' => ['id', 'shipping_fee_name']]);
        $this->set('shipping_fee', $shipping_fee);

        if ($this->request->is('post')) {
            $this->Shop->create();
            if ($this->Shop->save($this->request->data['Shop'])) {
                $this->Session->setFlash('ショップを登録しました', 'default', [], 'success');
                return $this->redirect(['action' => 'add']);
            }
            $this->Session->setFlash('ショップの登録に失敗しました', 'default', [], 'error');
        }
    }

    public function shipping_fee()
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', 'ショップ管理メニュー');
        $this->loadModel('ShippingFee');

        $edit_id = (int)$this->request->query('edit_id');

        if ($this->request->is('post')) {
            $data = $this->request->data['ShippingFee'];

            if (!empty($this->request->data['update']) && !empty($data['id']) && $this->ShippingFee->exists($data['id'])) {
                $this->ShippingFee->id = (int)$data['id'];
                if ($this->ShippingFee->save($data)) {
                    $this->Session->setFlash('配送方法を更新しました', 'default', [], 'success');
                    return $this->redirect(['action' => 'shipping_fee']);
                }
                $this->Session->setFlash('配送方法の更新に失敗しました', 'default', [], 'error');
                $edit_id = (int)$data['id'];
            } else {
                $this->ShippingFee->create();
                if ($this->ShippingFee->save($data)) {
                    $this->Session->setFlash('配送方法を登録しました', 'default', [], 'success');
                    return $this->redirect(['action' => 'shipping_fee']);
                }
                $this->Session->setFlash('配送方法の登録に失敗しました', 'default', [], 'error');
            }
        }

        $shipping_fee_list = $this->ShippingFee->find('all', ['order' => ['ShippingFee.shipping_fee' => 'ASC']]);
        $this->set(compact('shipping_fee_list', 'edit_id'));
    }

    public function delete_shipping_fee($id = null)
    {
        $this->autoRender = false;
        $this->loadModel('ShippingFee');

        if ($this->request->is('post') && $this->ShippingFee->exists($id)) {
            if ($this->ShippingFee->delete($id)) {
                $this->Session->setFlash('削除しました', 'default', [], 'delete_msg');
            }
        } else {
            $this->Session->setFlash('削除できませんでした', 'default', [], 'delete_msg');
        }

        return $this->redirect(['controller' => 'Shops', 'action' => 'shipping_fee']);
    }
}
