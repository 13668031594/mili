<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/27
 * Time: 下午1:44
 */

namespace app\index\controller;

use classes\index\OrderClass;
use classes\index\OrderDownloadClass;
use think\Request;

class OrderController extends \app\http\controller\IndexController
{
    private $class;

    public function __construct()
    {
        parent::__construct();

        $this->class = new OrderClass();
    }

    //下单页面
    public function getOrder()
    {
        $goods_id = input('goodsId');

        $goods = $this->class->goods_info($goods_id);

        $store = $this->class->store();

        $express = $this->class->express();

        $content = $this->class->goods_content($goods_id);

        $result = [
            'store' => $store,
            'express' => $express,
            'platform' => config('member.store_platform'),
            'goods' => $goods,
            'prompt' => $this->class->prompt($goods),
            'content' => $content,
        ];

        return parent::view('order', $result);
    }

    //下单
    public function postOrder(Request $request)
    {
        $this->class->status();

        $address = $this->class->validator_order($request);

        $result = $this->class->save($request, $address);

        return parent::success('', '操作成功', $result);
    }

    //已购礼品页面
    public function getGoodsHad()
    {
        $result = [
            'store' => $this->class->store()
        ];

        return parent::view('goods-had', $result);
    }

    //已购礼品数据
    public function getGoodsHadTable()
    {
        $result = $this->class->goods_had_table();

        return parent::tables($result);
    }

    //发货清单
    public function getGoodsSendTable()
    {
        $result = $this->class->goods_send_table();

        return parent::tables($result);
    }

    //取消订单
    public function getOrderBack()
    {
        $this->class->order_back();

        return parent::success('/order');
    }

    public function getOrderInfo()
    {
        $class = new \classes\order\OrderClass();

        $order = $this->class->order_info(input('id'));

        $express = $class->express($order);

        return parent::view('order-info', ['self' => $order, 'express' => $express]);
    }

    public function getOrderList()
    {
        $order = $this->class->order_info(input('id'));

        return parent::view('order-list', ['id' => input('id'), 'order' => $order]);
    }

    public function getOrderTable()
    {
        $class = new \classes\order\SendClass();

        $result = $class->index(\request());

        return parent::tables($result);
    }

    //发货单下载
    public function getOrderDownload()
    {
        $class = new OrderDownloadClass();

        $class->test_time();

        //删除过期的excel文件
        $class->excel_delete();

        //添加发货单
        $class->store_send();

        //生成excel
        $url = $class->excel();

        return parent::success('', '文件生成成功，请在5分钟内下载', ['url' => $url]);
    }

    //修改订单备注
    public function getOrderNote(Request $request)
    {
        $this->class->order_note($request);

        return parent::success();
    }
}