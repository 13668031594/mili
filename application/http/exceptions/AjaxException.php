<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/10/18
 * Time: 下午12:01
 */

namespace app\http\exceptions;

use think\Exception;

class AjaxException extends Exception
{
    public function render()
    {
        $error = json_decode(parent::getMessage(), true);

        return json($error);
    }
}