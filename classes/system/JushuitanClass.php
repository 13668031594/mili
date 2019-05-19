<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午5:30
 */

namespace classes\system;

use app\Substation\model\SubstationModel;
use classes\AdminClass;
use classes\vendor\StorageClass;
use think\Request;

class JushuitanClass extends AdminClass
{
    public $storage;

    public function __construct()
    {
        $name = 'jushuitanSetting.txt';

        $this->storage = new StorageClass($name);
    }

    public function index()
    {
        //读取设定文件
        $set = $this->storage->get();

        //获取默认配置
        $result = self::defaults();

        //设定文件存在，修改返回配置
        if (!is_array($set)) {

            //格式化配置信息
            $set = json_decode($set, true);

            //循环设定数据
            foreach ($result as $k => &$v) {

                //设定文件中有的设定，修改之
                if (isset($set[$k])) $v = $set[$k];
            }
        }

        //返回设定文件
        return $result;
    }

    //保存配置文件
    public function save()
    {
        //获取提交的参数
        $set = input();

        //单独处理富文本
//        $set['content'] = $set['fwb-content'];
//        unset($set['fwb-content']);

        //获取原始配置
        $result = self::defaults();

        //循环修改
        foreach ($result as $k => &$v) {

            //设定文件中有的设定，修改之
            if (isset($set[$k])) {

                $v = $set[$k];
            }
        }

        //保存到文件
        $this->storage->save(json_encode($result));

        return $result;
    }

    //验证
    public function save_validator()
    {
        $rule = [
            'jushuitanShopid|聚水潭店铺id' => 'require|max:20',
            'jushuitanId|聚水潭id' => 'require|max:50',
            'jushuitanKey|聚水潭key' => 'require|max:50',
            'jushuitanToken|聚水潭token' => 'require|max:50',
            'jushuitanRefreshToken|聚水潭token续期时间' => 'require',
            'jushuitanRefreshOrder|聚水潭同步订单时间' => 'require|integer|between:1,1440',
        ];

        $result = parent::validator(input(), $rule);

        if (!is_null($result)) parent::ajax_exception(0, $result);
    }

    //充值，删除配置文件
    public function reset()
    {
        $this->storage->unlink_files();
    }

    //默认数据
    private function defaults()
    {
        return [
            'jushuitanShopid' => 10306045,
            'jushuitanId' => 'c4bee67756d584195e367a8e44dc6f8c',
            'jushuitanKey' => '0951cf9b1b392420f17d788cfd39f7c5',
            'jushuitanToken' => '32e8833df97187b82b53f31584716876',
            'jushuitanRefreshToken' => '05-01',
            'jushuitanRefreshOrder' => '5',
        ];
    }
}