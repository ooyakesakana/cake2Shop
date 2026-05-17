<?php
App::uses('AppController', 'Controller');
class ExpensesController extends AppController
{

    public function index()
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', '経費管理メニュー');

        $expenses = $this->Expense->find('all', [
            'order' => ['Expense.expense_date' => 'DESC', 'Expense.id' => 'DESC'],
            'recursive' => 0,
        ]);
        $this->set(compact('expenses'));
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
        $expenseCategories = $this->ExpenseCategory->find('list', [
            'fields' => ['ExpenseCategory.category_name', 'ExpenseCategory.category_name'],
            'order' => ['ExpenseCategory.category_name' => 'ASC'],
        ]);
        $this->set(compact('expenseCategories'));

        if ($this->request->is('post')) {
            $data = $this->request->data['Expense'];
            unset($data['category_select']);
            if (empty($data['expense_date'])) {
                $data['expense_date'] = date('Y-m-d');
            }
            $data['amount'] = (float)str_replace(',', '', mb_convert_kana((string)($data['amount'] ?? 0), 'n'));

            $categoryName = trim((string)($data['category_name'] ?? ''));
            if ($categoryName !== '') {
                $category = $this->ExpenseCategory->find('first', [
                    'conditions' => ['ExpenseCategory.category_name' => $categoryName],
                    'recursive' => -1,
                ]);
                if ($category) {
                    $data['expense_category_id'] = $category['ExpenseCategory']['id'];
                    $data['tax_account_name'] = $category['ExpenseCategory']['tax_account_name'];
                } else {
                    $this->ExpenseCategory->create();
                    if ($this->ExpenseCategory->save(['category_name' => $categoryName])) {
                        $data['expense_category_id'] = $this->ExpenseCategory->id;
                    }
                }
            }

            $this->Expense->create();
            if ($this->Expense->save($data)) {
                $this->Session->setFlash('経費を登録しました', 'default', [], 'success');
                return $this->redirect(['action' => 'index']);
            }
            $this->Session->setFlash('経費登録に失敗しました', 'default', [], 'errMsg');
        }
    }
}
