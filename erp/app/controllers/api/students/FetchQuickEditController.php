<?php

require_once __DIR__ . '/../../../models/api/students/FetchQuickEditModel.php';

class FetchQuickEditController
{
    private $model;

    public function __construct()
    {
        $this->model = new FetchQuickEditModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/fetch_quick_edit/index.php';
    }
}

?>