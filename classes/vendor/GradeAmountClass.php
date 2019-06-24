<?php

namespace classes\vendor;

use app\Member\model\MemberGradeAmountModel;

class GradeAmountClass
{
    private $amount_model;
    private $sub;

    public function __construct($sub = null)
    {
        if (is_null($sub)) $this->sub = SUBSTATION;
        else $this->sub = $sub;

        //初始化定价模型
        $this->amount_model = new MemberGradeAmountModel();
    }

    public function amount($id, $recharge, $buy_total)
    {
        $result = [
            'status' => 'on',
            'recharge' => $recharge,
            'buy_total' => $buy_total,
        ];

        //获取当前定价信息
        $a = $this->amount_model->where('grade', '=', $id)->where('substation', '=', $this->sub)->find();

        if (!is_null($a)) {

            $result = [
                'status' => $a['status'],
                'recharge' => $a['recharge'],
                'buy_total' => $a['buy_total'],
            ];
        }

        return $result;
    }
}