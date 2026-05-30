<?php

require_once __DIR__ . '/../../../models/api/students/SearchGlobalModel.php';

class SearchGlobalController
{
    private $model;

    public function __construct()
    {
        $this->model = new SearchGlobalModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/search_global/index.php';
    }
}

?>