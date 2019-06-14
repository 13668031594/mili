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

class BankClass extends AdminClass
{
    public $storage;
    public $dir = 'logo';

    public function __construct()
    {
        $name = 'bankSetting.txt';

        if (!empty(SUBSTATION)) {

            $name = 'bankSetting_' . SUBSTATION . '.txt';
            $this->dir .= '_' . SUBSTATION;
        }

        $this->storage = new StorageClass($name);
        if (!is_dir($this->dir)) mkdir($this->dir);
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
            'file|收款内容' => 'require|min:1|max:100000',
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
            'file' => '收款设置',
        ];
    }
}