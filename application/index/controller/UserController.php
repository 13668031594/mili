<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/27
 * Time: 下午4:58
 */

namespace app\index\controller;


use classes\index\LoginClass;
use classes\index\UserClass;
use classes\member\MemberStoreClass;
use think\Request;

class UserController extends \app\http\controller\IndexController
{
    private $class;

    public function __construct()
    {
        parent::__construct();

        $this->class = new UserClass();
    }

    //个人中心
    public function getUser()
    {
        return parent::view('user', ['choice' => '/user', 'host' => request()->host()]);
    }

    //完善资料
    public function getData()
    {
        $covers = $this->class->covers();

        return parent::view('data', ['choice' => '/data', 'covers' => $covers]);
    }

    //完善资料
    public function postData(Request $request)
    {
        $this->class->validator_data($request);

        $this->class->data($request);

        return parent::success();
    }

    //修改密码页面
    public function getPassword()
    {
        return parent::view('change-password', ['choice' => '/change-password']);
    }

    //修改密码
    public function postPassword(Request $request)
    {
        $this->class->validator_pass($request);

        $this->class->pass($request);

        return parent::success();
    }

    //修改交易密码页面
    public function getPay()
    {
        return parent::view('pay-password', ['choice' => '/pay-password']);
    }

    //修改交易密码
    public function postPay(Request $request)
    {
        $this->class->validator_pay_pass($request);

        $this->class->pay_pass($request);

        return parent::success();
    }

    //交易密码找回页面
    public function getReset()
    {
        return parent::view('reset-pay');
    }

    //交易密码找回
    public function postReset(Request $request)
    {
        $this->class->validator_reset($request);

        $this->class->reset($request);

        return parent::success();
    }

    //交易密码找回短信
    public function getResetSms()
    {
        //当前时间戳
        $time = time();

        $member = $this->class->member();

        $class = new LoginClass();

        //验证
        $class->validator_sms_reset($member['phone'], $time);

        //发送
        $end = $class->send_sms($member['phone'], $time, '『交易密码找回』');

        //反馈
        return parent::success('', '发送成功', ['time' => $end]);
    }

    //店铺页面
    public function getStore()
    {
        $result = [
            'platform' => str_replace('"', "'", json_encode(config('member.store_platform'))),
            'platform_array' => config('member.store_platform'),
            'choice' => '/store',
        ];

        return parent::view('store', $result);
    }

    //店铺数据
    public function getStoreTable()
    {
        $result = $this->class->store_table();

        return parent::tables($result);
    }

    //店铺修改
    public function postStore(Request $request)
    {
        $class = new MemberStoreClass();

        $class->validator_save($request);

        $class->save($request);

        return parent::success('/store');
    }

    //店铺删除
    public function getStoreDelete()
    {
        $this->class->delete();

        return parent::success('/store');
    }

    //显示或隐藏
    public function getStoreShow()
    {
        $this->class->store_show();

        return parent::success('/store');
    }

    //会员升级
    public function getUpgrade()
    {
        $grades = $this->class->grades();

        $express = $this->class->express();

        $platform = config('member.store_platform');

        $result = [
            'choice' => '/upgrade',
            'grades' => $grades,
            'express' => $express,
            'platform' => $platform,
        ];

        return parent::view('upgrade', $result);
    }

    public function getDefault()
    {
        $covers = $this->class->covers();

        return parent::view('default', ['covers' => $covers, 'choice' => '', 'host' => request()->host()]);
    }

    //全面完善资料
    public function postDefault(Request $request)
    {
        $this->class->validator_data($request);
        $this->class->validator_pay_pass($request);
        $this->class->data($request);
        $this->class->pay_pass($request);

        return parent::success('/');
    }

    public function postUpgrade(Request $request)
    {
        $info = $this->class->validator_upgrade($request);

        $this->class->upgrade($info);

        exit ("<script>alert('升级成功');location.href='".$_SERVER["HTTP_REFERER"]."';</script>");
//        return parent::success('/upgrade');
    }
}