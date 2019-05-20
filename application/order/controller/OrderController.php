<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/25
 * Time: 下午2:14
 */

namespace app\order\controller;

use app\http\controller\AdminController;
use classes\order\OrderClass;
use think\Db;
use think\Request;

class OrderController extends AdminController
{
    private $class;

    public function __construct(Request $request = null)
    {
        $this->class = new OrderClass();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getIndex()
    {
        return parent::view('index');
    }

    public function getTable()
    {
        $result = $this->class->index();

        return parent::tables($result);
    }

    public function getEdit($id)
    {
        $order = $this->class->edit($id);

        $express = $this->class->express($order);

        return parent::view('order', ['self' => $order, 'express' => $express]);
    }

    public function getDelete()
    {
        $ids = explode(',', input('id'));

        //删除
        $this->class->delete($ids);

        //反馈成功
        return parent::success('/order/index');
    }

    public function getPay($id)
    {
        $this->class->pay($id);

        return parent::success('/order/edit?id=' . $id);
    }

    public function getSend($id)
    {
        $order = $this->class->edit($id);

        $express = $this->class->express($order);

        $sends = $this->class->sends($order);

        return parent::view('send', ['self' => $order, 'express' => $express, 'sends' => $sends]);
    }

    public function postSend(Request $request)
    {
        $result = $this->class->send2($request);
        if ($result === false) $this->class->send($request);

        return parent::success('/order/index');
    }

    public function getBack()
    {
        Db::startTrans();

        //验证订单状态
        $order = $this->class->validator_back();

        //发货中状态
        if ($order['order_status'] == 15) {

            //获取发货单号
            $send = $this->class->back_sends($order);

            //验证所有订单在聚水潭中的状态
            if (!empty($send)) $this->class->jushuitan_back($send);
        }

        //撤销订单
        $this->class->order_back($order);

        Db::commit();

        return parent::view('index');
    }
}