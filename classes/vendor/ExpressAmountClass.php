<?php

namespace classes\vendor;

use app\member\model\ExpressLevelAmountModel;
use app\member\model\MemberGradeAmountModel;
use app\member\model\MemberGradeExpressModel;
use app\substation\model\SubstationLevelModel;
use app\substation\model\SubstationLevelUpModel;
use app\substation\model\SubstationModel;

class ExpressAmountClass
{
    private $level_amount_model;
    private $cost1 = 0;
    private $protect1 = 0;
    private $cost2 = 0;
    private $protect2 = 0;
    private $where = [];
    private $sub;
    private $pid;

    public function __construct($sub = null)
    {
        if (is_null($sub)) $this->sub = SUBSTATION;
        else $this->sub = $sub;

        //初始化定价模型
        $this->level_amount_model = new ExpressLevelAmountModel();

        if ($this->sub != 0) {

            //获取当前分站信息
            $sub = new SubstationModel();
            $sub = $sub->find($this->sub);
            $this->pid = $sub->pid;

            //获取初始等级信息
            $level = new SubstationLevelModel();
            $level = $level->find($sub['level_id']);
            $this->cost1 = $level['express_cost_up'];
            $this->protect1 = $level['express_cost_up'];

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

                    $this->cost2 = $level['express_cost_up'];
                    $this->protect2 = $level['express_cost_up'];
                } else {

                    $this->cost2 = $up['express_cost_up'];
                    $this->protect2 = $up['express_cost_up'];
                }

            }
        }
    }

    public function amount($express_id, $cost, $protect)
    {
        $result = [
            'cost' => $cost,
            'protect' => $protect,
        ];

        if ($this->sub == 0) {

            $result['cost'] = number_format($result['cost'], 2, '.', '');
            $result['protect'] = number_format($result['protect'], 2, '.', '');
            return $result;
        }


        //根据基础定价计算出当前分站应有的价格
        if ($this->pid == 0) {

            $base_cost = $cost + $this->cost1;
            $base_protect = $protect + $this->protect1;
        } else {
            $where = $this->where;
            $where[0] = ['substation', '=', 0];
            $p_al = $this->level_amount_model->where($where)->where('express', '=', $express_id)->find();

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

        //获取当前定价信息
        $al = $this->level_amount_model->where($this->where)->where('express', '=', $express_id)->find();

        //确定成本价与保护价
        if (is_null($al)) {

            $result['cost'] = $base_cost;
            $result['protect'] = $base_protect;
        } else {

            $result['cost'] = $cost > $al->cost ? $cost : $al->cost;
            $result['protect'] = $protect > $al->protect ? $protect : $al->protect;
        }

        $result['cost'] = number_format($result['cost'], 2, '.', '');
        $result['protect'] = number_format($result['protect'], 2, '.', '');

        return $result;
    }
}