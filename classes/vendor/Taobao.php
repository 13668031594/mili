<?php

/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/4/24
 * Time: 下午6:33
 */
class Taobao
{
    public function __construct($app_key,$app_secret,$app_session,$app_nick,$tid)
    {
        $para = array(
            'format' => 'json',
            'v' => '2.0',
            'sign_method' => 'md5',
            'app_key' => $app_key,
            'app_secret' => $app_secret,
            'session' => $app_session,
            'app_nick' => $app_nick,
            'app_type' => 'B',
            'method' => 'taobao.trade.get',
            'fields' => 'tid,type,status,payment,orders',
            'tid' => $tid
        );
        $result = self::do_execute($url, $para, $para);
//var_dump($result);exit;
        $arr = json_decode($result);
        print_r($arr);
        exit;
    }

    function do_execute($url, $apiParams, $sign_conf)
    {
        $timestamp = date("Y-m-d H:i:s");
        $apiParams ['timestamp'] = $timestamp;
        $sign_conf['timestamp'] = $timestamp;
        $apiParams ['sign'] = self::createSign($apiParams['app_secret'], $sign_conf); // 签名生成sign

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        // 如果参数为数组则
        if (is_array($apiParams) && 0 < count($apiParams)) {
            $postBodyString = "";
            foreach ($apiParams as $k => $v) {
                $postBodyString .= "$k=" . urlencode($v) . "&";
            }
            unset ($k, $v);
        } else {
            $postBodyString = $apiParams;
        }

        try {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
            $reponse = curl_exec($ch);
            if (curl_errno($ch)) {
                $curl_error = curl_error($ch);
                throw new Exception ($curl_error, 0);
            } else {
                $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if (200 !== $httpStatusCode) {
                    throw new Exception ($reponse, $httpStatusCode);
                }
            }
        } catch (Exception $e) {
        }

        curl_close($ch);
        return $reponse;
    }

    /**
     * 生成签名
     */
    function createSign($appSecret, $paramArr)
    {
        $sign = $appSecret;
        ksort($paramArr);
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $sign .= $key . $val;
            }
        }

        $sign .= $appSecret;
        $sign = strtoupper(md5($sign));
        return $sign;
    }
}
