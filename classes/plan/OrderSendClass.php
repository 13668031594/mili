<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/5/16
 * Time: 下午9:24
 */

namespace classes\plan;


use app\order\model\OrderModel;
use app\order\model\OrderSendModel;
use classes\FirstClass;
use classes\system\SystemClass;
use classes\vendor\JushuitanClass;
use think\Db;

class OrderSendClass extends FirstClass
{
    public function __construct()
    {
        $set = new SystemClass();
        $set = $set->index();

        $test = self::test_time($set);
        if (!$test) return;

        $send = new OrderSendModel();
        $sends = $send->alias('a')
            ->leftJoin('order o', 'o.id = a.order_id')
            ->where('o.order_status', 'in', [15])
            ->where('a.send_create', '=', null)
            ->column('a.id,a.send_order,o.id as oid', 'a.send_order');
        if (count($sends) <= 0) parent::ajax_exception(000, '没有需要发货的订单');

        //50个一组
        $sends2 = array_chunk($sends, 50);


        $class = new JushuitanClass();

        $date = date('Y-m-d H:i:s');
        $update = [];
        $order_ids = [];
        foreach ($sends2 as $k => $v) {

            //通过接口获取订单信息
            $result = $class->orders_single_query($set['jushuitanShopid'], array_column($v, 'send_order'));

            if ($result['code'] != '0' || !isset($result['orders'])) continue;

            foreach ($result['orders'] as $va) {

                if (isset($sends[$va['so_id']]) && !is_null($va['l_id'])) {

                    $update[] = [
                        'id' => $sends[$va['so_id']]['id'],
                        'express_no' => $va['l_id'],
                        'send_create' => $date,
                    ];
                    $order_ids[] = $sends[$va['so_id']]['oid'];
                }
            }
        }

        if (count($update) > 0) self::table_update('order_send', $update);
        if (count($order_ids)) {
            $order = new OrderModel();
            $order->whereIn('id', $order_ids)->update(['order_status' => '20']);
        }
    }

    //验证同步时间
    private function test_time($set)
    {
        $cache = cache('send_order_time');
        if ($cache) return false;
        cache('send_order_time', date('Y-m-d H:i:s'), (60 * $set['jushuitanRefreshOrder']));

        return true;
    }

    //批量更新
    private function table_update($tableName = "", $multipleData = array(), $referenceColumn = 'id')
    {
        if ($tableName && !empty($multipleData)) {

            $tableName = env('DATABASE_PREFIX') . $tableName;

            $multipleData = array_values($multipleData);

            // column or fields to update
            $updateColumn = array_keys($multipleData[0]);
            unset($updateColumn[$referenceColumn]);
            $whereIn = "";

            $q = "UPDATE " . $tableName . " SET ";
            foreach ($updateColumn as $uColumn) {
                $q .= $uColumn . " = CASE ";

                foreach ($multipleData as $data) {
                    $q .= "WHEN " . $referenceColumn . " = " . $data[$referenceColumn] . " THEN '" . $data[$uColumn] . "' ";
                }
                $q .= "ELSE " . $uColumn . " END, ";
            }
            foreach ($multipleData as $data) {
                $whereIn .= "'" . $data[$referenceColumn] . "', ";
            }
            $q = rtrim($q, ", ") . " WHERE " . $referenceColumn . " IN (" . rtrim($whereIn, ', ') . ")";

            // Update
            $a = Db::execute($q);
            dump($a);
            exit;

        } else {
            return false;
        }
    }
}