<?php

namespace app\test\controller;

use classes\index\OrderClass;
use classes\system\SystemClass;
use classes\vendor\JushuitanClass;
use classes\vendor\SmsClass;
use think\Controller;
use think\Request;

class TestController extends Controller
{
    public function index()
    {
        include "area.php";
//        include "area_ext.php";

        //淘宝收货地址页面
        $js_url='https://g.alicdn.com/vip/address/6.0.14/index-min.js';

        $c=new \area();
        $c->setUrl($js_url);
        $c->setIsCountry(false);
        $c->setMakeCsv(true);
        $c->setExtData([]);
        $c->process();
    }

    public function index2()
    {
        $method = 'inventory.query';
        $partnerid = 'c4bee67756d584195e367a8e44dc6f8c';
        $partnerkey = '0951cf9b1b392420f17d788cfd39f7c5';
        $token = '32e8833df97187b82b53f31584716876';
//        $partnerid = 'ywv5jGT8ge6Pvlq3FZSPol345asd';
//        $partnerkey = 'ywv5jGT8ge6Pvlq3FZSPol2323';
//        $token = '181ee8952a88f5a57db52587472c3798';

        $ts = time();

        $data = [
            'page_index' => '1',
            'page_size' => '30',
            'modified_begin' => date('Y-m-d H:i:s', strtotime('-7 day')),
            'modified_end' => date('Y-m-d H:i:s', strtotime('-1 day')),
//            'sku_ids' => 'WA3699-WZ-AK048123',
        ];

        $sign = md5("{$method}{$partnerid}token{$token}ts{$ts}{$partnerkey}");

//        $url = "http://c.sursung.com/api/open/query.aspx?method={$method}&partnerid={$partnerid}&token={$token}&ts={$ts}&sign={$sign}";
        $url = "http://open.erp321.com/api/open/query.aspx?method={$method}&partnerid={$partnerid}&token={$token}&ts={$ts}&sign={$sign}";

        $result = self::url_post($url, json_encode($data));

        dump(json_decode($result, true));

        exit('ok');
    }

    public function file(Request $request)
    {

        $class = new OrderClass();

        $a = $class->file($request);
        dump($a);
        exit('end');
    }

    /**
     * 访问url，post
     *
     * @param $url
     * @param $post_data
     * @return mixed
     */
    public function url_post($url, $post_data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }
}
