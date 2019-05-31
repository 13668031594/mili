<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/27
 * Time: 下午4:58
 */

namespace classes\index;

use app\avatar\model\AvatarModel;
use app\express\model\ExpressModel;
use app\Member\model\MemberGradeAmountModel;
use app\member\model\MemberGradeExpressModel;
use app\member\model\MemberGradeModel;
use app\member\model\MemberModel;
use app\member\model\MemberRecordModel;
use app\member\model\MemberStoreModel;
use classes\vendor\ExpressAmountClass;
use classes\vendor\GradeAmountClass;
use think\Db;
use think\Request;

class UserClass extends \classes\IndexClass
{
    private $dir = 'member_cover';

    public function __construct()
    {
        if (!is_dir($this->dir)) mkdir($this->dir);//新建文件夹
    }

    public function covers()
    {
        $model = new AvatarModel();

        $covers = $model->where('substation', '=', SUBSTATION)->where('show', '=', 'on')->order('sort', 'desc')->column('location');

        return $covers;
    }

    public function validator_data(Request $request)
    {
        //验证条件
        $rule = [
            'nickname|昵称' => 'require|max:40|min:1',
            'bank_no|交易宝' => 'require|max:40|min:6',
            'bank_name|收款人姓名' => 'require|max:40|min:1',
        ];

        //验证
        $result = parent::validator($request->post(), $rule);

        //有错误报告则报错
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function data(Request $request)
    {
        $member = session('member');

        $model = new MemberModel();
        $member = $model->where('id', '=', $member['id'])->find();

        if ($request->post('bank_no') != $member->bank_no) {

            $pass = $request->post('pass');
            if (empty($pass)) parent::ajax_exception(000, '请输入交易密码');

            if (md5($pass) != $member->pay_pass) parent::ajax_exception(000, '交易密码输入错误');
        }

        $member->nickname = $request->post('nickname');
        $member->bank_no = $request->post('bank_no');
        $member->bank_name = $request->post('bank_name');
        $member->cover = self::file_cover($request);
        $member->updated_at = date('Y-m-d H:i:s');
        $member->save();
    }

    public function validator_pass(Request $request)
    {
        //验证条件
        $rule = [
            'pass|旧交易密码' => 'require|max:20|min:6',
            'new|新交易密码' => 'require|max:20|min:6',
            'again|确认交易密码' => 'require|max:20|min:6',
        ];

        //验证
        $result = parent::validator($request->post(), $rule);

        //有错误报告则报错
        if (!is_null($result)) parent::ajax_exception(000, $result);

        if ($request->post('new') != $request->post('again')) parent::ajax_exception(000, '确认交易密码错误');
    }

    public function pass(Request $request)
    {
        $member = session('member');

        $model = new MemberModel();
        $member = $model->where('id', '=', $member['id'])->find();
        if (md5($request->post('pass')) != $member['password']) parent::ajax_exception(000, '旧密码输入错误');
        $member->password = md5($request->post('new'));
        $member->save();
    }

    public function validator_pay_pass(Request $request)
    {
        //验证条件
        $rule = [
            'pass|原交易密码' => 'require|max:20|min:6',
            'new|新密码' => 'require|max:20|min:6',
            'again|确认密码' => 'require|max:20|min:6',
        ];

        //验证
        $result = parent::validator($request->post(), $rule);

        //有错误报告则报错
        if (!is_null($result)) parent::ajax_exception(000, $result);

        if ($request->post('new') != $request->post('again')) parent::ajax_exception(000, '确认密码错误');
    }

    public function pay_pass(Request $request)
    {
        $member = session('member');

        $model = new MemberModel();
        $member = $model->where('id', '=', $member['id'])->find();
        if (md5($request->post('pass')) != $member['pay_pass']) parent::ajax_exception(000, '原交易密码输入错误');
        $member->pay_pass = md5($request->post('new'));
        $member->save();
    }

    /**
     * 找回密码验证
     *
     * @param Request $request
     */
    public function validator_reset(Request $request)
    {
        $rule = [
            'code|手机验证码' => 'min:5|max:20',
            'pass|密码' => 'require|min:6|max:20',
            'again|确认密码' => 'require|min:6|max:20',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        //验证验证码
        $class = new LoginClass();
        $member = parent::member();
        $request->withPost(array_merge($request->post(), ['phone' => $member['phone']]));
        $class->validator_phone($request);

        if ($request->post('pass') != $request->post('again')) parent::ajax_exception(000, '确认密码有误');
    }

    /**
     * 重置密码
     *
     * @param Request $request
     */
    public function reset(Request $request)
    {
        $member = session('member');
        $model = new MemberModel();
        $model = $model->where('id', '=', $member['id'])->find();
        if (is_null($model)) parent::ajax_exception(000, '会员不存在');
        $model->pay_pass = md5($request->post('pass'));
        $model->save();
    }

    //会员门店
    public function store_table()
    {
        $member = session('member');

        $store = new MemberStoreModel();

        $where = [
            ['member_id', '=', $member['id']]
        ];

        $result = [
            'where' => $where,
            'order_name' => 'sort'
        ];

        $store = parent::page($store, $result);

        foreach ($store['message'] as &$v) {

            $v['created_at'] = date('Y-m-d', strtotime($v['created_at']));
        }

        return $store;
    }

    public function delete()
    {
        $id = input('id');

        $member = session('member');

        $model = new MemberStoreModel();

        $model->where('id', '=', $id)->where('member_id', '=', $member['id'])->delete();
    }

    public function store_show()
    {
        $id = input('id');
        $member = session('member');

        $store = new MemberStoreModel();
        $store = $store->where('member_id', '=', $member['id'])->where('id', '=', $id)->find();
        if (is_null($store)) parent::ajax_exception(000, '请刷新重试');
        $store->show = ($store->show == 'on') ? 'off' : 'on';
        $store->save();
    }

    public function file_cover(Request $request)
    {
        if (!isset($request->file()['file'])) return $request->post('cover');

        $member = parent::member();

        // 获取表单上传文件 例如上传了001.jpg
        $file = $request->file('file');

        $location = 'cover_' . $member['id'];

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size' => (1024 * 1024), 'ext' => 'jpg,png,gif,jpeg,bmp'])->move($this->dir, $location);

        // 上传失败获取错误信息
        if (!$info) parent::ajax_exception(000, $file->getError());

        $url = '/' . $this->dir . '/' . $info->getSaveName();

        return $url;
    }

    //初始资料完善验证
    public function validator_default(Request $request)
    {
        //验证条件
        $rule = [
            'nickname|昵称' => 'require|max:40|min:1',
            'bank_no|支付宝' => 'require|max:40|min:6',
            'bank_name|收款人姓名' => 'require|max:40|min:1',
            'new|新交易密码' => 'require|max:20|min:6',
            'again|确认交易密码' => 'require|max:20|min:6',
        ];

        //验证
        $result = parent::validator($request->post(), $rule);

        //有错误报告则报错
        if (!is_null($result)) parent::ajax_exception(000, $result);

        if ($request->post('new') != $request->post('again')) parent::ajax_exception(000, '确认交易密码错误');
    }

    //初始资料完善
    public function defaults(Request $request)
    {
        $member = session('member');

        $model = new MemberModel();
        $member = $model->where('id', '=', $member['id'])->find();
        $member->nickname = $request->post('nickname');
        $member->bank_no = $request->post('bank_no');
        $member->bank_name = $request->post('bank_name');
        $member->cover = self::file_cover($request);
        $member->pay_pass = md5($request->post('new'));
        $member->updated_at = date('Y-m-d H:i:s');
        $member->save();
    }

    //可以升级的会员等级
    public function grades()
    {
        $member = parent::member();
        $member_grade = new MemberGradeModel();
        $member_grade = $member_grade->where('id', '=', $member['grade_id'])->find();

        $grades = new MemberGradeModel();
        $grades = $grades->where('sort', '>=', $member_grade->sort)->order('sort desc')->column('*');

        $express = new MemberGradeExpressModel();
        $class = new ExpressAmountClass();

        //获取分站的等级信息
        $amount_model = new GradeAmountClass();
        $grade_amount = $amount_model->amount($member_grade['id'], $member_grade['recharge'], $member_grade['buy_total']);

        foreach ($grades as &$v) {


            $v['express'] = $express->where('grade', '=', $v['id'])->column('express,amount', 'express');
            foreach ($v['express'] as $ke => &$va) {

                $amount = $class->amount($ke, $v['id']);
                $va = $amount['amount'];
            }

            $ga = $amount_model->amount($v['id'], $v['recharge'], $v['buy_total']);
            $v['buy_total'] = ($ga['buy_total'] > $grade_amount['buy_total']) ? ($ga['buy_total'] - $grade_amount['buy_total']) : 0;
        }

        return $grades;
    }

    //快递列表
    public function express()
    {
        $model = new ExpressModel();

        $express = $model->where('disabled', '=', 'on')->order('platform', 'asc')->order('sort', 'desc')->column('*');

        return array_values($express);
    }

    //验证会员升级信息
    public function validator_upgrade(Request $request)
    {
        //验证条件
        $rule = [
            'pay_pass|交易密码' => 'require|max:20|min:6',
            'grade|升级等级' => 'require'
        ];

        //验证
        $result = parent::validator($request->post(), $rule);

        //有错误报告则报错
        if (!is_null($result)) parent::ajax_exception(000, $result);

        $member = parent::member();

        if (md5($request->post('pay_pass')) != $member['pay_pass']) parent::ajax_exception(000, '交易密码错误');

        list($grade, $total) = explode('|', $request->post('grade'));

        //当前等级
        $member_grade = new MemberGradeModel();
        $member_grade = $member_grade->where('id', '=', $member['grade_id'])->find();

        //升级等级
        $upgrade = new MemberGradeModel();
        $upgrade = $upgrade->where('id', '=', $grade)->find();

        //高低等级验证
        if ($upgrade->sort < $member_grade->sort) parent::ajax_exception(000, '无法升级到较低等级');

        $amount_class = new GradeAmountClass();
        $member_grades = $amount_class->amount($member_grade['id'], $member_grade['recharge'], $member_grade['buy_total']);
        $upgrades = $amount_class->amount($upgrade['id'], $upgrade['recharge'], $upgrade['buy_total']);

        //升级费用
        $diff_total = ($upgrades['buy_total'] > $member_grades['buy_total']) ? ($upgrades['buy_total'] - $member_grades['buy_total']) : 0;
        if ($diff_total != $total) parent::ajax_exception(000, '请刷新重试total');

        if ($member['remind'] < $total) parent::ajax_exception(000, '余额不足');

        //反馈升级所需信息
        return [
            'grade_id' => $upgrade->id,
            'grade_name' => $upgrade->name,
            'total' => $total,
        ];
    }

    //自主升级
    public function upgrade($info)
    {
        $grade_id = $info['grade_id'];
        $grade_name = $info['grade_name'];
        $total = $info['total'];

        $members = parent::member();
        $old = $members['grade_name'];

        Db::startTrans();

        $member = new MemberModel();
        $member = $member->where('id', '=', $members['id'])->find();
        $member->remind -= $total;
        $member->grade_id = $grade_id;
        $member->grade_name = $grade_name;
        $member->save();

        //添加会员钱包记录
        $record = new MemberRecordModel();
        $record->member_id = $member->id;
        $record->account = $member->account;
        $record->nickname = $member->nickname;
        $record->content = '购买会员升级，会员等级『' . $old . '』->『' . $grade_name . '』消耗余额『' . $total . '』';
        $record->remind = 0 - $total;
        $record->commis_now = $member->commis;
        $record->commis_all = $member->commis_all;
        $record->remind_now = $member->remind;
        $record->remind_all = $member->remind_all;
        $record->type = 70;
        $record->created_at = date('Y-m-d H:i:s');
        $record->save();

        Db::commit();
    }
}