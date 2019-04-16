<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/26
 * Time: 下午3:02
 */

namespace app\index\controller;

class BaseController
{
    //head内内容
    public function head()
    {
        return view('head');
    }

    //统一上半部分
    public function top()
    {
        return view('top');
    }

    //版权信息
    public function copyright()
    {
        return view('copyright');
    }

    //个人中心侧面
    public function user_left()
    {
        return view('user_left');
    }

    //在线充值侧面
    public function recharge_left()
    {
        return view('recharge_left');
    }
}