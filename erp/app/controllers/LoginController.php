<?php

require_once __DIR__ . '/../models/LoginModel.php';

class LoginController
{
    private $model;

    public function __construct()
    {
        $this->model = new LoginModel();
    }

    public function index()
    {
        include __DIR__ . '/../views/login/index.php';
    }
}

?>