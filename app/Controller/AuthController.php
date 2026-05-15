<?php

App::uses('AppController', 'Controller');
class AuthController extends AppController
{

    public function index()
    {
        $this->set('title', 'ログイン');
    }

    public function logout()
    {
        if ($this->request->is('post')) {
            $this->Session->destroy();
            return $this->redirect([
                'action' => 'index'
            ]);
        }
    }
}
