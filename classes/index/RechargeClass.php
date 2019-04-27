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
use app\recharge\model\RechargeOrderModel;
use classes\system\SystemClass;
use think\Request;

class RechargeClass extends \classes\IndexClass
{
    public function order()
    {
        $member = parent::member();

        $order = new RechargeOrderModel();
        $order = $order->where('member_id', '=', $member['id'])->where('status', '=', 0)->find();

        if (is_null($order)) {

            $order = new RechargeOrderModel();
            $order->member_id = $member['id'];
            $order->created_at = date('Y-m-d H:i:s');
            $order->substation = SUBSTATION;
            $order->save();
            $order->order_number = 'R' . (37957 + $order->id);
            $order->save();
        }

        return $order->order_number;
    }

    public function validator_recharge(Request $request)
    {
        $rule = [
            'total|充值金额' => 'require|integer',
            'order|单号' => 'require',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        $set = new SystemClass();
        $set = $set->index();

        $total = $request->post('total');

        if ($total < $set['rechargeBase']) parent::ajax_exception(000, '充值金额不得小于：' . $set['rechargeBase']);

        if ($total % $set['rechargeTimes']) parent::ajax_exception(000, '充值金额必须为：' . $set['rechargeTimes'] . '的正整数倍');

        $member = parent::member();
        $test = new RechargeModel();
        $test = $test->where('member_id', '=', $member['id'])->where('status', '=', 0)->find();
        if (!is_null($test)) parent::ajax_exception(000, '您还有未处理的充值订单');

        $order_number = $request->post('order');
        $order = new RechargeOrderModel();
        $order = $order->where('order_number', '=', $order_number)->find();
        if (!is_null($order)) {

            $order->status = 1;
            $order->save();
        }

        $test = new RechargeModel();
        $test = $test->where('order_number', '=', $order_number)->find();
        if (!is_null($test)) {

            parent::ajax_exception(000, '请刷新重试');
        }
    }

    public function recharge(Request $request)
    {
        $member = parent::member();

        $recharge = new RechargeModel();
        $recharge->order_number = $request->post('order');
        $recharge->total = $request->post('total');
        $recharge->remind = $recharge->total;
        $recharge->member_id = $member['id'];
        $recharge->member_account = $member['account'];
        $recharge->member_phone = $member['phone'];
        $recharge->member_nickname = $member['nickname'];
        $recharge->member_create = $member['created_at'];
        $recharge->created_at = date('Y-m-d H:i:s');
        $recharge->updated_at = date('Y-m-d H:i:s');
        $recharge->substation = SUBSTATION;
        $recharge->save();
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
            ['member_id', '=', $member['id']],
            ['remind', '<>', 0]
        ];

        $result = [
            'where' => $where,
        ];

        $model = new MemberRecordModel();
        $result = $this->page($model, $result);

        $type = config('member.record');

        foreach ($result['message'] as &$v) $v['type'] = $type[$v['type']];

        return $result;
    }
}