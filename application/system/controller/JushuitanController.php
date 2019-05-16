<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午5:29
 */

namespace app\system\controller;

use app\http\controller\AdminController;
use classes\system\JushuitanClass;

class JushuitanController extends AdminController
{
    private $class;

    public function __construct()
    {
        $this->class = new JushuitanClass();
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

        return parent::success('/jushuitan/index');
    }
}