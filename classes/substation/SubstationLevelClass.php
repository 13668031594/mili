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
use app\Substation\model\SubstationLevelModel;
use app\Substation\model\SubstationLevelUpModel;
use app\Substation\model\SubstationModel;
use classes\AdminClass;
use classes\ListInterface;
use think\Db;
use think\Request;

class SubstationLevelClass extends AdminClass implements ListInterface
{
    public $model;

    public function __construct()
    {
        $this->model = new SubstationLevelModel();
    }

    public function index()
    {
        $other = [
        ];

        $data = parent::page($this->model, $other);

        if (SUBSTATION != 0) {

            $up = new SubstationLevelUpModel();

            foreach ($data['message'] as &$v) {

                $ups = $up->where('substation', '=', SUBSTATION)->where('level_id','=',$v['id'])->find();

                if (!is_null($ups)) {
//                    $ups = $ups->toArray();
                    $v['goods_up'] = $ups['goods_up'];
                    $v['express_up'] = $ups['express_up'];
                    $v['goods_cost_up'] = $ups['goods_cost_up'];
                    $v['express_cost_up'] = $ups['express_cost_up'];
                    $v['goods_protect_up'] = $ups['goods_protect_up'];
                    $v['express_protect_up'] = $ups['express_protect_up'];
                }
            }
        }

        return $data;
    }

    public function create()
    {

    }

    public function save(Request $request)
    {
        $substation = $this->model;
        $substation->name = $request->post('name');
        $substation->sort = $request->post('sort');
        $substation->goods_up = $request->post('goods_up');
        $substation->express_up = $request->post('express_up');
        $substation->goods_cost_up = $request->post('goods_cost_up');
        $substation->express_cost_up = $request->post('express_cost_up');
        $substation->goods_protect_up = $request->post('goods_protect_up');
        $substation->express_protect_up = $request->post('express_protect_up');
        $substation->created_at = date('Y-m-d H:i:s');
        $substation->save();
    }

    public function read($id)
    {
        $substation = $this->model->where('id', '=', $id)->find();

        if (is_null($substation)) parent::redirect_exception('/admin/substation-level/index', '分站等级不存在');

        $up = new SubstationLevelUpModel();
        $ups = $up->where('substation', '=', SUBSTATION)->where('level_id','=',$substation['id'])->find();

        if (!is_null($ups)) {

            $substation['goods_up'] = $ups['goods_up'];
            $substation['express_up'] = $ups['express_up'];
            $substation['goods_cost_up'] = $ups['goods_cost_up'];
            $substation['express_cost_up'] = $ups['express_cost_up'];
            $substation['goods_protect_up'] = $ups['goods_protect_up'];
            $substation['express_protect_up'] = $ups['express_protect_up'];
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

        if (is_null($substation)) parent::ajax_exception(000, '分站等级不存在');

        if (SUBSTATION != '0') {

            $up = new SubstationLevelUpModel();
            $a = $up->where('substation', '=', SUBSTATION)->find();
            if (!is_null($a)) {

                $a->goods_up = $request->post('goods_up');
                $a->express_up = $request->post('express_up');
                $a->goods_cost_up = $request->post('goods_cost_up');
                $a->express_cost_up = $request->post('express_cost_up');
                $a->goods_protect_up = $request->post('goods_protect_up');
                $a->express_protect_up = $request->post('express_protect_up');
                $a->save();
            } else {

                $up->level_id = $substation->id;
                $up->substation = SUBSTATION;
                $up->goods_up = $request->post('goods_up');
                $up->express_up = $request->post('express_up');
                $up->goods_cost_up = $request->post('goods_cost_up');
                $up->express_cost_up = $request->post('express_cost_up');
                $up->goods_protect_up = $request->post('goods_protect_up');
                $up->express_protect_up = $request->post('express_protect_up');
                $up->save();
            }
        } else {

            $substation->name = $request->post('name');
            $substation->sort = $request->post('sort');
            $substation->goods_up = $request->post('goods_up');
            $substation->express_up = $request->post('express_up');
            $substation->goods_cost_up = $request->post('goods_cost_up');
            $substation->express_cost_up = $request->post('express_cost_up');
            $substation->goods_protect_up = $request->post('goods_protect_up');
            $substation->express_protect_up = $request->post('express_protect_up');
            $substation->updated_at = date('Y-m-d H:i:s');
            $substation->save();

            $model = new SubstationModel();
            $model->where('level_id', '=', $substation->id)->update(['level_name' => $substation->name]);
        }
    }

    public function delete($id)
    {
        $this->model->whereIn('id', $id)->delete();
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'name|等级名称' => 'require|max:10|unique:substation_level,name',
            'sort|排序' => 'require|integer|between:1,999',
            'goods_up|商品成交价上浮' => 'require|between:0,100000000',
            'express_up|快递成交价上浮' => 'require|between:0,100000000',
            'goods_cost_up|商品成本价上浮' => 'require|between:0,100000000',
            'express_cost_up|快递成本价上浮' => 'require|between:0,100000000',
            'goods_protect_up|商品保护价上浮' => 'require|between:0,100000000',
            'express_protect_up|快递保护价上浮' => 'require|between:0,100000000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'name|等级名称' => 'require|max:10|unique:substation_level,name,' . $id . ',id',
            'sort|排序' => 'require|integer|between:1,999',
            'goods_up|商品成交价上浮' => 'require|between:0,100000000',
            'express_up|快递成交价上浮' => 'require|between:0,100000000',
            'goods_cost_up|商品成本价上浮' => 'require|between:0,100000000',
            'express_cost_up|快递成本价上浮' => 'require|between:0,100000000',
            'goods_protect_up|商品保护价上浮' => 'require|between:0,100000000',
            'express_protect_up|快递保护价上浮' => 'require|between:0,100000000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_delete($id)
    {
        $substation = new SubstationModel();
        $substation = $substation->whereIn('level_id', $id)->find();

        if (!is_null($substation)) parent::ajax_exception(000, '请先清空分站等级内的分站');
    }
}