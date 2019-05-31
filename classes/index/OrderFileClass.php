<?php

namespace classes\index;

use classes\vendor\StorageClass;

class OrderFileClass
{
    public $file;
    public $region;

    public function __construct($files)
    {
        $storage = new StorageClass('region');
        $region = $storage->get();
        $this->region = json_decode($region, true);
        if (is_string($this->region)) $this->region = json_decode($this->region, true);

        if (input('type') == '0') {

            $result = self::text($files);
        } else {

            switch (input('platform')) {
                case 'self':
                    $result = self::my_file($files);
                    break;
                case 'tianmao':
                    $result = self::tianmao($files);
                    break;
                case 'taobao':
                    $result = self::tianmao($files);
                    break;
                case 'jingdong':
                    $result = self::jingdong($files);
                    break;
                case 'pinduoduo':
                    $result = self::pinduoduo($files);
                    break;
                default:
                    $result = '无效的平台';
                    break;
            }
        }

        $this->file = $result;
    }

    //京东
    public function pinduoduo($files)
    {
        //格式化并验证
        $result = [];
        $select_name = input('select_name');
        $select_value = input('select_value');
        $value = is_null($select_value) ? [] : explode("\r\n", $select_value);
        foreach ($value as $key => $val) if (empty($val)) unset($value[$key]);

        foreach ($files as $k => $v) {

            if (!isset($v[14]) || !isset($v[15]) || !isset($v[17]) || !isset($v[18]) || !isset($v[19]) || !isset($v[20])) {

                return '导入文件格式有误';
            }

            $name = $v[14];
            $phone = $v[15];
            $address = $v[17] . $v[18] . $v[19] . $v[20];
            $address = str_replace(" ", '', $address);//去空格

            if (!empty($value)) {

                switch ($select_name) {
                    case 'order';
                        if (!in_array($v[1], $value)) continue 2;
                        break;
                    default:
                        return '智能筛选项错误';
                        break;
                }
            }

            if (empty($name)) {
                return '第' . ($k + 2) . '行收货人格式错误';
            }
            if (strlen($phone) != 11) {
                return '第' . ($k + 2) . '行收货电话格式错误';
            }
            if (empty($address)) {
                return '第' . ($k + 2) . '行收货地址格式错误';
            }
            if (strlen($address) > 255) {
                return '第' . ($k + 2) . '行收货地址超长';
            }

            $pro = $v[17];
            $city = $v[18];
            $area = $v[19];
            $add = $v[20];
            $test = false;

            foreach ($this->region as $va) {

                if ($va['name'] == $pro) {

                    foreach ($va['child'] as $val) {

                        if ($val['name'] == $city) {

                            foreach ($val['child'] as $valu) {

                                if ($valu['name'] == $area) {

                                    $test = true;

                                    break 3;
                                }
                            }
                        }
                    }
                }
            }

            if ($test === false) return '第' . ($k + 2) . '行收货地址省市区错误';

            $re = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'pro' => $pro,
                'city' => $city,
                'area' => $area,
                'add' => $add,
            ];

            $result[] = $re;
        }

        return $result;
    }

    //京东
    public function jingdong($files)
    {
        //格式化并验证
        $result = [];
        $select_name = input('select_name');
        $select_value = input('select_value');
        $value = is_null($select_value) ? [] : explode("\r\n", $select_value);
        foreach ($value as $key => $val) if (empty($val)) unset($value[$key]);
        foreach ($files as $k => $v) {

            if (!isset($v[14]) || !isset($v[15]) || !isset($v[16])) {

                return '导入文件格式有误';
            }

            $name = $v[14];
            $phone = $v[16];
            $address = $v[15];
            $address = str_replace(" ", '', $address);//去空格


            if (!empty($value)) {

                switch ($select_name) {
                    case 'order';
                        if (!in_array($v[0], $value)) continue 2;
                        break;
                    case 'phone';
                        if (!in_array($phone, $value)) continue 2;
                        break;
                    default:
                        return '智能筛选项错误';
                        break;
                }
            }

            if (empty($name)) {
                return '第' . ($k + 2) . '行收货人格式错误';
            }
            if (!preg_match("/^1[23456789]\d{9}$/", $phone)) {
                return '第' . ($k + 2) . '行收货电话格式错误';
            }
            if (empty($address)) {
                return '第' . ($k + 2) . '行收货地址格式错误';
            }
            if (strlen($address) > 255) {
                return '第' . ($k + 2) . '行收货地址超长';
            }

            $add = self::pro_city_area_add($address);
            if ($add === false) return '第' . ($k + 2) . '行收货地址省市区错误';

            $re = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
            ];

            $result[] = array_merge($re, $add);
        }

        return $result;
    }

    //天猫与淘宝的格式
    public function tianmao($files)
    {
        //格式化并验证
        $result = [];
        $select_name = input('select_name');
        $select_value = input('select_value');
        $value = is_null($select_value) ? [] : explode("\r\n", $select_value);
        foreach ($value as $key => $val) if (empty($val)) unset($value[$key]);
        foreach ($files as $k => $v) {

            if (!isset($v[14]) || !isset($v[15]) || !isset($v[18])) {

                return '导入文件格式有误';
            }

            $name = $v[14];
            $phone = substr($v[18], 1);
            $address = $v[15];
            $address = str_replace(" ", '', $address);//去空格

            if (!empty($value)) {

                switch ($select_name) {
                    case 'order';
                        if (!in_array($v[0], $value)) continue 2;
                        break;
                    case 'phone';
                        if (!in_array($phone, $value)) continue 2;
                        break;
                    default:
                        return '智能筛选项错误';
                        break;
                }
            }

            if (empty($name)) {
                return '第' . ($k + 2) . '行收货人格式错误';
            }
            if (!preg_match("/^1[23456789]\d{9}$/", $phone)) {
                return '第' . ($k + 2) . '行收货电话格式错误' . $phone;
            }
            if (empty($address)) {
                return '第' . ($k + 2) . '行收货地址格式错误';
            }
            if (strlen($address) > 255) {
                return '第' . ($k + 2) . '行收货地址超长';
            }

            $add = self::pro_city_area_add($address);
            if ($add === false) return '第' . ($k + 2) . '行收货地址省市区错误';

            $re = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
            ];

            $result[] = array_merge($re, $add);
        }

        return $result;
    }

    //自己的格式
    private function my_file($files)
    {
        //格式化并验证
        $result = [];
        foreach ($files as $k => $v) {

            if (count($v) != 3) {
                return '导入文件格式有误';
            }

            list($name, $phone, $address) = $v;
            $address = str_replace(" ", '', $address);//去空格

            if (empty($name)) {
                return '第' . ($k + 2) . '行收货人格式错误';
            }
            if (!preg_match("/^1[23456789]\d{9}$/", $phone)) {
                return '第' . ($k + 2) . '行收货电话格式错误';
            }
            if (empty($address)) {
                return '第' . ($k + 2) . '行收货地址格式错误';
            }
            if (strlen($address) > 255) {
                return '第' . ($k + 2) . '行收货地址超长';
            }

            $add = self::pro_city_area_add($address);
            if ($add === false) return '第' . ($k + 2) . '行收货地址省市区错误';

            $re = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
            ];

            $result[] = array_merge($re, $add);
        }

        return $result;
    }

    public function text($str)
    {
        $str = str_replace(" ", '', $str);//去空格
        $address = explode("\r\n", $str);

        $result = [];
        $i = 0;
        foreach ($address as $v) {

            if (empty($v)) continue;

            $v = preg_replace("/(，)/", ',', $v);
            $a = explode(',', $v);
            if (count($a) < 3) return '第' . ($i + 1) . '行数据格式错误';
            list($result[$i]['name'], $result[$i]['phone'], $result[$i]['address']) = $a;

            $area_t = strstr($result[$i]['phone'], '-', true);
            if ($area_t) list($de, $result[$i]['phone']) = explode('-', $result[$i]['phone']);

            if (empty($result[$i]['name'])) return '第' . ($i + 1) . '行收货人格式错误';
            if (!preg_match("/^1[34578]\d{9}$/", $result[$i]['phone'])) return '第' . ($i + 1) . '行收货电话格式错误';
            if (empty($result[$i]['address'])) return '第' . ($i + 1) . '行收货地址格式错误';
            if (strlen($result[$i]['address']) > 255) return '第' . ($i + 1) . '行收货地址超长';

            $add = self::pro_city_area_add($result[$i]['address']);

            if ($add === false) return '第' . ($i + 1) . '行收货地址省市区不明';

            $result[$i] = array_merge($result[$i], $add);

            $i++;
        }

        return $result;
    }

    /**
     * 从字符串中摘取省市区
     *
     * @param $address
     * @return array|bool
     */
    public function pro_city_area_add($address)
    {
        $result = [
            'pro' => '',
            'city' => '',
            'area' => '',
            'add' => '',
        ];
//        $address = '重庆沙坪坝区土主镇土主镇市民广场';
        foreach ($this->region as $v) {

            $long1 = strlen($v['name']);

            $pro_t = $v['name'];
            $result['pro'] = $pro_t;
            $pro = substr($address, 0, $long1);

            if ($pro != $pro_t) {

                $pro_t = strstr($v['name'], '省', true);
                if (!$pro_t) $pro_t = strstr($v['name'], '市', true);
                if (!$pro_t) $pro_t = strstr($v['name'], '自治区', true);
                if (!$pro_t) $pro_t = strstr($v['name'], '特别行政区', true);
                if (!$pro_t) continue;
                $long1 = strlen($pro_t);
                $pro = substr($address, 0, $long1);
            }

            if ($pro != $pro_t) continue;

            foreach ($v['child'] as $va) {

                if (self::special_pro($result['pro'])){

                    $long2 = 0;
                    $city = $pro_t;
                    $city_t = $pro_t;
                    $result['city'] = $city_t;
                }else{

                    $long2 = strlen($va['name']);

                    $city_t = $va['name'];
                    $result['city'] = $city_t;
                    $city = substr($address, $long1, $long2);

                    if ($city != $city_t) {

                        $city_t = strstr($va['name'], '市', true);
                        if (!$city_t) continue;
                        $long2 = strlen($city_t);
                        $city = substr($address, $long1, $long2);
                    }
                }

                if ($city != $city_t) continue;

                //不设区的市级单位
                if (self::special_city($result['pro'], $result['city'])) {

                    $result['area'] = $city_t;

                    $add = substr($address, ($long1 + $long2));

                    $result['add'] = $add;

                    return $result;
                } else {

                    //正常市级单位
                    foreach ($va['child'] as $val) {

                        $long3 = strlen($val['name']);

                        $area_t = $val['name'];

                        $area = substr($address, ($long1 + $long2), $long3);

                        $result['area'] = $area;

                        if ($area != $area_t) {

                            $area_t = strstr($val['name'], '区', true);
                            if (!$area_t) $area_t = strstr($val['name'], '县', true);
                            if (!$area_t) $area_t = strstr($val['name'], '镇', true);
                            if (!$area_t) continue;
                            $long3 = strlen($area_t);
                            $area = substr($address, ($long1 + $long2), $long3);
                        }

                        $add = substr($address, ($long1 + $long2 + $long3));

                        $result['add'] = $add;

                        if ($area == $area_t) return $result;
                    }
                }
            }
        }

        return false;
    }

    public function special_pro($pro)
    {
        if (input('platform') != 'jingdong' || input('type') == '0')return false;

        $special_pro = [
            '北京市',
            '天津市',
            '上海市',
            '重庆市',
        ];

        if (in_array($pro,$special_pro))return true;

        return false;
    }

    public function special_city($pro, $city)
    {
        $special_city = [
            '广东省' => [
                '东莞市',
                '中山市',
            ],
            '海南省' => [
                '三沙市',
                '儋州市',
            ],
            '甘肃省' => [
                '嘉峪关市',
            ],
        ];

        if (isset($special_city[$pro]) && in_array($city, $special_city[$pro])) return true;
        else return false;
    }
}