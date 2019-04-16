<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/10/18
 * Time: 上午11:59
 */

namespace app\http\exceptions;

use Exception;
use think\exception\Handle;

class Http extends Handle
{
    public function render(Exception $e)
    {
        if ($e instanceof RedirectException) {

            return $e->render();
        }

        if ($e instanceof AjaxException) {

            return $e->render();
        }

        return parent::render($e);
    }
}