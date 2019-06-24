<?php

namespace classes\vendor;

use app\goods\model\GoodsAmountModel;
use app\Goods\model\GoodsLevelAmountModel;
use app\Substation\model\SubstationLevelModel;
use app\Substation\model\SubstationLevelUpModel;
use app\Substation\model\SubstationModel;

class GoodsAmountClass
{
    private $amount_model;
    private $level_amount_model;
    private $amount1;
    private $amount2;
    private $cost1;
    private $cost2;
    private $protect1;
    private $protect2;
    private $where;
    private $pid;
    private $sub;

    public function __construct($sub = null)
    {
        if (is_null($sub)) $this->sub = SUBSTATION;
        else $this->sub = $sub;

        if ($this->sub != 0){

            //初始化定价模型
            $this->amount_model = new GoodsAmountModel();
            $this->level_amount_model = new GoodsLevelAmountModel();

            //获取当前分站信息
            $sub = new SubstationModel();
            $sub = $sub->find($this->sub);
            $this->pid = $sub['pid'];

            //获取初始等级信息
            $level = new SubstationLevelModel();
            $level = $level->find($sub['level_id']);
            $this->amount1 = $level['goods_up'];
            $this->cost1 = $level['goods_cost_up'];
            $this->protect1 = $level['goods_protect_up'];

            //商品单独定价
            $this->where = [
                ['substation', '=', $sub['pid']],
                ['level_id', '=', $sub['level_id']],
            ];

            //二级分站，获取以及分站设置的等级信息
            if ($sub['pid'] != 0) {

                $up = new SubstationLevelUpModel();
                $up = $up->where('level_id', '=', $sub['level_id'])->find();

                if (is_null($up)) {

                    $this->amount2 = $level['goods_up'];
                    $this->cost2 = $level['goods_cost_up'];
                    $this->protect2 = $level['goods_protect_up'];
                } else {

                    $this->amount2 = $up['goods_up'];
                    $this->cost2 = $up['goods_cost_up'];
                    $this->protect2 = $up['goods_protect_up'];
                }
            }
        }
    }

    public function amount($goods_id, $amount, $cost, $protect)
    {
        $result = [
            'amount' => $amount,
            'cost' => $cost,
            'protect' => $protect,
        ];

        if ($this->sub == 0){

            $result['amount'] = number_format($result['amount'], 2, '.', '');
            $result['cost'] = number_format($result['cost'], 2, '.', '');
            $result['protect'] = number_format($result['protect'], 2, '.', '');
            return $result;
        }

        //获取当前定价信息
        $a = $this->amount_model->where('goods_id', '=', $goods_id)->where('substation', '=', $this->sub)->find();
        $al = $this->level_amount_model->where($this->where)->where('goods_id', '=', $goods_id)->find();

        //根据基础定价计算出当前分站应有的价格
        if ($this->pid == 0) {

            $base_amount = $amount + $this->amount1;
            $base_cost = $cost + $this->cost1;
            $base_protect = $protect + $this->protect1;
        } else {
            $where = $this->where;
            $where[0] = ['substation', '=', 0];
            $p_a = $this->amount_model->where('goods_id', '=', $goods_id)->where('substation', '=', $this->sub)->find();
            $p_al = $this->level_amount_model->where($where)->where('goods_id', '=', $goods_id)->find();

            //上级站点是否自行定价
            if (is_null($p_a)) $base_amount = $amount + $this->amount1 + $this->amount2;//未自行定价，用主站价格上浮两次
            else $base_amount = $p_a->amount + $this->amount2;//自行定价了，上浮一次

            if (is_null($p_al)) {

                //上级站点没有自行定价
                //保护价和成本价上浮两次
                $base_cost = $cost + $this->cost1 + $this->cost2;
                $base_protect = $protect + $this->protect1 + $this->protect2;
            } else {

                //上级站点有自行定价
                $base_cost = $p_al->cost + $this->cost2;
                $base_protect = $p_al->protect + $this->protect2;
            }
        }

        //确定成本价与保护价
        if (is_null($al)) {

            $result['cost'] = $base_cost;
            $result['protect'] = $base_protect;
        } else {

            $result['cost'] = $cost > $al->cost ? $cost : $al->cost;
            $result['protect'] = $protect > $al->protect ? $protect : $al->protect;
        }

        //确定售价
        if (!is_null($a)) {

            $result['amount'] = $a->amount;
        } else {

            $result['amount'] = $base_amount;
        }

        if ($result['protect'] > $result['amount']) $result['amount'] = $result['protect'];

        $result['amount'] = number_format($result['amount'], 2, '.', '');
        $result['cost'] = number_format($result['cost'], 2, '.', '');
        $result['protect'] = number_format($result['protect'], 2, '.', '');

        return $result;
    }
}