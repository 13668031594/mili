<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/27
 * Time: 下午5:54
 */

namespace classes\index;

use app\member\model\MemberRecordModel;
use app\recharge\model\RechargeModel;
use think\Request;

class RechargeClass extends \classes\IndexClass
{
    public function validator_recharge(Request $request)
    {
        parent::ajax_exception(000, '开发中');
    }

    public function recharge(Request $request)
    {
        parent::ajax_exception(000, '开发中');
    }

    //充值记录数据
    public function note()
    {
        $member = session('member');

        $where[] = ['member_id', '=', $member['id']];

        $result = [
            'where' => $where,
        ];

        return parent::page(new RechargeModel(), $result);
    }

    //充值取消
    public function rollback()
    {
        $id = input('id');

        $member = parent::member();
        if ($member['status'] == '1') parent::ajax_exception(000, '您的账号已经被冻结');

        $model = new RechargeModel();
        $model = $model->where('member_id', '=', $member['id'])->where('id', '=', $id)->where('status', '=', '0')->find();
        if (is_null($model)) parent::ajax_exception(000, '该订单已锁定');

        $model->status = '2';
        $model->save();
    }

    //余额记录
    public function expense()
    {
        $member = session('member');

        $where = [
            ['member_id','=',$member['id']],
            ['remind','<>',0]
        ];

        $result = [
            'where' => $where,
        ];

        $model = new MemberRecordModel();
        $result = $this->page($model,$result);

        $type = config('member.record');

        foreach ($result['message'] as &$v)$v['type'] = $type[$v['type']];

        return $result;
    }
}