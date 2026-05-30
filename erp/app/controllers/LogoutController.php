<?php

require_once __DIR__ . '/../models/LogoutModel.php';

class LogoutController
{
    private $model;

    public function __construct()
    {
        $this->model = new LogoutModel();
    }

    public function index()
    {
        include __DIR__ . '/../views/logout/index.php';
    }
}

?>