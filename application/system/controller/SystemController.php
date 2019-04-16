<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午5:29
 */

namespace app\system\controller;

use app\http\controller\AdminController;
use classes\system\SystemClass;
use think\Request;

class SystemController extends AdminController
{
    private $class;

    public function __construct()
    {
        $this->class = new SystemClass();
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

        $this->class->image_delete($result);//删除未使用的logo

        return parent::success('/system/index');
    }

    public function postImage(Request $request)
    {
        $image = $this->class->image($request);

        return parent::success('','操作成功',$image);
    }
}