<?php

require_once __DIR__ . '/../models/IndexModel.php';

class IndexController
{
    private $model;

    public function __construct()
    {
        $this->model = new IndexModel();
    }

    public function index()
    {
        include __DIR__ . '/../views/index/index.php';
    }
}

?>