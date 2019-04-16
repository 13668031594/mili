<?php

namespace app\recharge\controller;

use app\http\controller\AdminController;
use classes\recharge\RechargeClass;
use think\Request;

class RechargeController extends AdminController
{
    private $class;

    public function __construct(Request $request = null)
    {
        $this->class = new RechargeClass();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getIndex()
    {
        return parent::view('recharge');
    }

    public function getTable(Request $request)
    {
        $result = $this->class->index($request);

        return parent::tables($result);
    }

    public function getStatus(Request $request)
    {
        $this->class->status($request);

        return parent::success('/admin/recharge/index');
    }
}
