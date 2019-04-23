<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/24
 * Time: 下午9:13
 */

namespace classes\withdraw;


use app\member\model\MemberModel;
use app\member\model\MemberRecordModel;
use app\withdraw\model\WithdrawModel;
use classes\AdminClass;
use think\Db;
use think\Request;

class WithdrawClass extends AdminClass
{
    public $model;

    public function __construct()
    {
        $this->model = new WithdrawModel();
    }

    public function index(Request $request)
    {
        $where = [
            //['substation','=', SUBSTATION]

        ];

        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');
        $account = $request->get('account');

        if (!empty($startTime)) {
            $where[] = ['created_at', '>=', $startTime];
        }
        if (!empty($endTime)) {
            $where[] = ['created_at', '<', $endTime];
        }
        if (!empty($account)) {
            $where[] = ['member_account|member_phone', 'like', '%' . $account . '%'];
        }

        return parent::page($this->model, ['substation' => '1','where' => $where]);
    }

    public function status(Request $request)
    {
        Db::startTrans();

        $id = $request->get('id');

        //订单获取
        $order = $this->model->where('id', '=', $id)->find();

        //获取成功
        if (is_null($order)) parent::ajax_exception(0, '订单不存在');

        //未锁定
        if ($order->status != '0') parent::ajax_exception(0, '订单已锁定');

        //新状态获取
        $status = input('value');

        //合法的状态码
        $array = [1, 2];

        //状态码合法
        if (!in_array($status, $array)) parent::ajax_exception(0, '状态错误');

        //获取管理员
        $master = parent::master();

        //修改订单状态
        $order->status = $status;
        $order->change_id = $master['id'];
        $order->change_nickname = $master['nickname'];
        $order->change_date = date('Y-m-d H:i:s');
        $order->save();

        //状态为处理，发放积分
        if ($status == '2') {

            self::integralAdd($order->getData());
        }

        Db::commit();
    }

    private function integralAdd($order)
    {
        //会员寻找与家谱卷添加
        $member = new MemberModel();
        $member = $member->where('id', '=', $order['member_id'])->find();
        if (is_null($member)) return;
        $member->commis += $order['commis'];
        $member->save();

        //会员变更记录
        $record = new MemberRecordModel();
        $record->member_id = $member->id;
        $record->account = $member->account;
        $record->nickname = $member->nickname;
        $record->commis = $order['commis'];
        $record->commis_now = $member->commis;
        $record->commis_all = $member->commis_all;
        $record->type = '30';
        $record->content = '管理员取消了您的提现订单(订单号：' . $order['order_number'] . ')，『佣金』返还：' . $order['commis'];
        $record->created_at = date('Y-m-d H:i:s');
        $record->save();
    }
}