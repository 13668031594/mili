<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/26
 * Time: 下午2:34
 */

namespace app\http\controller;

use classes\IndexClass;

class IndexController extends FirstController
{
    private $class;
    protected $url = 'index';
    protected $set = [];

    public function __construct()
    {
        $this->class = new IndexClass();//初始化操作类

        $this->set = $this->class->set();//获取网站设置

        if ($this->set['webSwitch'] == 'off') exit($this->set['webCloseReason']);//网站关闭

//        $this->webTitle = $this->set['webTitle'];//设置title，默认为网站名称
//        $this->webName = $this->set['webName'];//设置title，默认为网站名称
    }

    /**
     * 视图
     *
     * @param string $view
     * @param array $data
     * @return \think\response\View
     */
    protected function view($view = '', $data = [])
    {
        $data['member'] = $this->class->member();//会员信息
//        $data['webTitle'] = $this->webTitle;//title
//        $data['webName'] = $this->webName;//title
        $data['set'] = $this->set;//设定数组
        $data['nav'] = $this->class->nav();//导航特效
        $data['link'] = $this->class->link();//快捷搜索
        $data['bank_set'] = $this->class->bank_set();//收款设置

        return parent::view($view, $data); // TODO: Change the autogenerated stub
    }

    /**
     * ajax成功
     *
     * @param string $url
     * @param string $success
     * @param array $other
     * @return \think\response\Json
     */
    protected function success($url = '', $success = '操作成功', $other = [])
    {
        $result = [
            'status' => 'success',
            'url' => $url,
            'message' => $success,
        ];

        return json(array_merge($result, $other));
    }
}