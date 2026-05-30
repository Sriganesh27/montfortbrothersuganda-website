<?php

require_once __DIR__ . '/../../models/includes/DbModel.php';

class DbController
{
    private $model;

    public function __construct()
    {
        $this->model = new DbModel();
    }

    public function index()
    {
        include __DIR__ . '/../../views/includes/db/index.php';
    }
}

?>