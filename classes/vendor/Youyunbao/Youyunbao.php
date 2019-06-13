<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/6/13
 * Time: 下午2:52
 */

namespace classes\vendor\Youyunbao;


class Youyunbao
{
    public $config;
    public $appid = null;
    public $appkey = null;
    public $server = null;
    public $reurl = null;
    public $uip = null;
    public $helpts = null;
    public $alipayh5 = null;

    //配置需要的参数
    public function __construct(
        $appid = null,
        $appkey = null,
        $server = 'http://yunpay.waa.cn/',
        $reurl = null,
        $uip = null,
        $helpts = 1,
        $alipayh5 = 1
    )
    {
        $this->appid = $appid;
        $this->appkey = $appkey;
        $this->server = $server;
        $this->reurl = is_null($reurl) ? 'http://' . $_SERVER['SERVER_NAME'] . '/youyunbao' : $reurl;
        $this->uip = is_null($uip) ? self::getIp() : $uip;
        $this->helpts = $helpts;
        $this->alipayh5 = $alipayh5;

        $this->config = [
            'appid' => $this->appid,
            'appkey' => $this->appkey,
            'server' => $this->server,
            'reurl' => $this->reurl,
            'uip' => $this->uip,
            'helpts' => $this->helpts,
            'alipayh5' => $this->alipayh5,
        ];
    }

    //获取客户端IP地址
    public function getIp()
    { //取IP函数
        static $realip;
        if (isset($_SERVER)) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $realip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            } else {
                $realip = isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : $_SERVER['REMOTE_ADDR'];
            }
        } else {
            if (getenv('HTTP_X_FORWARDED_FOR')) {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            } else {
                $realip = getenv('HTTP_CLIENT_IP') ? getenv('HTTP_CLIENT_IP') : getenv('REMOTE_ADDR');
            }
        }
        $realip = explode(",", $realip);

        return $realip[0];
    }

    //数组拼接为url参数形式
    public function urlparams($params)
    {
        $sign = '';
        foreach ($params AS $key => $val) {
            if ($val == '') continue;
            if ($key != 'sign') {
                if ($sign != '') {
                    $sign .= "&";
                }
                $sign .= "$key=$val"; //拼接为url参数形式
            }
        }
        return $sign;
    }


    /* PHP CURL HTTPS POST */
    public function curl_post_https($url, $data)
    { // 模拟提交数据函数
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
            echo 'Errno' . curl_error($curl);//捕抓异常
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据，json格式
    }

    public function parseurl($url = "")
    {
        $url = rawurlencode(mb_convert_encoding($url, 'gb2312', 'utf-8'));
        $a = array("%3A", "%2F", "%40");
        $b = array(":", "/", "@");
        $url = str_replace($a, $b, $url);
        return $url;
    }
}