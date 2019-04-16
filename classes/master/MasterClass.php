<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/10/17
 * Time: 下午5:57
 */

namespace classes\master;

use app\master\model\MasterModel;
use classes\AdminClass;
use classes\ListInterface;
use think\Request;

class MasterClass extends AdminClass implements ListInterface
{
    public $model;

    public function __construct()
    {
        $this->model = new MasterModel();
    }

    public function index()
    {
        return parent::page($this->model);
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function save(Request $request)
    {
        $master = $this->model;
        $master->nickname = $request->post('nickname');
        $master->account = $request->post('account');
        $master->password = md5($request->post('password'));
        $master->created_at = date('Y-m-d H:i:s');
        $master->save();
    }

    public function read($id)
    {
        $master = $this->model->where('id', '=', $id)->find();

        if (is_null($master)) parent::redirect_exception('/admin/master/index', '管理员不存在');

        return $master;
    }

    public function edit($id)
    {
        return self::read($id);
    }

    public function update($id, Request $request)
    {
        $master = $this->model->where('id', '=', $id)->find();

        if (is_null($master)) parent::ajax_exception(000, '管理员不存在');

        $master->nickname = $request->post('nickname');
        if ($request->post('password') != 'w!c@n#m$b%y^') $master->password = md5($request->post('password'));
        $master->updated_at = date('Y-m-d H:i:s');
        $master->save();
    }

    public function delete($id)
    {
        $this->model->whereIn('id', $id)->delete();
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'nickname|昵称' => 'require|min:1|max:48|unique:master,nickname',
            'account|账号' => 'require|min:6|max:20|unique:master,account',
            'password|密码' => 'require|min:6|max:20',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'nickname|昵称' => 'require|min:1|max:48|unique:master,nickname,' . $id . ',id',
            'password|密码' => 'require|min:6|max:20',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_delete($id)
    {
        if (in_array(1, $id)) parent::ajax_exception(000, '无法删除初始管理员');
    }

    /**
     * 特殊验证：初始管理员无法被其他人编辑
     *
     * @param $master
     * @param $id
     */
    public function validator_special($master, $id)
    {
        if (($master['id'] != $id) && ($id == 1)) parent::ajax_exception(000, '无权修改该管理员的信息');
    }
}