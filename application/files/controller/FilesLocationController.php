<?php

namespace app\files\controller;

use app\http\controller\AdminController;
use classes\files\FilesLocationClass;
use think\Request;

class FilesLocationController extends AdminController
{
    /**
     * 上传图片
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function postImage(Request $request)
    {
        $class = new FilesLocationClass();

        //删除过期图片
        $class->image_delete();

        $result = $class->image($request);

        $result['index'] = $request->post('index');

        return parent::success('', null, $result);
    }

    /**
     * 上传图片
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function postFile(Request $request)
    {
        $class = new FilesLocationClass();

        //删除过期图片
        $class->image_delete();

        $result = $class->file($request);

        $result['index'] = $request->post('index');

        return parent::success('', null, $result);
    }

}
