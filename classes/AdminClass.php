<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午3:26
 */

namespace classes;

use app\master\model\MasterModel;

class AdminClass extends FirstClass
{
    public function master()
    {
        $master = session('master');
        $model = new MasterModel();
        return $model->where('id', '=', $master['id'])->find();
    }
}