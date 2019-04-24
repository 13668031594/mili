<?php

namespace app\test\controller;

use classes\index\OrderClass;
use classes\system\SystemClass;
use classes\vendor\SmsClass;
use think\Controller;
use think\Request;

class TestController extends Controller
{
    public function index()
    {
        return view('file');
    }

    public function file(Request $request){

        $class = new OrderClass();

        $a = $class->file($request);
        dump($a);
        exit('end');
    }
}
