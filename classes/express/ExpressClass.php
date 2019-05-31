<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午6:15
 */

namespace classes\express;


use app\express\model\ExpressModel;
use app\member\model\MemberGradeExpressModel;
use classes\AdminClass;
use classes\ListInterface;
use think\Db;
use think\Request;

class ExpressClass extends AdminClass implements ListInterface
{
    public $model;

    public function __construct()
    {
        $this->model = new ExpressModel();
    }

    public function index()
    {
        $where = [];

        $platform = input('platform');
        if (!is_null($platform) && ($platform != '')) {

            $where[] = ['platform', '=', $platform];
        }

        $other = [
            'where' => $where,
            'order_name' => 'sort',
        ];

        return parent::page($this->model, $other);
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function save(Request $request)
    {
        $express = $this->model;
        $express->name = $request->post('name');
        $express->sort = $request->post('sort');
        $express->disabled = $request->post('disabled');
        $express->platform = $request->post('platform');
        $express->goods_code = $request->post('goods_code');
        $express->cost = $request->post('cost');
        $express->protect = $request->post('protect');
        $express->substation = SUBSTATION;
        $express->created_at = date('Y-m-d H:i:s');
        $express->save();
    }

    public function read($id)
    {
        $express = $this->model->where('id', '=', $id)->find();

        if (is_null($express)) parent::redirect_exception('/admin/express/index', '快递不存在');

        return $express;
    }

    public function edit($id)
    {
        return self::read($id);
    }

    public function update($id, Request $request)
    {
        $express = $this->model->where('id', '=', $id)->find();

        if (is_null($express)) parent::ajax_exception(000, '快递不存在');

        $express->name = $request->post('name');
        $express->sort = $request->post('sort');
        $express->disabled = $request->post('disabled');
        $express->platform = $request->post('platform');
        $express->goods_code = $request->post('goods_code');
        $express->updated_at = date('Y-m-d H:i:s');
        $express->save();
    }

    public function delete($id)
    {
        $this->model->whereIn('id', $id)->delete();
        Db::table('young_member_grade_express')->whereIn('express', $id)->delete();
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'name|快递名称' => 'require|min:1|max:48',
            'sort|排序' => 'require|integer|between:1,999',
            'disabled|状态' => 'require',
            'platform|平台' => 'require|in:0,1,2',
            'goods_code|限制使用' => 'max:2000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'name|快递名称' => 'require|min:1|max:48',
            'sort|排序' => 'require|integer|between:1,999',
            'disabled|状态' => 'require',
            'platform|平台' => 'require|in:0,1,2',
            'goods_code|限制使用' => 'max:2000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_delete($id)
    {
        /*$substation = $this->model->column('id,substation');

        $sub = [];

        foreach ($substation as $k => $v) {

            if (!isset($sub[$v])) $sub[$v] = 0;
            $sub[$v]++;
        }

        foreach ($sub as $ke => $va) {

            $count = $this->model->where('substation','=',$ke)->count();
            if ($count <= $va)parent::ajax_exception(000, '至少保留一种快递');
        }*/
    }
}