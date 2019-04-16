<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/30
 * Time: 下午4:43
 */

namespace app\nav\controller;


use app\http\controller\AdminController;
use classes\nav\NavClass;

class NavController extends AdminController
{
    private $class;

    public function __construct()
    {
        $this->class = new NavClass();
    }

    public function getIndex()
    {
        $set = $this->class->index();

        return parent::view('nav', ['set' => $set]);
    }

    public function postIndex()
    {
        $this->class->save();

        return parent::success('/nav/index');
    }
}