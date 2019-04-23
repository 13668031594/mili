<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/30
 * Time: 下午4:43
 */

namespace classes\nav;


use classes\AdminClass;
use classes\vendor\StorageClass;

class NavClass extends AdminClass
{
    public $storage;

    public function __construct()
    {
        $name = 'nav.txt';
        if (!empty(SUBSTATION)){

            $name = 'nav_'.SUBSTATION.'.txt';
        }

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
        foreach ($result as $k => $v) {

            //设定文件中有的设定，修改之
            if (isset($set[$k])) {

                if (isset($set[$k]['hot']) && ($set[$k]['hot'] == 'on'))$result[$k]['hot'] = 'on';
                else $result[$k]['hot'] = 'off';

                if (isset($set[$k]['flash']) && ($set[$k]['flash'] == 'on'))$result[$k]['flash'] = 'on';
                else $result[$k]['flash'] = 'off';

                if (isset($set[$k]['tilt']) && ($set[$k]['tilt'] == 'on'))$result[$k]['tilt'] = 'on';
                else $result[$k]['tilt'] = 'off';
            }
        }

        //保存到文件
        $this->storage->save(json_encode($result));
//        $this->storage->save(json_encode($set));

        return $result;
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
            'index' => [
                'name' => '首页',
                'hot' => 'off',//加热
                'flash' => 'off',//闪烁
                'tilt' => 'off',//加斜
            ],
            'goods' => [
                'name' => '礼品大厅',
                'hot' => 'off',//加热
                'flash' => 'off',//闪烁
                'tilt' => 'off',//加斜
            ],
            'order' => [
                'name' => '已买礼品',
                'hot' => 'off',//加热
                'flash' => 'off',//闪烁
                'tilt' => 'off',//加斜
            ],
            'user' => [
                'name' => '用户中心',
                'hot' => 'off',//加热
                'flash' => 'off',//闪烁
                'tilt' => 'off',//加斜
            ],
            'recharge' => [
                'name' => '在线充值',
                'hot' => 'off',//加热
                'flash' => 'off',//闪烁
                'tilt' => 'off',//加斜
            ],
            'agent' => [
                'name' => '代理中心',
                'hot' => 'off',//加热
                'flash' => 'off',//闪烁
                'tilt' => 'off',//加斜
            ],
            'article' => [
                'name' => '文章公告',
                'hot' => 'off',//加热
                'flash' => 'off',//闪烁
                'tilt' => 'off',//加斜
            ],
        ];
    }
}