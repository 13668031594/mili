<?php

namespace app\plan\controller;

use classes\plan\OrderSendClass;
use classes\system\SystemClass;
use classes\vendor\JushuitanClass;
use think\Controller;
use think\Db;
use think\Request;

class PlanController extends Controller
{
    public function index()
    {
        Db::startTrans();
        //刷新聚水潭token
        self::refresh_token();

        new OrderSendClass();

        Db::commit();

        exit('ok');
    }

    //刷新token
    private function refresh_token()
    {
        $set = new SystemClass();
        $set = $set->index();

        //每年5月1日刷新
        $date = date('m-d');
        if ($date != $set['jushuitanRefreshToken']) return;

        //获取缓存
        $cache = cache('refresh_token');

        //有缓存，代表已经刷新过了
        if ($cache) return;

        //初始化聚水潭class
        $class = new JushuitanClass();

        //调用刷新token方法
        $result = $class->refresh_token();

        //缓存刷新时间，持续1天
        if ($result['code'] == 0) cache('refresh_token', date('Y-m-d H:i:s'), (60 * 60 * 24));

        //打印结果
        dump('refresh_token');
        dump($result);
    }
}
