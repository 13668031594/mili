<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/4/24
 * Time: 下午5:07
 */

namespace classes\index;


class OrderFileClass
{
    public $file;

    public function __construct($files)
    {
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

        $this->file = $result;
    }

    //京东
    public function pinduoduo($files)
    {
        //格式化并验证
        $result = [];
        foreach ($files as $k => $v) {

            if (!isset($v[14]) || !isset($v[15]) || !isset($v[17])|| !isset($v[18])|| !isset($v[19])|| !isset($v[20])) {

                return '导入文件格式有误';
            }

            $name = $v[14];
            $phone = $v[15];
            $address = $v[17].$v[18].$v[19].$v[20];

            $select_name = input('select_name');
            $select_value = input('select_value');
            if (!is_null($select_value)) {

                $value = explode("\r\n", $select_value);

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

            $result[] = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
            ];
        }

        return $result;
    }

    //京东
    public function jingdong($files)
    {
        //格式化并验证
        $result = [];
        foreach ($files as $k => $v) {

            if (!isset($v[14]) || !isset($v[15]) || !isset($v[16])) {

                return '导入文件格式有误';
            }

            $name = $v[14];
            $phone = $v[16];
            $address = $v[15];

            $select_name = input('select_name');
            $select_value = input('select_value');
            if (!is_null($select_value)) {

                $value = explode("\r\n", $select_value);

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

            $result[] = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
            ];
        }

        return $result;
    }

    //天猫与淘宝的格式
    public function tianmao($files)
    {
        //格式化并验证
        $result = [];
        foreach ($files as $k => $v) {

            if (!isset($v[14]) || !isset($v[15]) || !isset($v[18])) {

                return '导入文件格式有误';
            }

            $name = $v[14];
            $phone = substr($v[18], 1);
            $address = $v[15];

            $select_name = input('select_name');
            $select_value = input('select_value');
            if (!is_null($select_value)) {

                $value = explode("\r\n", $select_value);

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

            $result[] = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
            ];
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

            $result[] = [
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
            ];
        }

        return $result;
    }
}