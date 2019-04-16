<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/24
 * Time: 下午4:36
 */

namespace app\member\controller;


use app\http\controller\AdminController;
use app\member\model\MemberModel;
use classes\member\MemberStoreClass;
use think\Request;

class MemberStoreController extends AdminController
{
    private $class;

    public function __construct(Request $request)
    {
        //初始化类库
        $this->class = new MemberStoreClass();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getIndex()
    {
        $platform = str_replace('"',"'",json_encode($this->class->create()));

        //视图
        return self::view('index', ['platform' => $platform]);
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
        $platform = $this->class->create();

        //视图
        return self::view('store', ['platform' => $platform]);
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
        $store = $this->class->edit($request->get('id'));

        $platform = $this->class->create();

        //视图
        return self::view('store', ['self' => $store, 'platform' => $platform]);
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
        return parent::success('/store/index?member_id='.$this->class->member['id']);
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
        return parent::success('/store/index?member_id='.$this->class->member['id']);
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

    //特殊渲染方法
    public function view($view = '', $data = [])
    {
        $data['member'] = $this->class->member;

        return parent::view($view, $data); // TODO: Change the autogenerated stub
    }
}