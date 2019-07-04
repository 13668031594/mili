<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/6/13
 * Time: 下午3:34
 */

namespace classes\vendor\Youyunbao;

use app\Substation\model\SubstationModel;
use app\Youyunbao\model\YouyunbaoOrderModel;
use classes\system\SystemClass;

class YouyunbaoClass
{
    public $config;

    public function __construct($set = null)
    {
        $reurl = null;
        if (is_null($set)) {

            $set = new SystemClass();
            $set = $set->index();
        } else {

            $substation = new SubstationModel();
            $self = $substation->find(SUBSTATION);

            if (!empty($self->pid)) {

                $substation = $substation->find($self->pid);
                $reurl = 'http://' . $substation->localhost . '/youyunbao';
            } else {

                $safe_path = include 'safe_path.php';

                $url = $safe_path[0];

                $reurl = 'http://' . $url . '/youyunbao';
            }
        }

        $appid = $set['youAppid'];
        $appkey = $set['youAppkey'];

        $this->config = new Youyunbao($appid, $appkey, $reurl);
    }

    public function codepay($money, $order, $type, $recharge_id, $recharge = 0)
    {
        error_reporting(0);//PHP报错不显示
        header("content-Type: text/html; charset=Utf-8");
        //导入配置文件 一般配置这个文件即可 如果你是高手任你发挥

        $congig = $this->config->config;

        //将数据列入数组
        $yundata = array(
            "appid" => $congig['appid'],
            "data" => $order,//网站订单号/或者账号
            "money" => number_format($money, 2, ".", ""),//注意金额一定要格式化否则token会出现错误
            "type" => (int)$type,
            "uip" => $congig['uip'],
        );
        /*
        token签名规则 注意顺序不能乱
        //金额格式 例如正确格式(10.00  100.01  0.01)  金额必须格式化否则token签名会失败
        错误的格式 (10  20  500  2)
        */
        $token = array(
            "appid" => $congig['appid'],//APPID号码
            "data" => $yundata["data"],//数据单号
            "money" => $yundata["money"],//金额
            "type" => $yundata["type"],//类别
            "uip" => $congig['uip'],//客户IP
            "appkey" => $congig['appkey']//appkey密匙
        );

        /*
        token签名MD5加密
        将字符串进行MD5加密
        md5(appid=88888888&data=222222&money=100.00&type=1&uip=127.0.0.1&appkey=xxxxxxx)
        签名一律小写 例如 ：528a657d628395de403d4d152d658073
        */
        $token = md5($this->config->urlparams($token));
        $postdata = $this->config->urlparams($yundata) . '&token=' . $token;

        if ($congig['alipayh5'] == 1 && $yundata["type"] == 1) {//仅限支付宝
            //启用本地备注模式
            /*$order_data = base64_encode($yundata["data"] . ',' . $yundata["money"]);//将数据进行base64编码
            $qrcode = 'http://' . $_SERVER['HTTP_HOST'] . '/alipayh5?data=' . $order_data . '';//本地自动生成二维码地址
                        $sdata = array('state' => 1, 'qrcode' => $qrcode, 'youorder' => $yundata["data"], 'data' => $yundata["data"], 'money' => $yundata["money"], 'times' => time() + 300, 'orderstatus' => 0, 'text' => 10089); //本地生成二维码可手动伪造JSON数据

            */
            //启用本地备注模式
            $h5yundata = array("appid" => $congig['appid'], "data" => $yundata['data'], "money" => $yundata['money'], "atype" => 1, "type" => 1);
            $h5token = array("appid" => $congig['appid'], "data" => $yundata['data'], "money" => $yundata['money'], "type" => 1, "appkey" => $congig['appkey']);
            $h5token = md5($this->config->urlparams($h5token));
            $h5postdata = $this->config->urlparams($h5yundata) . '&token=' . $h5token;
            $h5fdata = $this->config->curl_post_https($congig['server'] . 'Alipay', $h5postdata);
            $h5sdata = json_decode($h5fdata, true);//将json代码转换为数组
            if ($h5sdata['state'] == 0) {
                exit($h5sdata['text']);
            }
            $h5sdata = $h5sdata['text'];
            $qrcode = 'alipays://platformapi/startapp?appId=20000123&actionType=scan&biz_data={"s":"money","u": "' . $h5sdata['alipayid'] . '","a": "' . $h5sdata['money'] . '","m":"' . $h5sdata['data'] . '"}';
            //$qrcode ='alipays://platformapi/startapp?appId=09999988&actionType=toAccount&goBack=NO&amount='.$h5sdata['money'].'&userId='.$h5sdata['alipayid'].'&memo='.$h5sdata['data'].'';

            $sdata = array('state' => 1, 'qrcode' => $qrcode, 'youorder' => $yundata["data"], 'data' => $yundata["data"], 'money' => $yundata["money"], 'times' => (time() + 300), 'orderstatus' => 0, 'text' => 10089); //本地生成二维码可手动伪造JSON数据
        } else {
            //否则走云端
            $fdata = $this->config->curl_post_https($congig['server'], $postdata);//发送数据到网关
            $sdata = json_decode($fdata, true);//将json代码转换为数组
        }
        /*返回的json参数
        {"state":"1","qrcode":"二维码","youorder":"token","data":"data","money":"10.00","times":"1531384783","orderstatus":"0","text":"10089"}
        state = 1 为成功获取二维码数据  0表示异常 请看错误代码
        */
        $state = $sdata["state"];//状态 1 ok   0有错误

        if (!$state) {
            exit('异常' . $sdata["text"]);
        }

        $from = 'wechat';
        if ($yundata['type'] == 1) $from = 'alipay';
        if ($yundata['type'] == 2) $from = 'qq';

        //添加本地记录
        $model = new YouyunbaoOrderModel();
        $model->state = $sdata['state'];
        $model->qrcode = $sdata['qrcode'];
        $model->order = $sdata['order'];
        $model->datas = $sdata['data'];
        $model->money = $sdata['money'];
        $model->times = $sdata['times'];
        $model->orderstatus = $sdata['orderstatus'];
        $model->text = $sdata['text'];
        $model->member_id = $recharge_id;
        $model->created_at = date('Y-m-d H:i:s');
        $model->substation = SUBSTATION;
        $model->recharge_type = $recharge;
        $model->from = $from;
        $model->save();

        $qrcode = $sdata["qrcode"];//二维码

        $times = $sdata["times"] - time(); //有效时间减去当前时间 保留一分钟减去60秒

        $moneys = $sdata["money"];//实际付款金额

        $orderstatus = $sdata["orderstatus"];//付款状态 1ok  0等待付款

        $data = $sdata["data"];//传递的订单号

        $order = $sdata["order"];//云端分配的唯一订单号 通过这个订单号查询状态
//
        if ($yundata["type"] == 1) {
            $logo = 'template/Image/zfb.png';
            $title = '支付宝';
            $text = '支付宝扫一扫付款（手机上可以直接启动APP，或者截图相册识别）';
            $tishi = '<div style="position:relative;width:300px;height:341px;margin:0 auto;border:1px solid #e4e3e3"><img src="template/Image/zfbbg.png" alt="" /><div style="position:absolute;width:100px;height:100px;z-indent:2;left:35;top:210;font-size:48px;color:#F00">' . $moneys . '</div></div>';
            //如果你只使用支付宝 固定金额 可以做成自动启动支付宝APP  具体查阅开发文档或询问技术
        } elseif ($yundata["type"] == 2) {
            $logo = 'template/Image/qq.png';
            $title = 'QQ钱包';
            $text = 'QQ钱包扫一扫付款（QQ中可长按识别，或者截图相册识别）';
            $tishi = '<div style="position:relative;width:300px;height:360px;margin:0 auto;border:1px solid #e4e3e3"><img src="template/Image/qqbg.png" alt="" /><div style="position:absolute;width:100px;height:100px;z-indent:2;left:35;top:220;font-size:48px;color:#F00">' . $moneys . '</div></div>';
        } elseif ($yundata["type"] == 3) {
            $logo = 'template/Image/wx.png';
            $title = '微信支付';
            $text = '微信扫一扫付款（微信中可长按识别，或者截图相册识别）';
            $tishi = '<div style="position:relative;width:300px;height:331px;margin:0 auto;border:1px solid #e4e3e3"><img src="template/Image/wxbg.png" alt="" /><div style="position:absolute;width:100px;height:100px;z-indent:2;left:65;top:200;font-size:48px;color:#F00">' . $moneys . '</div></div>';

        } elseif ($yundata["type"] == 4) {
            $logo = 'template/Image/ysf.png';
            $title = '云闪付';
            $text = '银联云闪付扫一扫付款';
            $tishi = '';
        }

        return [
            'config' => $congig,
            'money' => $money,
            'qrcode' => $qrcode,
            'times' => $times,
            'orderstatus' => $orderstatus,
            'data' => $data,
            'order' => $order,
            'logo' => $logo,
            'title' => $title,
            'text' => $text,
            'tishi' => $tishi,
            'type' => $yundata["type"],

        ];
    }

    public function alipayh5()
    {
        $congig = $this->config->config;

        //导入配置文件 一般配置这个文件即可 如果你是高手任你发挥
        $base = base64_decode($_REQUEST['data']);
        if (!$base) {
            exit('error data');
        }
//        dd($_REQUEST['data']);
        $base = explode(',', $base);
        //将主要数据列入数组
        $yundata = array(
            "appid" => $congig['appid'],//获取appid
            "data" => $base[0],//数据单号
            "money" => $base[1],//金额
            "atype" => 1,//H5模式1
            "type" => 1
        );

        //订单查询签名格式
        //$sing = md5('appid='.$appid.'&data='.$data.'&money='.$money.'&type='.$type.'&appkey='.&appkey.'');
        //以上是token签名规则
        $token = array(
            "appid" => $congig['appid'],//APPID号码
            "data" => $yundata['data'],//数据单号
            "money" => $yundata['money'],//金额
            "type" => 1,//支持支付宝
            "appkey" => $congig['appkey']//appkey密匙
        );

        //加密token 32位  小写
        $token = md5($this->config->urlparams($token));
        //exit($token);
        //重组条件
        $postdata = $this->config->urlparams($yundata) . '&token=' . $token;

        //订单查询网关地址后面加 order
        $fdata = $this->config->curl_post_https($congig['server'] . 'Alipay', $postdata);

        $sdata = json_decode($fdata, true);//将json代码转换为数组

        if ($sdata['state'] == 0) {
//            print_r($yundata);
            exit($sdata['text']);
        }
        $sdata = $sdata['text'];

        return $sdata;
    }

    public function orderajax()
    {
        $congig = $this->config->config;

        $yundata = array(
            "appid" => $congig['appid'],//获取appid
            "order" => $_REQUEST['order'],//这个是云端返回的一个唯一单号
            "type" => $_REQUEST['type'],//获取分类
            "uip" => $congig['uip']//获取用户IP地址
        );

        //订单查询签名格式
        // md5(appid=666666&type=1&uip=127.0.0.1&appkey=123456789);
        //以上是token签名规则
        $token = array(
            "appid" => $congig['appid'],//APPID号码
            "type" => $yundata["type"],//类别
            "uip" => $congig['uip'],//客户IP
            "appkey" => $congig['appkey']//appkey密匙
        );


        //加密token 32位  小写
        $token = md5($this->config->urlparams($token));
        //exit($token);
        //重组条件
        $postdata = $this->config->urlparams($yundata) . '&token=' . $token;

        //订单查询网关地址后面加 order
        $fdata = $this->config->curl_post_https($congig['server'] . 'order', $postdata);

        //$sdata = json_decode($fdata, true);//将json代码转换为数组
        /*
        json 返回的几个状态
        完整输出JSON例子  {"code":"0","msg":"\u5f85\u4ed8\u6b3e"}
        code = 0  等待付款
        code = 1  付款完成
        code = 2  订单超时
        code = 3  订单不存在或者超时被删除
        code = 4  token错误
        */
        echo $fdata;
    }

}