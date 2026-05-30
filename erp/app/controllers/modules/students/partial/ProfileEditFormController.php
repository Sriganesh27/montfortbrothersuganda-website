<?php

require_once __DIR__ . '/../../../../models/modules/students/partial/ProfileEditFormModel.php';

class ProfileEditFormController
{
    private $model;

    public function __construct()
    {
        $this->model = new ProfileEditFormModel();
    }

    public function index()
    {
        include __DIR__ . '/../../../../views/modules/students/partial/profile_edit_form/index.php';
    }
}

?>