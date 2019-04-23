<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/27
 * Time: 下午6:31
 */

namespace classes\index;


use app\member\model\MemberModel;
use app\member\model\MemberRecordModel;
use app\withdraw\model\WithdrawModel;
use think\Db;
use think\Request;

class AgentClass extends \classes\IndexClass
{
    //累计佣金
    public function commis_all()
    {
        $member = session('member');

        $model = new MemberRecordModel();

        return $model->where('member_id', '=', $member['id'])->where('type', '=', 40)->sum('commis');
    }

    //我的下级
    public function son()
    {
        $member = session('member');

        $result = [
            'where' => [['referee_id', '=', $member['id']]],
            'column' => 'account,phone,nickname,remind_all,created_at'
        ];

        $model = new MemberModel();

        return parent::page($model, $result);
    }

    public function validator_draw(Request $request)
    {
        //验证条件
        $rule = [
            'number|使用佣金' => 'require|integer|between:1,100000000',
            'pay|支付密码' => 'require',
            'type|使用类型' => 'require|in:1,2',
        ];

        //验证
        $result = parent::validator($request->post(), $rule);
        //有错误报告则报错
        if (!is_null($result)) parent::ajax_exception(000,$result);

        $member = parent::member();

        if (md5($request->post('pay')) != $member['pay_pass'])parent::ajax_exception(000,'支付密码输入错误');
    }

    public function draw(Request $request)
    {
        $member = parent::member();

        $number = $request->post('number');

        if ($number > $member['commis'])parent::ajax_exception(000,'佣金不足');
        if (empty($member['bank_no']))parent::ajax_exception(000,'请先填写收款账号');

        $date = date('Y-m-d H:i:s');

        Db::startTrans();

        $draw = new WithdrawModel();
        $draw->order_number = self::new_order();
        $draw->total = $number;
        $draw->commis = $number;
        $draw->member_id = $member['id'];
        $draw->member_nickname = $member['nickname'];
        $draw->member_create = $member['created_at'];
        $draw->member_account = $member['account'];
        $draw->member_phone = $member['phone'];
        $draw->member_bank_no = $member['bank_no'];
        $draw->member_bank_name = $member['bank_name'];
        $draw->created_at = $date;
        $draw->substation = SUBSTATION;
        $draw->save();

        //扣除会员余额
        $members = new MemberModel();
        $member = $members->where('id', '=', $member['id'])->find();
        $member->commis -= $number;
        $member->save();

        //添加会员钱包记录
        $record = new MemberRecordModel();
        $record->member_id = $member->id;
        $record->account = $member->account;
        $record->nickname = $member->nickname;
        $record->content = '申请提现（订单号：' . $draw->order_number . '）,扣除佣金：' . $number;
        $record->commis = 0 - $number;
        $record->commis_now = $member->commis;
        $record->commis_all = $member->commis_all;
        $record->remind_now = $member->remind;
        $record->remind_all = $member->remind_all;
        $record->type = 30;
        $record->created_at = $date;
        $record->save();
        //添加会员钱包记录结束

        Db::commit();
    }

    public function transfer(Request $request)
    {
        $member = parent::member();

        $number = $request->post('number');

        if ($number > $member['commis'])parent::ajax_exception(000,'佣金不足');

        $date = date('Y-m-d H:i:s');

        Db::startTrans();

        //扣除会员余额
        $members = new MemberModel();
        $member = $members->where('id', '=', $member['id'])->find();
        $member->commis -= $number;
        $member->remind += $number;
        $member->remind_all += $number;
        $member->save();

        //添加会员钱包记录
        $record = new MemberRecordModel();
        $record->member_id = $member->id;
        $record->account = $member->account;
        $record->nickname = $member->nickname;
        $record->content = '佣金转入,扣除佣金：' . $number.'，转入余额：'.$number;
        $record->commis = 0 - $number;
        $record->commis_now = $member->commis;
        $record->commis_all = $member->commis_all;
        $record->remind = $number;
        $record->remind_now = $member->remind;
        $record->remind_all = $member->remind_all;
        $record->type = 30;
        $record->created_at = $date;
        $record->save();
        //添加会员钱包记录结束

        Db::commit();
    }

    //提成记录
    public function withdraw()
    {
        $member = session('member');

        $where[] = ['member_id', '=', $member['id']];

        $result = [
            'where' => $where,
        ];

        return parent::page(new WithdrawModel(), $result);
    }

    //佣金记录数据
    public function commis()
    {
        $member = session('member');

        $where = [
            ['member_id', '=', $member['id']],
            ['commis', '<>', 0]
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

    //订单号
    private function new_order()
    {
        $pattern = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';//字幕字符串

        $key = 'W' . time();//时间戳

        //再随机2位字幕
        for ($i = 0; $i < 2; $i++) {
            $key .= $pattern[rand(0, 25)];    //生成php随机数
        }

        //验证订单号是否被占用
        $test = new WithdrawModel();
        $test = $test->where('order_number', '=', $test)->find();

        if (!is_null($test)) {

            return self::new_order();
        } else {

            return $key;
        }
    }

}