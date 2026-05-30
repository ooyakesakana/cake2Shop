<?php
App::uses('AppController', 'Controller');
App::uses('Folder', 'Utility');

class ExpensesController extends AppController
{

    public function index()
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', '経費管理メニュー');
        $this->loadModel('ExpenseCategory');

        if (!in_array('attachments', $this->Expense->getDataSource()->listSources(), true)) {
            $this->Expense->unbindModel(['hasMany' => ['Attachment']], false);
        }

        $categoryOptions = $this->ExpenseCategory->find('list', [
            'fields' => ['ExpenseCategory.category_name', 'ExpenseCategory.category_name'],
            'order' => ['ExpenseCategory.category_name' => 'ASC'],
            'recursive' => -1,
        ]);
        $expenseStatuses = [
            'ordered' => '注文済',
            'paid' => '支払済',
            'received' => '到着済',
            'active' => '使用開始',
        ];
        $dateFrom = trim((string)$this->request->query('date_from'));
        $dateTo = trim((string)$this->request->query('date_to'));
        $category = trim((string)$this->request->query('category'));
        $status = trim((string)$this->request->query('status'));
        $depreciation = trim((string)$this->request->query('depreciation'));
        $keyword = trim((string)$this->request->query('keyword'));

        $conditions = [];
        if ($dateFrom !== '') {
            $conditions['Expense.expense_date >='] = $dateFrom;
        }
        if ($dateTo !== '') {
            $conditions['Expense.expense_date <='] = $dateTo;
        }
        if ($category !== '') {
            $conditions['Expense.category_name'] = $category;
        }
        if ($status !== '') {
            $conditions['Expense.status'] = $status;
        }
        if ($depreciation === '1') {
            $conditions['Expense.is_depreciation'] = 1;
        } elseif ($depreciation === '0') {
            $conditions['Expense.is_depreciation'] = 0;
        } elseif ($depreciation === 'pending') {
            $conditions['Expense.is_depreciation'] = 1;
            $conditions['Expense.status !='] = 'active';
        }
        if ($keyword !== '') {
            $conditions['OR'] = [
                'Expense.vendor_name LIKE' => '%' . $keyword . '%',
                'Expense.description LIKE' => '%' . $keyword . '%',
                'Expense.memo LIKE' => '%' . $keyword . '%',
            ];
        }

        $expenses = $this->Expense->find('all', [
            'conditions' => $conditions,
            'order' => ['Expense.expense_date' => 'DESC', 'Expense.id' => 'DESC'],
            'recursive' => 1,
        ]);
        $this->set(compact('expenses', 'categoryOptions', 'expenseStatuses', 'dateFrom', 'dateTo', 'category', 'status', 'depreciation', 'keyword'));
    }

    public function main()
    {
        return $this->redirect(['action' => 'index']);
    }

    public function add()
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', '経費登録');

        $this->loadModel('ExpenseCategory');
        $this->loadModel('Attachment');

        $categoryFields = array_keys($this->ExpenseCategory->schema());
        $categoryFindOptions = [
            'order' => ['ExpenseCategory.category_name' => 'ASC'],
            'recursive' => -1,
        ];
        if (in_array('is_active', $categoryFields, true)) {
            $categoryFindOptions['conditions'] = ['ExpenseCategory.is_active' => 1];
        }
        if (in_array('sort_order', $categoryFields, true)) {
            $categoryFindOptions['order'] = ['ExpenseCategory.sort_order' => 'ASC', 'ExpenseCategory.category_name' => 'ASC'];
        }
        $expenseCategories = $this->ExpenseCategory->find('all', $categoryFindOptions);
        $categoryOptions = [];
        $categoryMeta = [];
        foreach ($expenseCategories as $category) {
            $name = $category['ExpenseCategory']['category_name'];
            $categoryOptions[$name] = $name;
            $categoryMeta[$name] = [
                'accounting_type' => $category['ExpenseCategory']['default_accounting_type'] ?? null,
                'tax_account_name' => $category['ExpenseCategory']['tax_account_name'] ?? null,
                'is_asset_candidate' => (int)($category['ExpenseCategory']['is_asset_candidate'] ?? 0),
            ];
        }
        $expenseStatuses = [
            'ordered' => '注文済',
            'paid' => '支払済',
            'received' => '到着済',
            'active' => '使用開始',
        ];
        $this->set(compact('categoryOptions', 'categoryMeta', 'expenseStatuses'));

        if ($this->request->is('post')) {
            $data = $this->request->data['Expense'];
            unset($data['category_select']);
            if (empty($data['expense_date'])) {
                $data['expense_date'] = date('Y-m-d');
            }
            if (trim((string)($data['amount'] ?? '')) === '') {
                $this->Session->setFlash('実支払額を入力してください。', 'default', [], 'errMsg');
                return;
            }
            $this->_normalizeExpenseAmounts($data);
            $data['business_use_rate'] = min(100, max(0, (float)($data['business_use_rate'] ?? 100)));
            $data['is_depreciation'] = !empty($data['is_depreciation']) ? 1 : 0;
            if (empty($data['status'])) {
                $data['status'] = 'paid';
            }

            if (!empty($data['is_depreciation']) && $data['status'] === 'active' && empty($data['use_start_date'])) {
                $this->Session->setFlash('減価償却対象を使用開始にする場合は使用開始日を入力してください。', 'default', [], 'errMsg');
                return;
            }

            $categoryName = trim((string)($data['category_name'] ?? ''));
            if ($categoryName !== '') {
                $category = $this->ExpenseCategory->find('first', [
                    'conditions' => ['ExpenseCategory.category_name' => $categoryName],
                    'recursive' => -1,
                ]);
                if ($category) {
                    $data['expense_category_id'] = $category['ExpenseCategory']['id'];
                    $data['tax_account_name'] = $category['ExpenseCategory']['tax_account_name'] ?? null;
                    $data['accounting_type'] = $category['ExpenseCategory']['default_accounting_type'] ?? ($category['ExpenseCategory']['tax_account_name'] ?? null);
                } else {
                    $this->ExpenseCategory->create();
                    if ($this->ExpenseCategory->save(['category_name' => $categoryName, 'is_active' => 1])) {
                        $data['expense_category_id'] = $this->ExpenseCategory->id;
                    }
                }
            }

            $this->Expense->create();
            if ($this->Expense->save($data)) {
                $this->_saveAttachments($this->Expense->id);
                $this->Session->setFlash('経費を登録しました', 'default', [], 'success');
                return $this->redirect(['action' => 'index']);
            }
            $this->Session->setFlash('経費登録に失敗しました', 'default', [], 'errMsg');
        }
    }

    public function edit($id = null)
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', '経費編集');
        $this->loadModel('ExpenseCategory');
        $this->loadModel('Attachment');

        $expense = $this->Expense->find('first', [
            'conditions' => ['Expense.id' => $id],
            'recursive' => 1,
        ]);
        if (!$expense) {
            throw new NotFoundException('経費データが見つかりません。');
        }

        $categoryFields = array_keys($this->ExpenseCategory->schema());
        $categoryFindOptions = [
            'order' => ['ExpenseCategory.category_name' => 'ASC'],
            'recursive' => -1,
        ];
        if (in_array('is_active', $categoryFields, true)) {
            $categoryFindOptions['conditions'] = ['ExpenseCategory.is_active' => 1];
        }
        if (in_array('sort_order', $categoryFields, true)) {
            $categoryFindOptions['order'] = ['ExpenseCategory.sort_order' => 'ASC', 'ExpenseCategory.category_name' => 'ASC'];
        }
        $expenseCategories = $this->ExpenseCategory->find('all', $categoryFindOptions);
        $categoryOptions = [];
        $categoryMeta = [];
        foreach ($expenseCategories as $category) {
            $name = $category['ExpenseCategory']['category_name'];
            $categoryOptions[$name] = $name;
            $categoryMeta[$name] = [
                'accounting_type' => $category['ExpenseCategory']['default_accounting_type'] ?? null,
                'tax_account_name' => $category['ExpenseCategory']['tax_account_name'] ?? null,
                'is_asset_candidate' => (int)($category['ExpenseCategory']['is_asset_candidate'] ?? 0),
            ];
        }
        $expenseStatuses = [
            'ordered' => '注文済',
            'paid' => '支払済',
            'received' => '到着済',
            'active' => '使用開始',
        ];

        if ($this->request->is(['post', 'put'])) {
            $data = $this->request->data['Expense'];
            unset($data['category_select']);
            if (trim((string)($data['amount'] ?? '')) === '') {
                $this->Session->setFlash('実支払額を入力してください。', 'default', [], 'errMsg');
                return $this->redirect(['action' => 'edit', $id]);
            }
            $this->_normalizeExpenseAmounts($data);
            $data['business_use_rate'] = min(100, max(0, (float)($data['business_use_rate'] ?? 100)));
            $data['is_depreciation'] = !empty($data['is_depreciation']) ? 1 : 0;
            if (empty($data['status'])) {
                $data['status'] = 'paid';
            }

            if (!empty($data['is_depreciation']) && $data['status'] === 'active' && empty($data['use_start_date'])) {
                $this->Session->setFlash('減価償却対象を使用開始にする場合は使用開始日を入力してください。', 'default', [], 'errMsg');
                return $this->redirect(['action' => 'edit', $id]);
            }

            $categoryName = trim((string)($data['category_name'] ?? ''));
            if ($categoryName !== '') {
                $category = $this->ExpenseCategory->find('first', [
                    'conditions' => ['ExpenseCategory.category_name' => $categoryName],
                    'recursive' => -1,
                ]);
                if ($category) {
                    $data['expense_category_id'] = $category['ExpenseCategory']['id'];
                    $data['tax_account_name'] = $category['ExpenseCategory']['tax_account_name'] ?? null;
                    $data['accounting_type'] = $category['ExpenseCategory']['default_accounting_type'] ?? ($category['ExpenseCategory']['tax_account_name'] ?? null);
                } else {
                    $this->ExpenseCategory->create();
                    if ($this->ExpenseCategory->save(['category_name' => $categoryName, 'is_active' => 1])) {
                        $data['expense_category_id'] = $this->ExpenseCategory->id;
                    }
                }
            }

            $this->Expense->id = $id;
            if ($this->Expense->save($data)) {
                $this->_saveAttachments($id);
                $this->Session->setFlash('経費を更新しました', 'default', [], 'success');
                return $this->redirect(['action' => 'index']);
            }
            $this->Session->setFlash('経費更新に失敗しました', 'default', [], 'errMsg');
        }

        $this->request->data = $expense;
        $this->set(compact('expense', 'categoryOptions', 'categoryMeta', 'expenseStatuses'));
    }

    public function delete_attachment($id = null)
    {
        $this->autoRender = false;
        $this->loadModel('Attachment');

        if (!$this->request->is('post')) {
            throw new MethodNotAllowedException('不正なリクエストです。');
        }

        $attachment = $this->Attachment->find('first', [
            'conditions' => ['Attachment.id' => $id, 'Attachment.target_type' => 'expense'],
            'recursive' => -1,
        ]);
        if (!$attachment) {
            throw new NotFoundException('証憑ファイルが見つかりません。');
        }

        $expenseId = (int)$attachment['Attachment']['target_id'];
        $path = WWW_ROOT . str_replace(['/', '\\'], DS, $attachment['Attachment']['file_path']);

        $this->Attachment->id = $id;
        if ($this->Attachment->delete()) {
            if (is_file($path)) {
                @unlink($path);
            }
            $this->Session->setFlash('証憑ファイルを削除しました。', 'default', [], 'success');
        } else {
            $this->Session->setFlash('証憑ファイルの削除に失敗しました。', 'default', [], 'errMsg');
        }

        return $this->redirect(['action' => 'edit', $expenseId]);
    }

    private function _normalizeExpenseAmounts(&$data)
    {
        $fields = ['amount', 'gross_amount', 'coupon_discount_amount', 'point_used_amount'];
        foreach ($fields as $field) {
            $raw = trim((string)($data[$field] ?? ''));
            if ($raw === '') {
                $data[$field] = null;
                continue;
            }
            $amount = (float)str_replace(',', '', mb_convert_kana($raw, 'n'));
            $data[$field] = max(0, $amount);
        }
        if ($data['amount'] === null) {
            $data['amount'] = 0;
        }
    }

    private function _saveAttachments($expenseId)
    {
        if (empty($this->request->data['Attachment']['files'])) {
            return;
        }
        if (!in_array('attachments', $this->Expense->getDataSource()->listSources(), true)) {
            return;
        }

        $files = $this->_normalizeFiles($this->request->data['Attachment']['files']);
        $dir = WWW_ROOT . 'files' . DS . 'attachments' . DS . 'expenses' . DS;
        $folder = new Folder($dir, true, 0755);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];

        foreach ($files as $file) {
            if (empty($file['tmp_name']) || !empty($file['error'])) {
                continue;
            }
            $originalName = basename((string)$file['name']);
            $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions, true)) {
                continue;
            }
            $fileName = 'expense_' . $expenseId . '_' . date('YmdHis') . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $targetPath = $dir . $fileName;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                continue;
            }
            $this->Attachment->create();
            $this->Attachment->save([
                'target_type' => 'expense',
                'target_id' => $expenseId,
                'file_name' => $fileName,
                'original_name' => $originalName,
                'file_path' => 'files/attachments/expenses/' . $fileName,
                'mime_type' => $file['type'] ?? null,
                'file_size' => (int)($file['size'] ?? 0),
                'memo' => trim((string)($this->request->data['Attachment']['memo'] ?? '')),
            ]);
        }
    }

    private function _normalizeFiles($files)
    {
        if (!is_array($files)) {
            return [];
        }
        if (!isset($files['name'])) {
            $normalized = [];
            foreach ($files as $file) {
                if (is_array($file) && isset($file['name'])) {
                    $normalized[] = $file;
                }
            }
            return $normalized;
        }
        if (!is_array($files['name'])) {
            return [$files];
        }

        $normalized = [];
        foreach ($files['name'] as $idx => $name) {
            $normalized[] = [
                'name' => $name,
                'type' => $files['type'][$idx] ?? null,
                'tmp_name' => $files['tmp_name'][$idx] ?? null,
                'error' => $files['error'][$idx] ?? null,
                'size' => $files['size'][$idx] ?? null,
            ];
        }
        return $normalized;
    }
}
