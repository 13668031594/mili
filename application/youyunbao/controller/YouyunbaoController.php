<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/6/13
 * Time: 下午6:09
 */

namespace app\youyunbao\controller;

use app\index\controller\IndexController;
use classes\vendor\StorageClass;
use classes\vendor\Youyunbao\YouyunbaoClass;
use think\Request;

class YouyunbaoController extends IndexController
{
    public function alipayh5()
    {
        $class = new YouyunbaoClass();

        $sdata = $class->alipayh5();

        return parent::view('alipayh5', ['sdata' => $sdata]);
    }

    public function orderajax()
    {
        $class = new YouyunbaoClass();

        $class->orderajax();

        exit;
    }

    public function youyunbao_notify(Request $request)
    {
        $storage = new StorageClass('youyunbao');

        $post = $request->post();

        $storage->save(json_encode($post, JSON_UNESCAPED_UNICODE));

        exit('ok');
    }

}