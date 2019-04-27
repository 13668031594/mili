<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/23
 * Time: 下午12:22
 */

namespace app\repair\controller;

use app\http\controller\AdminController;
use classes\repair\RepairDefaultClass;
use think\Request;

class RepairDefaultController extends AdminController
{
    private $class;

    public function __construct()
    {
        $this->class = new RepairDefaultClass();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getIndex()
    {
        $repair_class = $this->class->classes();

        //视图
        return parent::view('index',['repair_class' => $repair_class]);
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
        $repair_class = $this->class->classes();

        //视图
        return parent::view('repair_default',['repair_class' => $repair_class]);
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
        $repair = $this->class->edit($request->get('id'));
        $repair_class = $this->class->classes();

        //视图
        return parent::view('repair_default', ['self' => $repair,'repair_class' => $repair_class]);
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
        return parent::success('/repair_default/index');
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
        else self::update($id,$request);

        //反馈成功
        return parent::success('/repair_default/index');
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
    private function update($id,Request $request)
    {
        //验证字段
        $this->class->validator_update($id,$request);

        //更新
        $this->class->update($id,$request);
    }
}