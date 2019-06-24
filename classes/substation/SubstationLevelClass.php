<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午6:15
 */

namespace classes\substation;

use app\express\model\ExpressModel;
use app\Member\model\ExpressLevelAmountModel;
use app\member\model\MemberGradeModel;
use app\member\model\MemberModel;
use app\Substation\model\SubstationLevelModel;
use app\Substation\model\SubstationLevelUpModel;
use app\Substation\model\SubstationModel;
use classes\AdminClass;
use classes\ListInterface;
use classes\vendor\ExpressAmountClass;
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

                $ups = $up->where('substation', '=', SUBSTATION)->where('level_id', '=', $v['id'])->find();

                if (!is_null($ups)) {
                    if (!empty($ups['name']))$v['name'] = $ups['name'];
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
        $ups = $up->where('substation', '=', SUBSTATION)->where('level_id', '=', $substation['id'])->find();

        if (!is_null($ups)) {

            if (!empty($ups['name']))$substation['name'] = $ups['name'];
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
            $a = $up->where('substation', '=', SUBSTATION)->where('level_id', '=', $id)->find();
            if (!is_null($a)) {

                $a->name = $request->post('name');
                $a->goods_up = $request->post('goods_up');
                $a->express_up = $request->post('express_up');
                $a->goods_cost_up = $request->post('goods_cost_up');
                $a->express_cost_up = $request->post('express_cost_up');
                $a->goods_protect_up = $request->post('goods_protect_up');
                $a->express_protect_up = $request->post('express_protect_up');
                $a->save();
            } else {

                $up->level_id = $substation->id;
                $up->name = $request->post('name');
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
        }

        $sub = new SubstationModel();
        $sub_ids = $sub->where('pid', '=', SUBSTATION)
            ->whereOr('top', '=', SUBSTATION)
            ->column('id');

        $model = new SubstationModel();
        $model->where('level_id', '=', $id)->whereIn('id', $sub_ids)->update(['level_name' => $substation->name]);
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

    public function self_express()
    {
        $model = new ExpressModel();

        $result = $model->column('*', 'id');

        $result[0] = [
            'id' => 0,
            'name' => '统一模式',
            'cost' => 0.00,
            'protect' => 0.00,
        ];
        ksort($result);

        $amount_class = new ExpressAmountClass();
        foreach ($result as &$v) {

            if (SUBSTATION != '0') {

                $amount = $amount_class->amount($v['id'], $v['cost'], $v['protect']);
                $v['cost'] = $amount['cost'];
                $v['protect'] = $amount['protect'];
            }
        }

        return $result;
    }

    //分站等级
    public function substation_level($id, $express, $self)
    {
        $amount = new ExpressLevelAmountModel();
        $amount = $amount->where('substation', '=', SUBSTATION)->where('level_id', '=', $id)->column('*', 'express');

        foreach ($express as &$v) {

            if (isset($amount[$v['id']])) {

                $al = $amount[$v['id']];

                $v['cost'] = $v['cost'] > $al['cost'] ? $v['cost'] : $al['cost'];
                $v['protect'] = $v['protect'] > $al['protect'] ? $v['protect'] : $al['protect'];
            } else {

                $v['cost'] = $v['cost'] + $self['express_cost_up'];
                $v['protect'] = $v['protect'] + $self['express_protect_up'];
            }
        }

        return $express;
    }

    //为每个分站等级配备成本价和保护价
    public function level_amount(Request $request)
    {
        $level_id = $request->post('id');

        $express = self::self_express();

        //设置的成本价数组
        $costs = $request->post('cost');

        //设置的保护价数组
        $protects = $request->post('protect');

        $insert = [];
        foreach ($express as $v) {

            $cost = $costs[$v['id']];
            $protect = $protects[$v['id']];

            if ($v['cost'] > $cost) parent::ajax_exception(000, $v['name'] . ' 成本价不得低于：' . $v['cost']);
            if ($v['protect'] > $protect) parent::ajax_exception(000, $v['name'] . ' 保护价不得低于：' . $v['protect']);

            $i = [
                'express' => $v['id'],
                'cost' => $cost,
                'protect' => $protect,
                'substation' => SUBSTATION,
                'level_id' => $level_id,
            ];

            $insert[] = $i;
        }

        $goods_amount_model = new ExpressLevelAmountModel();
        $goods_amount_model->where('substation', '=', SUBSTATION)->where('level_id', '=', $level_id)->delete();

        if (count($insert) > 0) $goods_amount_model->insertAll($insert);
    }
}