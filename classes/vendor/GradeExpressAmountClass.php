<?php

namespace classes\vendor;

use app\Member\model\ExpressLevelAmountModel;
use app\Member\model\MemberGradeAmountModel;
use app\member\model\MemberGradeExpressModel;
use app\Substation\model\SubstationLevelModel;
use app\Substation\model\SubstationLevelUpModel;
use app\Substation\model\SubstationModel;

class GradeExpressAmountClass
{
    private $grade_amount_model;
    private $where;
    private $amount1 = 0;
    private $amount2 = 0;
    private $pid;
    private $sub;

    public function __construct($sub = null)
    {
        if (is_null($sub)) $this->sub = SUBSTATION;
        else $this->sub = $sub;

        //初始化定价模型
        $this->grade_amount_model = new MemberGradeExpressModel();
        $this->where = [
            ['substation', '=', $this->sub]
        ];

        if ($this->sub != 0) {

            //获取当前分站信息
            $sub = new SubstationModel();
            $sub = $sub->find($this->sub);
            $this->pid = $sub['pid'];

            //获取初始等级信息
            $level = new SubstationLevelModel();
            $level = $level->find($sub['level_id']);
            $this->amount1 = $level['express_up'];

            //二级分站，获取以及分站设置的等级信息
            if ($sub['pid'] != 0) {

                $up = new SubstationLevelUpModel();
                $up = $up->where('level_id', '=', $sub['level_id'])->find();

                if (is_null($up)) {

                    $this->amount2 = $level['express_up'];
                } else {

                    $this->amount2 = $up['express_up'];
                }
            }
        }
    }

    public function amount($express_id, $grade, $protect)
    {
        //寻找自己的价格
        $amount = $this->grade_amount_model->where($this->where)->where('grade', '=', $grade)->where('express', '=', $express_id)->find();

        if (!is_null($amount)) $result = $amount->amount;//找到定价，赋值
        elseif ($this->sub == 0) return 0.00;//主站无定价，价格0.00
        else {

            //自己没有定价，根据上级价格定价

            //寻找上级售价
            $p_amount = $this->grade_amount_model->where('substation', '=', $this->pid)->where('grade', '=', $grade)->where('express', '=', $express_id)->find();
            if (!is_null($p_amount)) {

                //找到了上级价格，叠加自动上浮
                $result = $p_amount->amount;
                $result += $this->pid == 0 ? $this->amount1 : ($this->amount2);
            } elseif ($this->pid != 0) {

                //没找到上级价格，再寻找主站价格
                $p_amount = $this->grade_amount_model->where('substation', '=', $this->pid)->where('grade', '=', $grade)->where('express', '=', $express_id)->find();
                if (!is_null($p_amount)) {

                    //找到主站售价，自动上浮两次
                    $result = $p_amount->amount;
                    $result += ($this->amount1 + $this->amount2);
                } else {

                    //没有找到主站售价，售价等于上浮两次价格
                    $result = $this->amount1 + $this->amount2;
                }
            } else {

                //上级就是主站，且没有设定价格，售价直接为上浮价格
                $result = $this->amount1;
            }
        }

        //若销售价小于保护价
        if ($result < $protect) $result = $protect;
        $result = number_format($result, 2, '.', '');

        return $result;
    }
}