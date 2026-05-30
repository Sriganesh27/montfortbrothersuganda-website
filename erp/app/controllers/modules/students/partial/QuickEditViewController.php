<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/QuickEditViewModel.php';

class QuickEditViewController
{
    private $model;

    public function __construct()
    {
        $this->model = new QuickEditViewModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/quick_edit_view/index.php';
    }
}

?>