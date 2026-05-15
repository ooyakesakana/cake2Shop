<?php
App::uses('AppController', 'Controller');
class ExpensesController extends AppController
{

    public function index()
    {
        $this->layout = 'layout';
        $this->set('titleForLayout', '経費管理メニュー');
    }
}
