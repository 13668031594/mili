<?php

namespace app\http\middleware;

use app\http\exceptions\RedirectException;
use app\master\model\MasterModel;
use app\substation\model\SubstationModel;
use think\Route;

class MasterLoginMiddleware
{
    public function handle($request, \Closure $next)
    {
        //尝试获取session中的master信息
        $master = session('master');
//dump($master);
//exit;
        //验证session中的信息格式与过期时间
        if (is_null($master) || !is_array($master) || !isset($master['id']) || !isset($master['login_ass']) || !isset($master['time']) || ($master['time'] < time())) self::errors();

        //初始化管理员模型
        $masters = new MasterModel();

        //尝试获取管理员资料
        $masters = $masters->where('id', '=', $master['id'])->find();

        //没有获取到管理员资料，跳转至登录页面
        if (is_null($masters)) self::errors();

        if (SUBSTATION == '0') {

            if ($masters['substation'] != '0') self::errors();
        } else {

            $substation_model = new SubstationModel();
            $substation_model = $substation_model->where('id', '=', SUBSTATION)->where('status', '=', 'on')->find();

            //没找到该分站或者该分站与管理员分站不符
            if (is_null($substation_model) || (($substation_model['id'] != $masters['substation']) &&
                    ($substation_model['pid'] != $masters['substation']) &&
                    ($substation_model['top'] != $masters['substation']))) self::errors();
        }


        //登录密钥验证
        if ($master['login_ass'] != $masters->login_ass) self::errors();

        //更新操作时间
        $master['time'] = time() + config('young.admin_login_time');
        session('master', $master);

        return $next($request);
    }

    //报错
    private function errors()
    {
        session('master', null);

        $errors = json_encode([
            'url' => '/admin/login',
            'message' => '请重新登录',
        ]);

        throw new RedirectException($errors);
    }
}
