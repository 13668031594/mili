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
    private $amount;
    private $cost;
    private $protect;

    public function __construct()
    {
        //初始化定价模型
        $this->amount_model = new GoodsAmountModel();
        $this->level_amount_model = new GoodsLevelAmountModel();

        //获取当前分站信息
        $sub = new SubstationModel();
        $sub = $sub->find(SUBSTATION);

        //获取初始等级信息
        $level = new SubstationLevelModel();
        $level = $level->find($sub['level_id']);
        $this->amount = $level['goods_up'];
        $this->cost = $level['goods_cost_up'];
        $this->protect = $level['goods_protect_up'];

        //商品单独定价
        $this->level_amount_model = $this->level_amount_model->where('substation', '=', $sub['pid'])->where('level_id', '=', $sub['level_id']);

        //二级分站，获取以及分站设置的等级信息
        if ($sub['pid'] != 0) {

            $up = new SubstationLevelUpModel();
            $up = $up->where('level_id', '=', $sub['level_id'])->find();

            $this->amount += $up['goods_up'];
            $this->cost += $up['goods_cost_up'];
            $this->protect += $up['goods_protect_up'];
        }
    }

    public function amount($goods_id, $amount, $cost, $protect)
    {
        $result = [
            'amount' => 0.00,
            'cost' => 0.00,
            'protect' => 0.00,
        ];

        //获取当前定价信息
        $a = $this->amount_model->where('goods_id', '=', $goods_id)->where('substation', '=', SUBSTATION)->find();
        $al = $this->level_amount_model->where('goods_id', '=', $goods_id)->find();

        //根据基础定价计算出当前分站应有的价格
        $base_amount = $amount + $this->amount;
        $base_cost = $cost + $this->cost;
        $base_protect = $protect + $this->protect;

        //确定成本价与保护价
        if (is_null($al)) {

            $result['cost'] = $base_cost;
            $result['protect'] = $base_protect;
        } else {

            $result['cost'] = $base_cost > $al->cost ? $base_cost : $al->cost;
            $result['protect'] = $base_protect > $al->protect ? $base_protect : $al->protect;
        }

        //确定售价
        if (!is_null($a)) {

            $result['amount'] = $a->amount;
        } else {

            $result['amount'] = $base_amount;
        }

        if ($result['protect'] > $result['amount']) $result['amount'] = $result['protect'];

        $result['amount'] = number_format($result['amount'],2,'.','');
        $result['cost'] = number_format($result['cost'],2,'.','');
        $result['protect'] = number_format($result['protect'],2,'.','');

        return $result;
    }
}