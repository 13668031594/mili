<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/26
 * Time: 下午3:23
 */

namespace classes;


use app\member\model\MemberModel;
use app\member\model\MemberStoreModel;
use app\nav\model\LinkModel;
use classes\nav\NavClass;
use classes\system\SystemClass;
use think\Request;

class IndexClass extends FirstClass
{
    private $m = null;

    /**
     * 获取会员信息
     *
     * @return array|null|\PDOStatement|string|\think\Model
     */
    public function member()
    {
        if (is_null($this->m)) {

            $member = session('member');
            $model = new MemberModel();
            $this->m = $model->where('id', '=', $member['id'])->find();
        }

        return $this->m;
    }

    public function status()
    {
        $member = self::member();

        if ($member['status'] == '1') parent::ajax_exception(000, '您的账号已经被冻结');

    }

    /**
     * 读取系统设置
     *
     * @return array
     */
    public function set()
    {
        $model = new SystemClass();
        return $model->index();
    }

    /**
     * 原路返回的报错
     *
     * @param $error
     */
    protected function back($error)
    {
        parent::redirect_exception(request()->url(), $error);
    }

    //会员门店
    public function store()
    {
        $member = session('member');

        $store = new MemberStoreModel();

        return $store->where('member_id', '=', $member['id'])->where('show', '=', 'on')->order('sort', 'desc')->column('*');
    }

    //导航样式
    public function nav()
    {
        $class = new NavClass();

        return $class->index();
    }

    //快捷导航
    public function link()
    {
        $model = new LinkModel();

        $result = $model->where('substation','=',SUBSTATION)->where('show','=','on')->order('sort','desc')->column('*');

        return $result;
    }
}