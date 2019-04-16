<?php

namespace app\withdraw\controller;

use app\http\controller\AdminController;
use classes\withdraw\WithdrawClass;
use think\Request;

class WithdrawController extends AdminController
{
    private $class;

    public function __construct(Request $request = null)
    {
        $this->class = new WithdrawClass();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getIndex()
    {
        return parent::view('withdraw');
    }

    public function getTable(Request $request)
    {
        $result = $this->class->index($request);

        return parent::tables($result);
    }

    public function getStatus(Request $request)
    {
        $this->class->status($request);

        return parent::success('/admin/withdraw/index');
    }
}
