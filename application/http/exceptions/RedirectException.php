<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/10/18
 * Time: 下午12:01
 */

namespace app\http\exceptions;

use think\Exception;

class RedirectException extends Exception
{
    public function render()
    {
        //获取参数
        $message = json_decode(parent::getMessage(), true);

        //重定向路由
        $url = $message['url'];

        //错误参数
        $errors = $message['message'];

        //保存错误参数，重定向时使用
        session('errors', $errors);

        //重定向
        return redirect(url($url, '', false));
    }
}