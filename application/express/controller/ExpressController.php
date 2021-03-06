<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午6:14
 */

namespace app\express\controller;

use app\http\controller\AdminController;
use classes\express\ExpressClass;
use think\Request;

class ExpressController extends AdminController
{
    private $class;

    public function __construct()
    {
        $this->class = new ExpressClass();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getIndex()
    {
        $platform = config('member.store_platform');

        //视图
        return parent::view('index', [
            'platform_array' => $platform,
            'platform' => json_encode($platform)
        ]);
    }

    /**
     * json返回列表数据
     *
     * @return \think\response\Json
     */
    public function getTable()
    {
        $result = $this->class->index();

        return parent::tables($result);
    }

    /**
     * 显示创建资源表单页
     *
     * @return \think\Response
     */
    public function getCreate()
    {
        $platform = config('member.store_platform');

        //视图
        return parent::view('express', ['platform' => $platform]);
    }

    /**
     * 显示编辑资源表单页
     *
     * @param Request $request
     * @return \think\response\View
     */
    public function getEdit(Request $request)
    {
        //获取数据
        $master = $this->class->edit($request->get('id'));

        $platform = config('member.store_platform');

        //视图
        return parent::view('express', ['self' => $master, 'platform' => $platform]);
    }

    /**
     * 删除指定资源
     */
    public function getDelete()
    {
        $ids = explode(',', input('id'));

        //验证资源
        $this->class->validator_delete($ids);

        //删除
        $this->class->delete($ids);

        //反馈成功
        return parent::success('/express/index');
    }

    /**
     * 保存与更新入口
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function postSave(Request $request)
    {
        $id = $request->post('id');

        if (empty($id)) self::save($request);
        else self::update($id, $request);

        //反馈成功
        return parent::success('/express/index');
    }

    /**
     * 保存新建的资源
     *
     * @param Request $request
     */
    private function save(Request $request)
    {
        //验证字段
        $this->class->validator_save($request);

        //添加
        $this->class->save($request);
    }

    /**
     * 保存更新的资源
     *
     * @param $id
     * @param Request $request
     */
    private function update($id, Request $request)
    {
        //验证字段
        $this->class->validator_update($id, $request);

        //更新
        $this->class->update($id, $request);
    }
}