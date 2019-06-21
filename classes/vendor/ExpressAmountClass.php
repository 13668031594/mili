<?php

namespace classes\vendor;

use app\Member\model\ExpressLevelAmountModel;
use app\Member\model\MemberGradeAmountModel;
use app\member\model\MemberGradeExpressModel;
use app\Substation\model\SubstationLevelModel;
use app\Substation\model\SubstationLevelUpModel;
use app\Substation\model\SubstationModel;

class ExpressAmountClass
{
    private $level_amount_model;
    private $amount;
    private $cost = 0;
    private $protect = 0;

    public function __construct()
    {
        //初始化定价模型
        $this->level_amount_model = new ExpressLevelAmountModel();

        if (SUBSTATION != 0) {

            //获取当前分站信息
            $sub = new SubstationModel();
            $sub = $sub->find(SUBSTATION);

            //获取初始等级信息
            $level = new SubstationLevelModel();
            $level = $level->find($sub['level_id']);
            $this->cost = $level['express_cost_up'];
            $this->protect = $level['express_cost_up'];

            //商品单独定价
            $this->level_amount_model = $this->level_amount_model->where('substation', '=', $sub['pid'])->where('level_id', '=', $sub['level_id']);

            //二级分站，获取以及分站设置的等级信息
            if ($sub['pid'] != 0) {

                $up = new SubstationLevelUpModel();
                $up = $up->where('level_id', '=', $sub['level_id'])->find();

                if (is_null($up)){

                    $this->cost += $level['express_cost_up'];
                    $this->protect += $level['express_cost_up'];
                }else{

                    $this->cost += $up['express_cost_up'];
                    $this->protect += $up['express_cost_up'];
                }

            }
        }
    }

    public function amount($express_id, $cost, $protect)
    {
        //根据基础定价计算出当前分站应有的价格
        $base_cost = $cost + $this->cost;
        $base_protect = $protect + $this->protect;

        $result = [
            'cost' => $base_cost,
            'protect' => $base_protect,
        ];

        if (SUBSTATION != 0) {

            //获取当前定价信息
            $al = $this->level_amount_model->where('express', '=', $express_id)->find();

            //确定成本价与保护价
            if (!is_null($al)) {

                $result['cost'] = $base_cost > $al->cost ? $base_cost : $al->cost;
                $result['protect'] = $base_protect > $al->protect ? $base_protect : $al->protect;
            }
        }

        $result['cost'] = number_format($result['cost'], 2, '.', '');
        $result['protect'] = number_format($result['protect'], 2, '.', '');

        return $result;
    }
}