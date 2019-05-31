<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/1/3
 * Time: 下午2:12
 */

namespace classes\bill;

use app\express\model\ExpressModel;
use app\goods\model\GoodsClassModel;
use app\order\model\OrderModel;
use app\Substation\model\SubstationModel;
use classes\AdminClass;
use think\Request;

class ProfitClass extends AdminClass
{
    //快递
    public function express()
    {
        $express = new ExpressModel();
        $e = $express->order('sort', 'asc')->order('platform', 'asc')->column('id,name,platform');

        $result = [];

        foreach ($e as $v) {

            $result[$v['platform']][] = $v;
        }

        return $result;
    }

    //分类
    public function goods_class()
    {
        $class = new GoodsClassModel();
        return $class->order('sort', 'asc')->column('id,name');
    }

    public function profit(Request $request)
    {
        $data = $request->post();
        $startTime = $data["startTime"];
        $endTime = $data["endTime"];
        $substation = $data["substation"];
        $express = $data["express"];
        $goods_class = $data["goods_class"];
        $goods_code = $data["goods_code"];

        //初始化订单模型
        $model = new OrderModel();
        $where = [];
        $where[] = ['substation', '=', $substation];
        $where[] = ['order_status', '=', 20];
        if (!empty($startTime)) $where[] = ['created_at', '>=', $startTime . ' 00:00:00'];
        if (!empty($endTime)) $where[] = ['created_at', '<=', $endTime . ' 00:00:00'];
        if (!empty($express)) {

            $e = explode('-', $express);
            if (isset($e[1])) {

                $ex = new ExpressModel();
                $ex = $ex->where('platform', '=', $e[1])->column('id');
                $model = $model->whereIn('express_id', $ex);
            } else {

                $where[] = ['express_id', '=', $e[0]];
            }
        }
        if (!empty($goods_class)) $where[] = ['goods_class_id', '=', $goods_class];
        if (!empty($goods_code)) {

            $str = str_replace(" ", '', $goods_code);//去空格
            $code = explode("\r\n", $str);//按行分组
            $codes = [];
            foreach ($code as $va) {

                $va = preg_replace("/(，)/", ',', $va);//去逗号
                $codes = array_merge($codes, explode(',', $va));
            }

            $model = $model->whereIn('goods_code', $codes);
        }

        $result = [
            'self' => [],
            'child' => [],
        ];

        $number = $model->where($where)->count();//完结订单数量

        $express_number = $model->where($where)->sum('express_number');//快递数量
        $express_total = $model->where($where)->sum('total_express');//快递费
        $express_cost = $model->where($where)->sum('express_cost_all');//快递成本
        $express_profit = $express_total - $express_cost;//快进收益

        $goods_number = $model->where($where)->sum('goods_number');//商品数量
        $goods_total = $model->where($where)->sum('total_goods');//商品金额
        $goods_cost = $model->where($where)->sum('goods_cost_all');//商品成本
        $goods_profit = $goods_total - $goods_cost;//商品总收益

        $total = $model->where($where)->sum('total');//总金额
        $cost = $goods_cost + $express_cost;//总成本
        $profit = $total - $cost;//总收益

        //当前站点的收益统计
        $result['self'] = [
            'number' => $number,
            'express_number' => $express_number,
            'express_total' => $express_total,
            'express_cost' => $express_cost,
            'express_profit' => $express_profit,
            'goods_number' => $goods_number,
            'goods_total' => $goods_total,
            'goods_cost' => $goods_cost,
            'goods_profit' => $goods_profit,
            'total' => $total,
            'cost' => $cost,
            'profit' => $profit,
        ];

        if ($substation == 0) {

            $child = true;
        } else {

            //获取这个站点的信息
            $sub = new SubstationModel();
            $self = $sub->find($substation);
            if ($self['pid'] == 0) {

                //下级有站点
                $child = true;
                $subs = $sub->where('pid', '=', $substation)->column('id');
                $subs[] = $substation;

                $model = $model->whereIn('substation', $subs);
            } else {

                $child = false;
            }
        }
        //获取团队收益
        if ($child) {

            unset($where[0]);
            $where = array_values($where);

            $number = $model->where($where)->count();//完结订单数量

            $express_number = $model->where($where)->sum('express_number');//快递数量
            $express_total = $model->where($where)->sum('total_express');//快递费
            $express_cost = $model->where($where)->sum('express_cost_all');//快递成本
            $express_profit = $express_total - $express_cost;//快进收益

            $goods_number = $model->where($where)->sum('goods_number');//商品数量
            $goods_total = $model->where($where)->sum('total_goods');//商品金额
            $goods_cost = $model->where($where)->sum('goods_cost_all');//商品成本
            $goods_profit = $goods_total - $goods_cost;//商品总收益

            $total = $model->where($where)->sum('total');//总金额
            $cost = $goods_cost + $express_cost;//总成本
            $profit = $total - $cost;//总收益

            //当前站点的收益统计
            $result['child'] = [
                'number' => $number,
                'express_number' => $express_number,
                'express_total' => $express_total,
                'express_cost' => $express_cost,
                'express_profit' => $express_profit,
                'goods_number' => $goods_number,
                'goods_total' => $goods_total,
                'goods_cost' => $goods_cost,
                'goods_profit' => $goods_profit,
                'total' => $total,
                'cost' => $cost,
                'profit' => $profit,
            ];
        }

        $result['substation'] = $substation;
        return $result;
//        dd($result);
    }
}