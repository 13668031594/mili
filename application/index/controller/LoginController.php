<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/26
 * Time: 下午2:33
 */

namespace app\index\controller;

use classes\index\LoginClass;
use think\Request;

class LoginController extends \app\http\controller\IndexController
{
    private $class;

    public function __construct()
    {
        parent::__construct();

        $this->class = new LoginClass();
    }

    //登录页面
    public function getLogin()
    {
        return parent::view('login');
    }

    //登录
    public function postLogin(Request $request)
    {
        $this->class->validator_login($request);

        $member = $this->class->login($request);

        $this->class->refresh_member($member);

        if (empty($member->bank_no) || empty($member->bank_name)){

            return redirect('/default');
        }else{

            return redirect('/');
        }
    }

    //注销
    public function getLogout()
    {
        $this->class->logout();

        return self::getLogin();
    }

    //注册页面
    public function getReg()
    {
        $referee = input('referee');

        return parent::view('register', ['referee' => $referee]);
    }

    //注册
    public function postReg(Request $request)
    {
        $this->class->validator_reg($request);

        $this->class->reg($request);

        return parent::success('/login', '注册成功');
    }

    //密码找回页面
    public function getReset()
    {
        return parent::view('reset');
    }

    //密码找回
    public function postReset(Request $request)
    {
        $this->class->validator_reset($request);

        $this->class->reset($request);

        return parent::success('/login');
    }

    //短信发送
    public function getRegSms($phone)
    {
        //当前时间戳
        $time = time();

        //验证
        $this->class->validator_sms_reg($phone, $time);

        //发送
        $end = $this->class->send_sms($phone, $time);

        //反馈
        return parent::success('', '发送成功', ['time' => $end]);
    }

    //密码找回短信
    public function getResetSms($phone)
    {
        //当前时间戳
        $time = time();

        //验证
        $this->class->validator_sms_reset($phone, $time);

        //发送
        $end = $this->class->send_sms($phone, $time, 'SMS_151773748');

        //反馈
        return parent::success('', '发送成功', ['time' => $end]);
    }

}