<?php

namespace classes\plan;

use app\order\model\OrderModel;
use app\order\model\OrderSendModel;
use classes\FirstClass;
use classes\order\SendClass;
use classes\system\JushuitanClass;

class OrderUploadClass extends FirstClass
{
    public function __construct()
    {
        $set = new JushuitanClass();
        $set = $set->index();

        $test = self::test_time($set);
        if (!$test) return;

        $class = new SendClass();

        //添加发货单
        $class->store_send();

        //上传订单
        $result = self::jushuitan_order();

        dump('upload_order：' . $result);
    }

    private function test_time($set)
    {
        $cache = cache('upload_order_time');
        if ($cache) return false;
        cache('upload_order_time', date('Y-m-d H:i:s'), (60 * $set['jushuitanRefreshOrder']));

        return true;
    }

    //上传订单到聚水潭
    public function jushuitan_order()
    {
        $class = new \classes\vendor\JushuitanClass();

        $date = date('Y-m-d H:i:s', strtotime('-3 day'));

        //获得需要上传的订单
        $send = new OrderSendModel();
        $sends = $send->alias('a')
            ->leftJoin('order o', 'o.id = a.order_id')
            ->leftJoin('goods g', 'g.id = o.goods_id')
            ->where('o.order_status', 'in', [10])
            ->where('a.order_create', '>=', $date)
            ->where('a.send_create', '=', null)
            ->column('a.*,o.total_goods,o.goods_amount,o.express_amount,o.express_number,o.store_address,g.code,o.member_account,o.total');

        if (count($sends) <= 0) return '没有需要发货的订单';

        //商品编号
        $sku_id = [];
        foreach ($sends as $v) if (!in_array($v['code'], $sku_id) && !empty($v['code'])) $sku_id[] = $v['code'];
        if (empty($sku_id)) return '请先核对商品编号1';

        //50个一组
        $sku_id = array_chunk($sku_id, 50);
        //可用编号
        $sku = [];
        //寻找在库存中的商品
        foreach ($sku_id as $v) {

            //通过接口获取数据
            $result = $class->inventory_query(implode(',', $v));

            //没有成功获取到数据
            if (!isset($result['inventorys'])) continue;

            //将返回的商品编号放入可用编号数组中
            foreach ($result['inventorys'] as $va) $sku[] = $va['sku_id'];
        }

        if (empty($sku)) return '请先核对商品编号2';

        $data = [];
        $order_numbers = [];
        $set = new \classes\system\JushuitanClass();
        $set = $set->index();
        foreach ($sends as $k => $v) {

            //商品编号聚水潭中没有，直接下一个
            if (!in_array($v['code'], $sku)) continue;

            //放入需要编辑为发货的订单号
            $order_numbers[] = $v['order_number'];

            //初始化上传数组
            $data[$k]['shop_id'] = $set['jushuitanShopid'];
            $data[$k]['so_id'] = $v['send_order'];
            $data[$k]['order_date'] = $v['order_create'];
            $data[$k]['shop_status'] = 'WAIT_SELLER_SEND_GOODS';//等待发货
            $data[$k]['shop_buyer_id'] = $v['consignee'];//买家昵称
            $data[$k]['receiver_state'] = $v['pro'];//收货省
            $data[$k]['receiver_city'] = $v['city'];//收货市
            $data[$k]['receiver_district'] = $v['area'];//收货区
            $data[$k]['receiver_address'] = $v['add'];//收货地址
            $data[$k]['receiver_name'] = $v['consignee'];//收货人
            $data[$k]['receiver_mobile'] = $v['phone'];//收货电话
            $data[$k]['pay_amount'] = number_format(($v['total_goods'] / $v['express_number']), 2, '.', '');//应付金额，保留两位小数，单位（元）
            $data[$k]['freight'] = $v['express_amount'];//运费，保留两位小数，单位（元）
            $data[$k]['remark'] = '发货：' . $v['store_address'];//备注
            $data[$k]['shop_modified'] = '';//店铺修改日期
            $data[$k]['logistics_company'] = $v['express'];//快递公司
            $data[$k]['items'] = [
                [
                    'sku_id' => $v['code'],//商品sku
                    'shop_sku_id' => $v['code'],//网站sku
                    'amount' => $v['goods_amount'],//应付金额，保留两位小数，单位（元）；备注：可能存在人工改价
                    'base_price' => $v['goods_amount'],//基本价（拍下价格），保留两位小数，单位（元）
                    'qty' => $v['goods_number'],//购买数量
                    'name' => $v['goods'],//商品名称
                    'outer_oi_id' => $v['id'],//id
                ],
            ];
            $data[$k]['pay'] = [
                'outer_pay_id' => $v['send_order'],//外部支付单号，最大50
                'pay_date' => $v['order_create'],//支付日期
                'payment' => '米礼网余额支付',//支付方式
                'seller_account' => $v['member_account'],//卖家账号
                'buyer_account' => $v['phone'],//买家账号
                'amount' => number_format(($v['total'] / $v['express_number']), 2, '.', ''),//支付金额
            ];
        }

        if (count($data) <= 0) return '请先核对商品编号3';

        $datas = array_chunk($data, 2000);

        foreach ($datas as $k => $v) {

            $result = $class->orders_upload(array_values($v));

            if ($result['code'] != '0') return '上传失败：' . $result['msg'] . '。code：' . $result['code'] . '，key：' . $k;

            //修改订单状态
            $model = new OrderModel();
            $model->whereIn('order_number', $order_numbers)->update(['order_status' => '15']);
        }

        return '成功上传，合计上传发货信息：' . count($data) . '条，共涉及本站订单：' . count(array_unique($order_numbers)) . '条';
    }
}