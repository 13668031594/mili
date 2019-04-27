<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/4/25
 * Time: 上午10:50
 */

namespace classes\files;


use app\files\model\FilesLocationModel;
use classes\AdminClass;
use classes\vendor\StorageClass;
use think\Request;

class FilesLocationClass extends AdminClass
{
    public $image;
    private $dir = 'files';

    public function __construct()
    {
        $this->image = new FilesLocationModel();
        if (!is_dir($this->dir)) mkdir($this->dir);//新建文件夹
    }

    public function file(Request $request)
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = $request->file('files');

        $location = 'files_' . ($request->post('id') ? $request->post('id') : time());

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size' => (1024 * 1024)])->move($this->dir, $location);

        // 上传失败获取错误信息
        if (!$info) parent::ajax_exception(000, $file->getError());

        $model = $this->image;
        $model->location = '/' . $this->dir . '/' . $info->getSaveName();
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

        return [
            'location' => $model->location,
            'id' => $model->id,
            'name' => $info->getSaveName(),
            'size' => floor(($info->getSize() / 1024)),
        ];
    }

    public function image(Request $request)
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = $request->file('images');

        $location = 'files_' . ($request->post('id') ? $request->post('id') : time());

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size' => (1024 * 1024), 'ext' => 'jpg,png,gif,jpeg,bmp'])->move($this->dir, $location);

        // 上传失败获取错误信息
        if (!$info) parent::ajax_exception(000, $file->getError());

        $model = $this->image;
        $model->location = '/' . $this->dir . '/' . $info->getSaveName();
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

        return [
            'image' => $model->location,
            'imageId' => $model->id,
        ];
    }

    //过期文件删除
    public function image_delete()
    {
        //过期时间
        $date = date('Y-m-d', strtotime('-1 day')) . ' 00:00:00';

        //验证今天是否执行过删除
        $storage = new StorageClass($this->dir);
        $over = $storage->get();
        if (!is_array($over) && ($over >= $date)) return;//执行过

        //寻找并删除文件
        $model = new FilesLocationModel();
        $result = $model->where('created_at', '<', $date)->where('master', '=', null)->select();
        if (count($result) > 0) foreach ($result as $v) {

            if (!is_null($v->location) && file_exists(substr($v->location, 1))) unlink(substr($v->location, 1));
        }

        //删除数据
        $model = new FilesLocationModel();
        $model->where('created_at', '<', $date)->where('master', null)->delete();

        //保存删除时间
        $storage->save($date);
    }

    public function images($file, $master)
    {
        // 获取表单上传文件 例如上传了001.jpg
//        $file = $request->file()['images'];

        if (count($file) <= 0) return;

        foreach ($file as $k => $v) {

            $location = 'files_' . $master . '_' . $k;

            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $v->validate(['size' => (1024 * 1024), 'ext' => 'jpg,png,gif,jpeg,bmp'])->move($this->dir, $location);

            // 上传失败获取错误信息
            if (!$info) parent::ajax_exception(000, $v->getError());

            $model =  new FilesLocationModel();
            $model->master = $master;
            $model->location = '/' . $this->dir . '/' . $info->getSaveName();
            $model->created_at = date('Y-m-d H:i:s');
            $model->save();
        }
    }

    public function files($file, $master)
    {
        // 获取表单上传文件 例如上传了001.jpg
//        $file = $request->file()['files'];

        if (count($file) <= 0) return;

        foreach ($file as $k => $v) {

            $location = 'files_' . $master . '_' . $k;

            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $v->validate(['size' => (1024 * 1024)])->move($this->dir, $location);

            // 上传失败获取错误信息
            if (!$info) parent::ajax_exception(000, $v->getError());

            $model =  new FilesLocationModel();
            $model->master = $master;
            $model->location = '/' . $this->dir . '/' . $info->getSaveName();
            $model->created_at = date('Y-m-d H:i:s');
            $model->save();
        }
    }
}