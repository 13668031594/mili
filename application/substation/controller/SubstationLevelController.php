<?php

namespace app\substation\controller;

use app\http\controller\AdminController;
use classes\substation\SubstationLevelClass;
use think\Request;

class SubstationLevelController extends AdminController
{
    private $class;

    public function __construct(Request $request)
    {
        //初始化类库
        $this->class = new SubstationLevelClass();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getIndex()
    {
        //视图
        return parent::view('index');
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
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function getCreate()
    {
        //视图
        return parent::view('level');
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
        $substation = $this->class->edit($request->get('id'));

        //视图
        return parent::view('level', ['self' => $substation]);
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
        return parent::success('/substation-level/index');
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
        return parent::success('/substation-level/index');
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

    //分站价格修改
    public function getAmount(Request $request)
    {
        $id = $request->get('id');

        $self = $this->class->read($id);

        $express_self = $this->class->self_express();

        $express = $this->class->substation_level($id, $express_self, $self);

        return parent::view('amount', ['id' => $id, 'self' => $express_self, 'express' => $express]);
    }

    public function postAmount(Request $request)
    {
        $this->class->level_amount($request);

        return parent::success('/substation-level/index');
    }

    public function getAmountReset()
    {
        $id = \request()->get('id');

        $this->class->level_amount_reset($id);

        return $this->success();
    }
}
