<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/22
 * Time: 下午3:50
 */

namespace app\repair\controller;

use app\http\controller\AdminController;
use classes\repair\RepairClassClass;
use think\Request;

class RepairClassController extends AdminController
{
    private $class;

    public function __construct(Request $request)
    {
        //初始化类库
        $this->class = new RepairClassClass();
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
        return parent::view('repair_class');
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function getEdit($id)
    {
        //获取数据
        $result = $this->class->edit($id);

        //视图
        return parent::view('repair_class', ['self' => $result]);
    }

    /**
     * 删除指定资源
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function getDelete(Request $request)
    {
        $ids = explode(',', $request->get('id'));

        //验证资源
        $this->class->validator_delete($ids);

        //删除
        $this->class->delete($ids);

        //反馈成功
        return parent::success('/repair_class/index');
    }

    /**
     * 保存资源
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function postSave(Request $request)
    {
        $id = $request->post('id');

        if (empty($id)) return self::save($request);
        else return self::update($id, $request);
    }

    /**
     * 保存新建的资源
     *
     * @param Request $request
     * @return \think\response\Json
     */
    private function save(Request $request)
    {
        //验证字段
        $this->class->validator_save($request);

        //添加
        $this->class->save($request);

        //反馈成功
        return parent::success('/repair_class/index');
    }

    /**
     * 保存更新的资源
     *
     * @param $id
     * @param Request $request
     * @return \think\response\Json
     */
    private function update($id, Request $request)
    {
        //验证字段
        $this->class->validator_update($id, $request);

        //更新
        $this->class->update($id, $request);

        //反馈成功
        return parent::success('/repair_class/index');
    }


}