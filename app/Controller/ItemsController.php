<?php

App::uses('AppController', 'Controller');
class ItemsController extends AppController
{
    public function index()
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', '商品管理メニュー');
    }

    public function main()
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', '商品検索・一覧');

        // 検索フォーム初期値
        $conditions = [];
        $keyword = '';
        $category = '';
        $stockCompare = '';
        $stockValue = '';

        // カテゴリ候補を取得
        $categoryList = $this->Item->find('list', [
            'fields' => ['Item.category', 'Item.category'],
            'group' => ['Item.category'],
            'order' => ['Item.category' => 'ASC'],
            'recursive' => -1,
        ]);

        // GETパラメータで検索条件を受け取る
        if (!empty($this->request->query)) {
            $keyword = trim((string)$this->request->query('keyword'));
            $category = trim((string)$this->request->query('category'));
            $stockCompare = trim((string)$this->request->query('stock_compare'));
            $stockValue = trim((string)$this->request->query('stock_value'));

            if ($keyword !== '') {
                $conditions['Item.item_name LIKE'] = '%' . $keyword . '%';
            }
            if ($category !== '') {
                $conditions['Item.category'] = $category;
            }
        }

        // 商品一覧を取得
        $items = $this->Item->find('all', [
            'conditions' => $conditions,
            'order' => ['Item.item_code' => 'ASC'],
            'recursive' => -1,
        ]);


        // 商品サムネイルを取得（なければ sample.jpg）
        $thumbMap = [];
        $sources = $this->Item->getDataSource()->listSources();
        if (in_array('item_images', $sources, true)) {
            $this->loadModel('ItemImage');
            $imgRows = $this->ItemImage->find('all', [
                'fields' => ['ItemImage.item_code', 'ItemImage.file_name'],
                'conditions' => ['ItemImage.image_order' => 1],
                'recursive' => -1,
            ]);
            foreach ($imgRows as $r) {
                $thumbMap[$r['ItemImage']['item_code']] = $r['ItemImage']['file_name'];
            }
        }

        // 在庫合計を商品ごとに引く（ショップ登録在庫）
        $this->loadModel('ShopInventory');
        $stockRows = $this->ShopInventory->find('all', [
            'fields' => ['ShopInventory.item_code', 'SUM(ShopInventory.stock_quantity) AS total_stock'],
            'group' => ['ShopInventory.item_code'],
            'recursive' => -1,
        ]);
        $stockMap = [];
        foreach ($stockRows as $row) {
            $stockMap[$row['ShopInventory']['item_code']] = (float)$row[0]['total_stock'];
        }

        // ショップ別在庫マップ（画面2段目の直接更新用）
        $shopStockMap = [];
        $shopStockRows = $this->ShopInventory->find('all', [
            'fields' => ['ShopInventory.item_code', 'ShopInventory.shop_id', 'ShopInventory.stock_quantity'],
            'recursive' => -1,
        ]);
        foreach ($shopStockRows as $row) {
            $code = $row['ShopInventory']['item_code'];
            $sid = (int)$row['ShopInventory']['shop_id'];
            $shopStockMap[$code][$sid] = (float)$row['ShopInventory']['stock_quantity'];
        }

        // ロット在庫合計（商品化/仕入由来）
        $lotMap = [];
        $avgCostMap = [];
        if (in_array('inventory_lots', $sources, true)) {
            $this->loadModel('InventoryLot');
            $lotRows = $this->InventoryLot->find('all', [
                'fields' => ['InventoryLot.item_code', 'SUM(InventoryLot.remaining_qty) AS lot_total'],
                'group' => ['InventoryLot.item_code'],
                'recursive' => -1,
            ]);
            foreach ($lotRows as $row) {
                $lotMap[$row['InventoryLot']['item_code']] = (float)$row[0]['lot_total'];
            }

            $avgCostRows = $this->InventoryLot->find('all', [
                'fields' => ['InventoryLot.item_code', 'SUM(InventoryLot.remaining_qty * InventoryLot.unit_cost) AS total_cost', 'SUM(InventoryLot.remaining_qty) AS total_qty'],
                'group' => ['InventoryLot.item_code'],
                'recursive' => -1,
            ]);
            foreach ($avgCostRows as $row) {
                $qty = (float)$row[0]['total_qty'];
                $avgCostMap[$row['InventoryLot']['item_code']] = $qty > 0 ? ((float)$row[0]['total_cost'] / $qty) : 0;
            }
        }

        // 在庫比較条件でPHP側フィルタ（DB変更に強くするため簡易実装）
        $resultItems = [];
        foreach ($items as $item) {
            $code = $item['Item']['item_code'];
            $registeredStock = isset($stockMap[$code]) ? $stockMap[$code] : 0;
            $lotStock = isset($lotMap[$code]) ? $lotMap[$code] : 0;
            $item['Item']['registered_stock'] = $registeredStock;
            $item['Item']['lot_total_stock'] = $lotStock;
            $item['Item']['free_stock'] = $lotStock - $registeredStock;
            $item['Item']['total_stock'] = $lotStock;
            $item['Item']['thumb_image'] = $thumbMap[$code] ?? 'sample.png';
            $item['Item']['shop_stock_map'] = isset($shopStockMap[$code]) ? $shopStockMap[$code] : [];
            $item['Item']['is_total_low'] = ((float)$lotStock <= 5);
            $item['Item']['avg_cost'] = isset($avgCostMap[$code]) ? (float)$avgCostMap[$code] : 0;

            $ok = true;
            if ($stockCompare !== '' && $stockValue !== '' && is_numeric($stockValue)) {
                $sv = (int)$stockValue;
                $stock = (int)$item['Item']['total_stock'];
                if ($stockCompare === 'gte') {
                    $ok = ($stock >= $sv);
                } elseif ($stockCompare === 'lte') {
                    $ok = ($stock <= $sv);
                } elseif ($stockCompare === 'eq') {
                    $ok = ($stock === $sv);
                }
            }
            if ($ok) {
                $resultItems[] = $item;
            }
        }

        $this->loadModel('Shop');
        $shops = $this->Shop->find('all', [
            'fields' => ['Shop.shop_id', 'Shop.shop_name', 'Shop.fee_percent', 'Shop.is_shipping_included'],
            'conditions' => ['Shop.is_active' => 1],
            'order' => ['Shop.shop_name' => 'ASC'],
            'recursive' => -1,
            'limit' => 4,
        ]);
        $shopIds = Hash::extract($shops, '{n}.Shop.shop_id');

        $this->loadModel('ShopItemPrice');
        $priceMap = [];
        if (!empty($shopIds)) {
            $priceRows = $this->ShopItemPrice->find('all', [
                'fields' => ['ShopItemPrice.shop_id', 'ShopItemPrice.item_code', 'ShopItemPrice.sale_price', 'ShopItemPrice.margin_rate'],
                'conditions' => ['ShopItemPrice.shop_id' => $shopIds],
                'recursive' => -1,
            ]);
            foreach ($priceRows as $pr) {
                $sid = (int)$pr['ShopItemPrice']['shop_id'];
                $code = $pr['ShopItemPrice']['item_code'];
                $priceMap[$code][$sid] = $pr['ShopItemPrice'];
            }
        }

        $lowStockThreshold = 5;
        $this->set(compact('resultItems', 'categoryList', 'keyword', 'category', 'stockCompare', 'stockValue', 'shops', 'lowStockThreshold', 'priceMap'));
    }

    public function add()
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', '商品登録');

        // 商品情報登録用フォーム
        $category = $this->Item->find('list', [
            'fields' => ['Item.category', 'Item.category'],
            'group' => ['Item.category'],
            'order' => ['Item.category' => 'ASC'],
            'recursive' => -1,
        ]);
        $this->set('category', $category);

        // 商品登録時
        if ($this->request->is('post')) {
            $input = $this->request->data['Item'];

            // カテゴリは入力テキスト優先
            $select = trim((string)$input['category_select']);
            $text   = trim((string)$input['category_text']);
            if ($text !== '') {
                $input['category'] = $text;
            } elseif ($select !== '') {
                $input['category'] = $select;
            }
            unset($input['category_select'], $input['category_text']);

            // 入力項目のバリデーション
            $this->Item->create();
            $this->Item->set($input);
            if (!$this->Item->validates()) {
                $this->Session->setFlash('商品の登録に失敗しました', 'default', [], 'errMsg');
                return;
            }

            // 画像アップロード（任意）
            $uploadDir = WWW_ROOT . 'img' . DS . 'items' . DS;
            $savedFileName = 'sample.png';
            $upload = $input['main_image'];

            if (isset($upload['error']) && $upload['error'] === UPLOAD_ERR_OK && !empty($upload['tmp_name'])) {
                $ext = strtolower(pathinfo($upload['name'], PATHINFO_EXTENSION));
                $allowExt = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                if (!in_array($ext, $allowExt, true)) {
                    $this->Item->validationErrors['main_image'][] = '拡張子が不正です';
                    return;
                }
                $savedFileName = $input['item_code'] . '.' . $ext;
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                if (!move_uploaded_file($upload['tmp_name'], $uploadDir . $savedFileName)) {
                    $this->Item->validationErrors['main_image'][] = '画像の保存に失敗しました';
                    return;
                }
            }

            $input['main_image'] = $savedFileName;
            if ($this->Item->save($input)) {
                // 追加画像の保存（2枚目以降は 商品コード_2.jpg 形式）
                $this->loadModel('ItemImage');
                $this->ItemImage->create();
                $this->ItemImage->save(['item_code' => $input['item_code'], 'file_name' => $savedFileName, 'image_order' => 1]);
                if (!empty($this->request->data['Item']['sub_images']) && is_array($this->request->data['Item']['sub_images'])) {
                    $order = 2;
                    foreach ($this->request->data['Item']['sub_images'] as $sub) {
                        if (empty($sub['tmp_name']) || (int)$sub['error'] !== UPLOAD_ERR_OK) {
                            continue;
                        }
                        $ext = strtolower(pathinfo($sub['name'], PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                            continue;
                        }
                        $subName = $input['item_code'] . '_' . $order . '.' . $ext;
                        if (move_uploaded_file($sub['tmp_name'], $uploadDir . $subName)) {
                            $this->ItemImage->create();
                            $this->ItemImage->save(['item_code' => $input['item_code'], 'file_name' => $subName, 'image_order' => $order]);
                            $order++;
                        }
                    }
                }
                // 登録完了後、在庫登録へ進める画面に遷移
                return $this->redirect(['action' => 'add_complete', $this->Item->id]);
            }
            $this->Session->setFlash('商品の登録に失敗しました', 'default', [], 'errMsg');
        }
    }

    public function add_complete($id = null)
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', '商品登録完了');

        if (!$id || !$this->Item->exists($id)) {
            throw new NotFoundException('商品が見つかりません');
        }

        $item = $this->Item->find('first', ['conditions' => ['Item.id' => $id], 'recursive' => -1]);
        $this->loadModel('Shop');
        $this->loadModel('ShopInventory');
        $this->loadModel('ShopItemPrice');

        // 価格/在庫の続けて登録
        if ($this->request->is('post')) {
            if (!empty($this->request->data['skip'])) {
                return $this->redirect(['action' => 'add']);
            }
            $d = $this->request->data['ShopEntry'];
            if (!empty($d['shop_id'])) {
                $price = (float)$d['sale_price'];
                $cost = (float)($item['Item']['base_price'] ?? 0);
                $shop = $this->Shop->find('first', [
                    'conditions' => ['Shop.shop_id' => (int)$d['shop_id']],
                    'fields' => ['Shop.fee_percent'],
                    'recursive' => -1,
                ]);
                $feePercent = $shop ? (float)$shop['Shop']['fee_percent'] : 0;
                $feeAmount = $price * ($feePercent / 100);
                $marginRate = ($price > 0) ? (($price - $feeAmount - $cost) / $price) * 100 : 0;

                $existingPrice = $this->ShopItemPrice->find('first', ['conditions' => ['shop_id' => $d['shop_id'], 'item_code' => $item['Item']['item_code']], 'recursive' => -1]);
                if ($existingPrice) {
                    $this->ShopItemPrice->id = $existingPrice['ShopItemPrice']['id'];
                } else {
                    $this->ShopItemPrice->create();
                }
                $this->ShopItemPrice->save(['shop_id' => $d['shop_id'], 'item_code' => $item['Item']['item_code'], 'sale_price' => $price, 'margin_rate' => $marginRate]);

                if ($d['stock_quantity'] !== '') {
                    $existingInv = $this->ShopInventory->find('first', ['conditions' => ['shop_id' => $d['shop_id'], 'item_code' => $item['Item']['item_code']], 'recursive' => -1]);
                    if ($existingInv) {
                        $this->ShopInventory->id = $existingInv['ShopInventory']['id'];
                    } else {
                        $this->ShopInventory->create();
                    }
                    $this->ShopInventory->save(['shop_id' => $d['shop_id'], 'item_code' => $item['Item']['item_code'], 'stock_quantity' => (float)$d['stock_quantity']]);
                }

                $this->Session->setFlash('ショップ価格/在庫を登録しました', 'default', [], 'success');
                return $this->redirect(['action' => 'add_complete', $id]);
            }
        }

        // 登録済みショップ一覧
        $shops = $this->Shop->find('all', ['fields' => ['Shop.shop_id', 'Shop.shop_name', 'Shop.fee_percent', 'Shop.is_shipping_included'], 'conditions' => ['Shop.is_active' => 1], 'order' => ['Shop.shop_name' => 'ASC'], 'recursive' => -1]);

        // この商品のショップ別在庫を連想配列に
        $invRows = $this->ShopInventory->find('all', [
            'conditions' => ['ShopInventory.item_code' => $item['Item']['item_code']],
            'recursive' => -1,
        ]);
        $invMap = [];
        foreach ($invRows as $r) {
            $invMap[$r['ShopInventory']['shop_id']] = (int)$r['ShopInventory']['stock_quantity'];
        }

        $priceRows = $this->ShopItemPrice->find('all', ['conditions' => ['item_code' => $item['Item']['item_code']], 'recursive' => -1]);
        $priceMap = [];
        foreach ($priceRows as $pr) {
            $priceMap[$pr['ShopItemPrice']['shop_id']] = $pr['ShopItemPrice'];
        }

        $this->set(compact('item', 'shops', 'invMap', 'priceMap'));
    }

    public function edit($id = null)
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', '商品編集');

        if (!$id || !$this->Item->exists($id)) {
            throw new NotFoundException('商品が見つかりません');
        }

        if ($this->request->is(['post', 'put'])) {
            $this->Item->id = $id;
            if ($this->Item->save($this->request->data)) {
                $this->Session->setFlash('商品情報を更新しました', 'default', [], 'success');
                return $this->redirect(['action' => 'main']);
            }
            $this->Session->setFlash('商品情報の更新に失敗しました', 'default', [], 'errMsg');
        } else {
            $this->request->data = $this->Item->find('first', ['conditions' => ['Item.id' => $id], 'recursive' => -1]);
        }
    }

    public function inventory()
    { /* 既存 */
        $this->layout = 'layout';
        $this->set('titleForLayout', '在庫登録');
        $this->loadModel('Shop');
        $this->loadModel('ShopInventory');
        $shops = $this->Shop->find('list', ['fields' => ['Shop.shop_id', 'Shop.shop_name'], 'conditions' => ['Shop.is_active' => 1]]);
        $items = $this->Item->find('list', ['fields' => ['Item.item_code', 'Item.item_name']]);
        $selectedShopId = $this->request->query('shop_id');
        $selectedItemCode = $this->request->query('item_code');
        $this->set(compact('shops', 'items', 'selectedShopId', 'selectedItemCode'));
        if ($this->request->is('post')) {
            $data = $this->request->data['ShopInventory'];
            $existing = $this->ShopInventory->find('first', ['conditions' => ['shop_id' => $data['shop_id'], 'item_code' => $data['item_code']], 'recursive' => -1]);
            if ($existing) {
                $this->ShopInventory->id = $existing['ShopInventory']['id'];
            } else {
                $this->ShopInventory->create();
            }
            if ($this->ShopInventory->save($data)) {
                $this->Session->setFlash('在庫を登録しました', 'default', [], 'success');
                return $this->redirect(['action' => 'inventory']);
            }
            $this->Session->setFlash('在庫登録に失敗しました', 'default', [], 'errMsg');
        }
        $inventoryList = $this->ShopInventory->find('all', ['recursive' => 0, 'order' => ['Shop.shop_name' => 'ASC', 'Item.item_code' => 'ASC']]);
        $this->set('inventoryList', $inventoryList);
    }

    public function quick_update_stock()
    {
        $this->autoRender = false;
        if (!$this->request->is('post')) {
            return $this->redirect(['action' => 'main']);
        }
        $this->loadModel('ShopInventory');
        $itemCode = $this->request->data('item_code');
        $stockQty = (int)$this->request->data('stock_quantity');
        $shopId = (int)$this->request->data('shop_id');
        if (!$itemCode || !$shopId) {
            $this->Session->setFlash('在庫更新に失敗しました', 'default', [], 'errMsg');
            return $this->redirect(['action' => 'main']);
        }
        $existing = $this->ShopInventory->find('first', ['conditions' => ['shop_id' => $shopId, 'item_code' => $itemCode], 'recursive' => -1]);
        if ($existing) {
            $this->ShopInventory->id = $existing['ShopInventory']['id'];
        } else {
            $this->ShopInventory->create();
        }
        if ($this->ShopInventory->save(['shop_id' => $shopId, 'item_code' => $itemCode, 'stock_quantity' => $stockQty])) {
            $this->Session->setFlash('在庫数を更新しました', 'default', [], 'success');
        }
        return $this->redirect(['action' => 'main']);
    }


    public function stock_adjustment()
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', '在庫調整（初期不良/返金対応）');
        $this->loadModel('Item');
        $this->loadModel('Shop');
        $this->loadModel('ShopInventory');
        $this->loadModel('InventoryLot');
        $this->loadModel('InventoryAdjustment');
        $this->loadModel('Expense');

        $items = $this->Item->find('list', ['fields' => ['Item.item_code', 'Item.item_name']]);
        $shops = $this->Shop->find('list', ['fields' => ['Shop.shop_id', 'Shop.shop_name'], 'conditions' => ['Shop.is_active' => 1]]);
        $selectedItemCode = $this->request->query('item_code');

        if ($this->request->is('post')) {
            $d = $this->request->data['InventoryAdjustment'];
            $qty = (float)$d['adjust_qty'];
            $itemCode = $d['item_code'];
            $shopId = !empty($d['shop_id']) ? (int)$d['shop_id'] : null;

            $registered = (float)$this->ShopInventory->find('first', ['fields' => ['SUM(stock_quantity) AS s'], 'conditions' => ['item_code' => $itemCode], 'recursive' => -1])[0]['s'];
            $lotTotal = (float)$this->InventoryLot->find('first', ['fields' => ['SUM(remaining_qty) AS s'], 'conditions' => ['item_code' => $itemCode], 'recursive' => -1])[0]['s'];
            $free = $lotTotal - $registered;

            if ($shopId === null && $free < $qty) {
                $this->Session->setFlash('未登録在庫が不足しています。ショップ在庫から減算するショップを選んでください。', 'default', [], 'errMsg');
                return $this->redirect(['action' => 'stock_adjustment', '?' => ['item_code' => $itemCode]]);
            }

            if ($shopId !== null) {
                $row = $this->ShopInventory->find('first', ['conditions' => ['shop_id' => $shopId, 'item_code' => $itemCode], 'recursive' => -1]);
                if (!$row || (float)$row['ShopInventory']['stock_quantity'] < $qty) {
                    $this->Session->setFlash('指定ショップ在庫が不足しています。', 'default', [], 'errMsg');
                    return $this->redirect(['action' => 'stock_adjustment', '?' => ['item_code' => $itemCode]]);
                }
                $this->ShopInventory->id = $row['ShopInventory']['id'];
                $this->ShopInventory->saveField('stock_quantity', (float)$row['ShopInventory']['stock_quantity'] - $qty);
            }

            // ロット在庫をFIFO減算
            $need = $qty;
            $lots = $this->InventoryLot->find('all', ['conditions' => ['item_code' => $itemCode, 'remaining_qty >' => 0], 'order' => ['id' => 'ASC'], 'recursive' => -1]);
            $cost = 0.0;
            foreach ($lots as $lot) {
                if ($need <= 0) break;
                $rem = (float)$lot['InventoryLot']['remaining_qty'];
                $use = min($need, $rem);
                if ($use <= 0) continue;
                $cost += $use * (float)$lot['InventoryLot']['unit_cost'];
                $need -= $use;
                $this->InventoryLot->id = $lot['InventoryLot']['id'];
                $this->InventoryLot->saveField('remaining_qty', $rem - $use);
            }

            $this->InventoryAdjustment->create();
            $this->InventoryAdjustment->save(['item_code' => $itemCode, 'shop_id' => $shopId, 'adjust_qty' => $qty, 'reason' => $d['reason'], 'memo' => $d['memo'], 'cost_amount' => $cost, 'shipping_loss' => (float)$d['shipping_loss']]);

            // 費用計上（棚卸減耗＋再送料）
            $this->Expense->create();
            $this->Expense->save(['category_name' => '初期不良/返金対応', 'tax_account_name' => '雑損失', 'amount' => $cost + (float)$d['shipping_loss'], 'memo' => $d['memo']]);

            $this->Session->setFlash('在庫調整と費用計上を登録しました', 'default', [], 'success');
            return $this->redirect(['action' => 'stock_adjustment']);
        }

        $shopStockList = [];
        if ($selectedItemCode) {
            $shopStockList = $this->ShopInventory->find('all', ['conditions' => ['item_code' => $selectedItemCode], 'recursive' => 0]);
        }
        $this->set(compact('items', 'shops', 'selectedItemCode', 'shopStockList'));
    }
}
