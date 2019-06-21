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
    private $amount = 0;

    public function __construct()
    {
        //初始化定价模型
        $this->grade_amount_model = new MemberGradeExpressModel();
        $this->grade_amount_model = $this->grade_amount_model->where('substation', '=', SUBSTATION);

        if (SUBSTATION != 0) {

            //获取当前分站信息
            $sub = new SubstationModel();
            $sub = $sub->find(SUBSTATION);

            //获取初始等级信息
            $level = new SubstationLevelModel();
            $level = $level->find($sub['level_id']);
            $this->amount = $level['express_up'];

            //二级分站，获取以及分站设置的等级信息
            if ($sub['pid'] != 0) {

                $up = new SubstationLevelUpModel();
                $up = $up->where('level_id', '=', $sub['level_id'])->find();

                if (is_null($up)) {

                    $this->amount += $level['express_up'];
                } else {

                    $this->amount += $up['express_up'];
                }
            }
        }
    }

    public function amount($express_id, $grade,$protect)
    {
        $result = $protect + $this->amount;
        $amount = $this->grade_amount_model->where('grade','=',$grade)->where('express','=',$express_id)->find();
        if (!is_null($amount) && ($amount->amount > $result))$result = $amount->amount;

        $result = number_format($result, 2, '.', '');

        return $result;
    }
}