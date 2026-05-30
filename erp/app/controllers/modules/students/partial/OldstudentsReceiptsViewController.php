<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/OldstudentsReceiptsViewModel.php';

class OldstudentsReceiptsViewController
{
    private $model;

    public function __construct()
    {
        $this->model = new OldstudentsReceiptsViewModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/oldstudents_receipts_view/index.php';
    }
}

?>