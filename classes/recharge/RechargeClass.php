<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/24
 * Time: 下午9:13
 */

namespace classes\recharge;


use app\member\model\MemberGradeModel;
use app\member\model\MemberModel;
use app\member\model\MemberRecordModel;
use app\recharge\model\RechargeModel;
use classes\AdminClass;
use classes\system\SystemClass;
use think\Db;
use think\Request;

class RechargeClass extends AdminClass
{
    public $model;

    public function __construct()
    {
        $this->model = new RechargeModel();
    }

    public function index(Request $request)
    {
        $where = [];

        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');
        $status = $request->get('status');
        $keyword = $request->get('keyword');
        $keywordType = $request->get('keywordType');

        if (!empty($startTime)) {
            $where[] = ['created_at', '>=', $startTime];
        }
        if (!empty($endTime)) {
            $where[] = ['created_at', '<', $endTime];
        }
        if (!empty($keyword)) {
            switch ($keywordType) {
                case '0':
                    $where[] = ['member_account|member_phone', 'like', '%' . $keyword . '%'];
                    break;
                case '1':
                    $where[] = ['order_number', 'like', '%' . $keyword . '%'];
                    break;
                default:
                    break;
            }
        }
        if (!empty($status) || ($status == '0')) {
            $where[] = ['status', '=', $status];
        }


        return parent::page($this->model, ['where' => $where]);
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
        $array = [1, 3];

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
        if ($status == '1') {

            self::integralAdd($order->getData());
            self::levelUp($order->getData());
        }

        Db::commit();
    }

    private function integralAdd($order)
    {
        //会员寻找与家谱卷添加
        $member = new MemberModel();
        $member = $member->where('id', '=', $order['member_id'])->find();
        if (is_null($member)) return;
        $member->remind += $order['remind'];
        $member->remind_all += $order['remind'];
        $member->total += $order['total'];
        $member->save();

        //会员变更记录
        $record = new MemberRecordModel();
        $record->member_id = $member->id;
        $record->account = $member->account;
        $record->nickname = $member->nickname;
        $record->remind = $order['remind'];
        $record->remind_now = $member->remind;
        $record->remind_all = $member->remind_all;
        $record->commis_now = $member->commis;
        $record->commis_all = $member->commis_all;
        $record->type = '20';
        $record->content = '管理员处理了您的充值订单(订单号：' . $order['order_number'] . ')，『余额』增加：' . $order['remind'];
        $record->created_at = date('Y-m-d H:i:s');
        $record->save();

        //没有有上级
        if (empty($member->referee_id)) return;

        //寻找上级
        $referee = new MemberModel();
        $referee = $referee->where('id', '=', $member->referee_id)->find();
        if (is_null($referee)) return;//没找到上级

        //计算佣金
        $setting = new SystemClass();
        $set = $setting->index();
        $number = number_format(($set['userCommiss'] * $order['remind'] / 10000), 2, '.', '');
        if ($number <= 0) return;//没有佣金

        //增加佣金
        $referee->commis += $number;
        $referee->commis_all += $number;
        $referee->save();

        //会员变更记录
        $record = new MemberRecordModel();
        $record->member_id = $referee->id;
        $record->account = $referee->account;
        $record->nickname = $referee->nickname;
        $record->commis = $number;
        $record->commis_now = $referee->commis;
        $record->commis_all = $referee->commis_all;
        $record->remind_now = $referee->remind;
        $record->remind_all = $referee->remind_all;
        $record->type = '40';
        $record->content = '管理员处理了您的下级『' . $member->nickname . '』的充值订单(订单号：' . $order['order_number'] . ')，您获得『佣金』' . $number;
        $record->created_at = date('Y-m-d H:i:s');
        $record->save();
    }

    //充值送会员
    private function levelUp($order)
    {
        $set = new SystemClass();
        $set = $set->index();

        if ($set['rechargeGradeSwitch'] != 'on')return;

        //会员寻找与家谱卷添加
        $member = new MemberModel();
        $member = $member->where('id', '=', $order['member_id'])->find();
        if (is_null($member)) return;

        $selfGrade = new MemberGradeModel();
        $selfGrade = $selfGrade->find($member->grade_id);

        $recharge = new RechargeModel();
        $recharge = $recharge->where('member_id','=',$member->id)->where('status','=',1)->sum('total');

        $grade = new MemberGradeModel();
        $grade = $grade->where('sort','>',$selfGrade->sort)->where('recharge','<=',$recharge)->order('sort','desc')->find();
        if (!is_null($grade)){

            $member->grade_id = $grade->id;
            $member->grade_name = $grade->name;
            $member->save();
        }
    }
}