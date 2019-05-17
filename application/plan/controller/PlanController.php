<?php

namespace app\plan\controller;

use classes\plan\OrderSendClass;
use classes\plan\OrderUploadClass;
use classes\plan\RefreshTokenClass;
use think\Controller;
use think\Db;

class PlanController extends Controller
{
    public function index()
    {
        set_time_limit (0);

        Db::startTrans();

        //刷新聚水潭token
        new RefreshTokenClass();

        //同步订单号
        new OrderSendClass();

        //自动上传订单
        new OrderUploadClass();

        Db::commit();

        exit('ok');
    }
}
