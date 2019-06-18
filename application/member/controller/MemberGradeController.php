<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/23
 * Time: 下午5:41
 */

namespace app\member\controller;

use app\http\controller\AdminController;
use app\Substation\model\SubstationLevelModel;
use classes\member\MemberGradeClass;
use think\Request;

class MemberGradeController extends AdminController
{
    private $class;

    public function __construct()
    {
        $this->class = new MemberGradeClass();
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
     * 显示创建资源表单页
     *
     * @return \think\Response
     */
    public function getCreate()
    {
        $result['express'] = $this->class->express();
        $result['platform'] = config('member.store_platform');

        //视图
        return parent::view('grade', $result);
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
        $result = $this->class->edit($request->get('id'));

        //快递信息
        $result['express'] = $this->class->express();
        $result['platform'] = config('member.store_platform');

        //视图
        return parent::view('grade', $result);
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
        return parent::success('/member_grade/index');
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
        return parent::success('/member_grade/index');
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
        $grade = $this->class->update($id, $request);

        //修改会员列表中的缓存
        $this->class->change_member_grade($grade);
    }

    //分站价格修改
    public function getAmount(Request $request)
    {
        $level_id = $request->get('level_id');

        $result = $this->class->read($request->get('id'));

        $level = $this->class->substation_level($result, $level_id);

        $express = $this->class->express();

        $platform = config('member.store_platform');

        $model = new SubstationLevelModel();

        $levels = $model->order('sort asc')->column('id,name');
//        $levels = array_merge([0 => '统一模式'], $levels);

        $result = array_merge($result, [
            'level_id' => $level_id,
            'level' => $level,
            'levels' => $levels,
            'express' => $express,
            'platform' => $platform,
        ]);
//dd($result);
        return parent::view('amount', $result);
    }

    public function postAmount(Request $request)
    {
        $this->class->level_amount($request);

        return parent::success('/member_grade/index');
    }
}