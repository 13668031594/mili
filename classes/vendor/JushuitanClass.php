<?php

namespace classes\vendor;

use classes\FirstClass;

class JushuitanClass extends FirstClass
{
    //聚水潭发放参数
    private $partnerid;//id
    private $partnerkey;//key
    private $token;//token
    private $url = 'http://open.erp321.com/api/open/query.aspx';//接口url

    public function __construct()
    {
        $set = new \classes\system\JushuitanClass();
        $set = $set->index();

        $this->partnerid = $set['jushuitanId'];
        $this->partnerkey = $set['jushuitanKey'];
        $this->token = $set['jushuitanToken'];
    }

    //token续期
    public function refresh_token()
    {
        $result = self::visit('refresh.token', []);

        return $result;
    }

    //店铺列表
    public function shops_query()
    {
        $result = self::visit('shops.query', ['nicks' => []]);

        return $result;
    }

    //查询库存
    public function inventory_query($sku_ids = '', $page = 1, $limit = 50, $begin = null, $end = null)
    {
        if (is_null($begin) && is_null($end)) {

            $begin = date('Y-m-d H:i:s', strtotime('-7 day'));
            $end = date('Y-m-d H:i:s');
        } elseif (is_null($begin)) {

            $begin = date('Y-m-d H:i:s', strtotime('-7 day', strtotime($end)));
        } else {

            $end = date('Y-m-d H:i:s', strtotime('+7 day', strtotime($begin)));
        }

        $data = [
            'page_index' => $page,
            'page_size' => $limit,
            'modified_begin' => $begin,
            'modified_end' => $end,
            'sku_ids' => $sku_ids,
        ];

        return self::visit('inventory.query', $data);
    }

    //订单上传
    public function orders_upload($data)
    {
        $result = self::visit('orders.upload', $data);

        return $result;
    }

    public function orders_single_query($shop_id = '', $so_ids = '', $page = 1, $limit = 30, $begin = null, $end = null)
    {
        if (is_null($begin) && is_null($end)) {

            $begin = date('Y-m-d H:i:s', strtotime('-7 day'));
            $end = date('Y-m-d H:i:s');
        } elseif (is_null($begin)) {

            $begin = date('Y-m-d H:i:s', strtotime('-7 day', strtotime($end)));
        } else {

            $end = date('Y-m-d H:i:s', strtotime('+7 day', strtotime($begin)));
        }

        $data = [
            'page_index' => $page,
            'page_size' => $limit,
            'modified_begin' => $begin,
            'modified_end' => $end,
            'so_ids' => $so_ids,
            'shop_id' => $shop_id,
        ];

        $result = self::visit('orders.single.query', $data);

        return $result;
    }

    /**
     * 访问接口
     *
     * @param $method
     * @param array $data
     * @return mixed
     */
    public function visit($method, array $data)
    {
        $ts = time();

        $sign = md5("{$method}{$this->partnerid}token{$this->token}ts{$ts}{$this->partnerkey}");

        $url = "{$this->url}?method={$method}&partnerid={$this->partnerid}&token={$this->token}&ts={$ts}&sign={$sign}";

        $result = self::url_post($url, json_encode($data));

        return json_decode($result, true);
    }

    /**
     * 访问url，post
     *
     * @param $url
     * @param $post_data
     * @return mixed
     */
    private function url_post($url, $post_data)
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