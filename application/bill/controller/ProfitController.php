<?php

namespace app\bill\controller;

use app\http\controller\AdminController;
use classes\bill\ProfitClass;
use think\Controller;
use think\Request;

class ProfitController extends AdminController
{
    public $class;

    public function __construct()
    {
        $this->class = new ProfitClass();
    }

    public function getIndex()
    {
        $platform = config('member.store_platform');
        $express = $this->class->express();
        $goods_class = $this->class->goods_class();

        $result = [
            'platform' => $platform,
            'express' => $express,
            'goods_class' => $goods_class,
        ];

        return parent::view('profit', $result);
    }

    public function postIndex(Request $request)
    {
        $result = $this->class->profit($request);

        $platform = config('member.store_platform');
        $express = $this->class->express();
        $goods_class = $this->class->goods_class();

        $result = array_merge($result,[
            'platform' => $platform,
            'express' => $express,
            'goods_class' => $goods_class,
        ]);

        $result['data'] = $request->post();

        return parent::view('index', $result);
    }
}
