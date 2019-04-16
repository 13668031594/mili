<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/24
 * Time: 下午1:39
 */

namespace app\member\controller;

use app\http\controller\AdminController;
use classes\member\MemberClass;
use think\Request;

class MemberController extends AdminController
{
    private $class;

    public function __construct(Request $request)
    {
        //初始化类库
        $this->class = new MemberClass();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getIndex()
    {
        $grade = $this->class->create();

        //视图
        return parent::view('index', ['grade' => $grade]);
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
        $grade = $this->class->create();

        //视图
        return parent::view('member', ['grade' => $grade]);
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
        $member = $this->class->edit($request->get('id'));

        $grade = $this->class->create();

        //视图
        return parent::view('member', ['self' => $member, 'grade' => $grade]);
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
        return parent::success('/member/index');
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
        return parent::success('/member/index');
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

    /**
     * 团队展示，只显示1级
     *
     * @param Request $request
     * @return \think\response\View
     */
    public function getTeam(Request $request)
    {
        $id = $request->get('id');

        $result = $this->class->team($id);

        $result['member'] = $this->class->read($id);

        $result['referee_location'] = config('member.referee_location') . '?referee=' . $result['member']['id'];

        return parent::view('team', $result);
    }

    /**
     * 钱包页面
     *
     * @param Request $request
     * @return \think\response\View
     */
    public function getWallet(Request $request)
    {
        $member = $this->class->read($request->get('id'));

        $result = [
            'self' => $member,
            'status' => config('member.status')
        ];

        return parent::view('wallet', $result);
    }

    /**
     * 钱包充值
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function postWallet(Request $request)
    {
        $this->class->validator_wallet($request);

        $this->class->wallet($request);

        return parent::success('/member/index');
    }

    /**
     * 钱包记录页面
     *
     * @param $id
     * @return \think\response\View
     */
    public function getRecord($id)
    {
        $result = [];
        $result['record_array'] = str_replace('"', "'", json_encode($this->class->record_array()));
        $result['self'] = $this->class->read($id);

        return parent::view('record', $result);
    }

    /**
     * 钱包记录列表
     *
     * @param Request $request
     * @return mixed
     */
    public function getRecordTable(Request $request)
    {
        $result = $this->class->record($request);

        return parent::tables($result);
    }

    /**
     * 删除记录
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function getRecordDelete(Request $request)
    {
        $ids = explode(',', $request->get('id'));

        //删除
        $this->class->record_delete($ids);

        //反馈成功
        return parent::success('/member/index');
    }
}