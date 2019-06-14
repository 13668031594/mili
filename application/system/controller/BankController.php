<?php

namespace app\system\controller;

use app\http\controller\AdminController;
use classes\system\BankClass;
use classes\system\SystemClass;
use think\Request;

class BankController extends AdminController
{
    private $class;

    public function __construct()
    {
        $this->class = new BankClass();
    }

    public function getIndex()
    {
        $self = $this->class->index();

        return parent::view('index', ['self' => $self]);
    }

    public function postIndex()
    {
        $this->class->save_validator();

        $result = $this->class->save();


        return parent::success('/bank/index');
    }
}