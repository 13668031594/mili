<?php

namespace app\recharge\controller;

use app\http\controller\AdminController;
use classes\recharge\RechargeClass;
use classes\recharge\RechargeDownClass;
use think\Db;
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
        Db::startTrans();

        $this->class->status($request);

        Db::commit();

        return parent::success('/admin/recharge/index');
    }

    public function getDownload()
    {
        $class = new RechargeDownClass();

        //删除过期的excel文件
        $class->excel_delete();

        //生成excel
        $url = $class->excel();

        $result = [
            'status' => 'success',
            'url' => '/' . $url,
            'message' => '生成成功',
        ];


        return json($result);
    }
}
