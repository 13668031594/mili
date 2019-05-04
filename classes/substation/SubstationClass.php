<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午6:15
 */

namespace classes\substation;

use app\member\model\MemberGradeModel;
use app\member\model\MemberModel;
use app\Substation\model\SubstationModel;
use classes\AdminClass;
use classes\ListInterface;
use think\Db;
use think\Request;

class SubstationClass extends AdminClass implements ListInterface
{
    public $model;

    public function __construct()
    {
        $this->model = new SubstationModel();
    }

    public function index()
    {
        if (SUBSTATION != 0) {
            $whereIn = [
                'pid' => parent::substation_ids(),
            ];
        } else {

            $whereIn = [];
        }


        $other = [
            'whereIn' => $whereIn,
        ];

        return parent::page($this->model, $other);
    }

    public function create()
    {

    }

    public function save(Request $request)
    {
        $substation = $this->model;
        $substation->name = $request->post('name');
        $substation->localhost = $request->post('localhost');
        $substation->status = $request->post('status');
        $substation->pid = $request->post('substation');
        $substation->top = 0;
        $substation->created_at = date('Y-m-d H:i:s');
        $substation->save();

        $grade = new MemberGradeModel();
        $insert = [
            'name' => '会员',
            'sort' => 50,
            'amount' => '10',
            'mode' => 'on',
            'change' => 'fail',
            'substation' => $substation->id,
            'created_at' => $substation->created_at,
        ];

        $grade->insert($insert);
    }

    public function read($id)
    {
        $substation = $this->model->where('id', '=', $id)->find();

        if (is_null($substation)) parent::redirect_exception('/admin/substation/index', '分站不存在');

        if ($substation['pid'] == '0') {

            $substation['pname'] = '主站';
        } else {

            $p = $this->model->where('id', '=', $substation['pid'])->find();
            $substation['pname'] = is_null($p) ? '未知' : $p['name'];
        }

        return $substation;
    }

    public function edit($id)
    {
        return self::read($id);
    }

    public function update($id, Request $request)
    {
        $substation = $this->model->where('id', '=', $id)->find();

        if (is_null($substation)) parent::ajax_exception(000, '分站不存在');

        $substation->name = $request->post('name');
        $substation->localhost = $request->post('localhost');
        $substation->status = $request->post('status');
        $substation->save();
    }

    public function delete($id)
    {
        $this->model->whereIn('id', $id)->delete();
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'name|分站名称' => 'require|max:10|unique:substation,name',
            'localhost|分站域名' => 'require|max:255|unique:substation,localhost',
            'status|状态' => 'require',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        //安全路径
        $safe_path = include 'safe_path.php';

        if (in_array($request->post('localhost'), $safe_path)) parent::ajax_exception(000, '非法域名');

        /*$localhost = $request->post('localhost');
        $head = substr($localhost, 0, 7);
        if (($head != 'http://')) {

            $head = substr($localhost, 0, 8);
            if (($head != 'https://')) {

                parent::ajax_exception(000, '域名必须以http://或者https://开头');
            }
        }*/
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'name|分站名称' => 'require|max:10|unique:substation,name,' . $id . ',id',
            'localhost|分站域名' => 'require|max:255|unique:substation,localhost,' . $id . ',id',
            'status|状态' => 'require',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        //安全路径
        $safe_path = include 'safe_path.php';

        if (in_array($request->post('localhost'), $safe_path)) parent::ajax_exception(000, '非法域名');


        /*$localhost = $request->post('localhost');
        $head = substr($localhost, 0, 7);
        if (($head != 'http://')) {

            $head = substr($localhost, 0, 8);
            if (($head != 'https://')) {

                parent::ajax_exception(000, '域名必须以http://或者https://开头');
            }
        }*/
    }

    public function validator_delete($id)
    {
        $member = new MemberModel();
        $member = $member->whereIn('substation', $id)->find();

        if (!is_null($member)) parent::ajax_exception(000, '请先清空分站内的会员');
    }
}