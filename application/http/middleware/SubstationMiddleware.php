<?php

namespace app\http\middleware;

use app\http\exceptions\RedirectException;
use app\master\model\MasterModel;
use app\substation\model\SubstationModel;

class SubstationMiddleware
{
    public function handle($request, \Closure $next)
    {
        //安全路径
        $safe_path = include 'safe_path.php';

        //获取访问域名
        $localhost = $_SERVER['SERVER_NAME'];

        $substation = '0';
        if (!in_array($localhost, $safe_path)) {

            //该域名不是主站域名，寻找站点id
            $substation_model = new SubstationModel();
            $substation_model = $substation_model->where('localhost', '=', $localhost)->where('status', '=', 'on')->find();

            //没找到该分站或者该分站与管理员分站不符
            if (is_null($substation_model)) self::errors();

            $substation = $substation_model['id'];
        }

        //定义分站常量
        define('SUBSTATION', $substation);

        return $next($request);
    }


    //报错
    private function errors()
    {
        exit('非法域名');
        session('master', null);

        $errors = json_encode([
            'url' => '',
            'message' => '域名不合法',
        ]);

        throw new RedirectException($errors);
    }
}
