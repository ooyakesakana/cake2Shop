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

        $shops = $this->Shop->find('all', [
            'order' => ['Shop.is_active' => 'DESC', 'Shop.shop_name' => 'ASC'],
            'recursive' => 0,
        ]);

        $this->set(compact('shops'));
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
            $this->request->data['Shop']['is_active'] = 1;
            if ($this->Shop->save($this->request->data['Shop'])) {
                $this->Session->setFlash('ショップを登録しました', 'default', [], 'success');
                return $this->redirect(['action' => 'add']);
            }
            $this->Session->setFlash('ショップの登録に失敗しました', 'default', [], 'error');
        }
    }

    public function edit($id = null)
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', 'ショップ編集');

        if (!$id || !$this->Shop->exists($id)) {
            throw new NotFoundException('ショップが見つかりません');
        }

        $this->loadModel('ShippingFee');
        $shipping_fee = $this->ShippingFee->find('list', ['fields' => ['id', 'shipping_fee_name']]);
        $this->set('shipping_fee', $shipping_fee);

        if ($this->request->is(['post', 'put'])) {
            $this->Shop->id = (int)$id;
            if ($this->Shop->save($this->request->data)) {
                $this->Session->setFlash('ショップ情報を更新しました', 'default', [], 'success');
                return $this->redirect(['action' => 'main']);
            }
            $this->Session->setFlash('ショップ情報の更新に失敗しました', 'default', [], 'error');
        } else {
            $this->request->data = $this->Shop->find('first', [
                'conditions' => ['Shop.shop_id' => (int)$id],
                'recursive' => -1,
            ]);
        }
    }

    public function deactivate($id = null)
    {
        $this->autoRender = false;

        if (!$id || !$this->request->is('post') || !$this->Shop->exists($id)) {
            $this->Session->setFlash('ショップを使用不可にできませんでした', 'default', [], 'error');
            return $this->redirect(['action' => 'main']);
        }

        $this->Shop->id = (int)$id;
        if ($this->Shop->saveField('is_active', 0)) {
            $this->Session->setFlash('ショップを使用不可にしました。過去の売上データは保持されます。', 'default', [], 'success');
        } else {
            $this->Session->setFlash('ショップを使用不可にできませんでした', 'default', [], 'error');
        }

        return $this->redirect(['action' => 'main']);
    }

    public function activate($id = null)
    {
        $this->autoRender = false;

        if (!$id || !$this->request->is('post') || !$this->Shop->exists($id)) {
            $this->Session->setFlash('ショップを再開できませんでした', 'default', [], 'error');
            return $this->redirect(['action' => 'main']);
        }

        $this->Shop->id = (int)$id;
        if ($this->Shop->saveField('is_active', 1)) {
            $this->Session->setFlash('ショップを使用可能にしました', 'default', [], 'success');
        } else {
            $this->Session->setFlash('ショップを再開できませんでした', 'default', [], 'error');
        }

        return $this->redirect(['action' => 'main']);
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
