<?php

namespace app\test\controller;

use classes\system\SystemClass;
use classes\vendor\SmsClass;
use think\Controller;
use think\Request;

class TestController extends Controller
{
    public function index()
    {
        $setting = new SystemClass();
        $set = $setting->index();
        $class = new SmsClass();
        $class->TemplateParam = [
            'username' =>'超级无敌洋',
            'order' => 'abcdefg',
            'web' => $set['webName']
        ];
        $result = $class->sendSms('13608302076', '123321123', 'SMS_151996093');

        dump($result);
    }

}
