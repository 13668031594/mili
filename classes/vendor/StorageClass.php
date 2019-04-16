<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/7
 * Time: 下午5:28
 */

namespace classes\vendor;

class StorageClass
{
    public $files_dir = '../files';//基础文件夹
    public $url;

    public function __construct($url)
    {
        if (!is_dir($this->files_dir)) mkdir($this->files_dir);//新建文件夹

        $this->url = $this->files_dir . '/' . $url;
    }

    //获取
    public function get()
    {
        if (!file_exists($this->url)) return ['code' => '1001', 'message' => '没有找到该文件'];//不是文件

        $file = fopen($this->url, 'r');//打开文件

        $result = fread($file, filesize($this->url));;//读取文件

        fclose($file);//关闭文件

        return $result;//返回内容
    }

    //保存
    public function save($txt = '')
    {
        $file = fopen($this->url, 'w');

        $result = fwrite($file, $txt);

        fclose($file);

        return $result;
    }

    //删除
    public function unlink_files($urls = null)
    {
        if (is_null($urls)) {

            //没有特殊参数，删除初始化的路径
            unlink($this->url);
        } elseif (is_array($urls)) {

            //是数组，批量删除

            //循环
            foreach ($urls as $v) {

                //是文件，删除
                if (is_file($v)) unlink($v);
            }
        } elseif (is_file($urls)) {

            //单个文件，单个删除
            unlink($urls);
        }
    }
}