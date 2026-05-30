<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/OldstudentsDebtsViewModel.php';

class OldstudentsDebtsViewController
{
    private $model;

    public function __construct()
    {
        $this->model = new OldstudentsDebtsViewModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/oldstudents_debts_view/index.php';
    }
}

?>