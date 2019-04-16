<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/6/30
 * Time: 下午5:58
 */

namespace classes\vendor;

use classes\FirstClass;

class SmsClass extends FirstClass
{
    private $helper;
    private $accessKeyId = 'LTAIMrTjpA02EVFF';
    private $accessKeySecret = '9xeqrtNFZVGpgyr8DMa5YquiFMZ5lR';
    private $signName = '5A电商网';
    public $TemplateParam = null;

    public function __construct()
    {
        $this->helper = new SignatureHelper();
    }

    /**
     * 发送短信
     *
     * @param $phone
     * @param $code
     * @param $templateCode
     * @return bool|\stdClass
     */
    public function sendSms($phone, $code, $templateCode)
    {
        $params = array();

        // *** 需用户填写部分 ***

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $phone;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = $this->signName;

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $templateCode;

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        $params['TemplateParam'] = is_null($this->TemplateParam) ? Array(
            "code" => $code,
        ) : $this->TemplateParam;

        // fixme 可选: 设置发送短信流水号
//        $params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
//        $params['SmsUpExtendCode'] = "1234567";


        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

//         初始化SignatureHelper实例用于设置参数，签名以及发送请求
//        $helper = new SignatureHelper();

        try {

            // 此处可能会抛出异常，注意catch
            $content = $this->helper->request(
                $this->accessKeyId,
                $this->accessKeySecret,
                "dysmsapi.aliyuncs.com",
                array_merge($params, array(
                    "RegionId" => "cn-hangzhou",
                    "Action" => "SendSms",
                    "Version" => "2017-05-25",
                ))
            // fixme 选填: 启用https
            // ,true
            );

            return $content;

        } catch (\Exception $exception) {

            parent::ajax_exception(000, '短信发送失败，请刷新重试');
        }
    }
}