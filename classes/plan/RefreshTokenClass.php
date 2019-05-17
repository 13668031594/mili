<?php

namespace classes\plan;


use classes\system\JushuitanClass;

class RefreshTokenClass
{
    public function __construct()
    {
        $set = new JushuitanClass();
        $set = $set->index();

        //每年5月1日刷新
        $date = date('m-d');
        if ($date != $set['jushuitanRefreshToken']) return;

        //获取缓存
        $cache = cache('refresh_token');

        //有缓存，代表已经刷新过了
        if ($cache) return;

        //初始化聚水潭class
        $class = new \classes\vendor\JushuitanClass();

        //调用刷新token方法
        $result = $class->refresh_token();

        //缓存刷新时间，持续1天
        if ($result['code'] == 0) cache('refresh_token', date('Y-m-d H:i:s'), (60 * 60 * 24));

        //打印结果
        dump('refresh_token');
        dump($result);
    }
}