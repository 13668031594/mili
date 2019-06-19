<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/6/13
 * Time: 下午6:09
 */

namespace app\youyunbao\controller;

use app\index\controller\IndexController;
use app\youyunbao\model\YouyunbaoPayModel;
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
        $post = $request->post();

        $model = new YouyunbaoPayModel();
        $model->ddh = $post['ddh'];
        $model->money = $post['money'];
        $model->name = $post['name'];
        $model->key = $post['key'];
        $model->paytime = $post['paytime'];
        $model->lb = $post['lb'];
        $model->type = $post['type'];
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

        exit('ok');
    }

}