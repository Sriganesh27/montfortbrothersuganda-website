<?php

require_once __DIR__ . '/../models/DebugDbModel.php';

class DebugDbController
{
    private $model;

    public function __construct()
    {
        $this->model = new DebugDbModel();
    }

    public function index()
    {
        include __DIR__ . '/../views/debug_db/index.php';
    }
}

?>