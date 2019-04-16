<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/27
 * Time: 下午6:31
 */

namespace app\index\controller;


use classes\index\AgentClass;
use think\Request;

class AgentController extends \app\http\controller\IndexController
{
    private $class;

    public function __construct()
    {
        parent::__construct();

        $this->class = new AgentClass();
    }

    //我的推广页面
    public function getGeneralize()
    {
        $result = [
            'choice' => '/generalize',
            'host' => request()->host(),
            'total' => $this->class->commis_all(),
        ];

        return parent::view('generalize', $result);
    }

    //我的推广数据，所有下级翻页
    public function getSon()
    {
        $result = $this->class->son();

        return parent::tables($result);
    }

    //提现记录页面
    public function getDeduct()
    {
        $result = [
            'choice' => '/deduct-note',
        ];

        return parent::view('deduct-note', $result);
    }

    //提现记录数据
    public function getDeductTable()
    {
        $result = $this->class->withdraw();

        return parent::tables($result);
    }

    //取消提现
    public function getBack()
    {
        $this->class->status();

    }

    //提现页面
    public function getWithdraw()
    {
        $result = [
            'choice' => '/withdraw',
        ];

        return parent::view('withdraw', $result);
    }

    //申请提现
    public function postWithdraw(Request $request)
    {
        $this->class->status();

        $this->class->validator_draw($request);

        if ($request->post('type') == '2')
            $this->class->transfer($request);
        else
            $this->class->draw($request);

        return parent::success('/withdraw');
    }

    //佣金记录页面
    public function getCommis()
    {
        $result = [
            'choice' => '/commis',
        ];

        return parent::view('commis-note', $result);
    }

    //佣金记录数据
    public function getCommisTable()
    {
        $result = $this->class->commis();

        return parent::tables($result);
    }

}