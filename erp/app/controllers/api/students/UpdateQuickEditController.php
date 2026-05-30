<?php

require_once __DIR__ . '/../../../models/api/students/UpdateQuickEditModel.php';

class UpdateQuickEditController
{
    private $model;

    public function __construct()
    {
        $this->model = new UpdateQuickEditModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../views/api/students/update_quick_edit/index.php';
    }
}

?>