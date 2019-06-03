<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/23
 * Time: 下午12:23
 */

namespace classes\member;

use app\express\model\ExpressModel;
use app\Member\model\MemberGradeAmountModel;
use app\member\model\MemberGradeExpressModel;
use app\member\model\MemberGradeModel;
use app\member\model\MemberModel;
use classes\AdminClass;
use classes\ListInterface;
use classes\vendor\ExpressAmountClass;
use classes\vendor\GoodsAmountClass;
use classes\vendor\GradeAmountClass;
use think\Db;
use think\Request;

class MemberGradeClass extends AdminClass implements ListInterface
{
    public $model;
    public $express;

    public function __construct()
    {
        $this->model = new MemberGradeModel();
        $this->express = new MemberGradeExpressModel();
    }

    public function index()
    {
        $leftJoin = [
            'member_grade_express o',
            'o.grade = a.id'
        ];

        $where = [
//            ['express', '=', 0],
        ];

        $other = [
//            'where' => $where,
            'order_name' => 'sort',
//            'alias' => 'a',
//            'leftJoin' => $leftJoin,
//            'column' => 'a.*,o.amount as oa,o.cost,o.protect',
        ];

        $result = parent::page($this->model, $other);

        $amount_class = new GradeAmountClass();
//dd($result);
        foreach ($result['message'] as &$v) {
            $amount = $amount_class->amount($v['id'], $v['recharge'], $v['buy_total']);
            $v['recharge'] = $amount['recharge'];
            $v['buy_total'] = $amount['buy_total'];
        }

        return $result;
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function save(Request $request)
    {
        $model = $this->model;
        $model->name = $request->post('name');
        $model->sort = $request->post('sort');
        $model->mode = $request->post('mode');
        $model->amount = 0;//number_format($request->post('amount'), 2, '.', '');
        $model->status = $request->post('status');
        $model->recharge = $request->post('recharge');
        $model->buy_total = $request->post('buy_total');
        $model->created_at = date('Y-m-d H:i:s');
        $model->substation = SUBSTATION;
        $model->save();

        /*Db::table('young_member_grade_express')
            ->where('grade', $model->id)
            ->where('express', '=', 0)
            ->where('substation', '=', SUBSTATION)
            ->delete();

        $insert = [];
        $insert[0]['grade'] = $model->id;
        $insert[0]['express'] = 0;
        $insert[0]['amount'] = number_format($request->post('amount'), 2, '.', '');
        $insert[0]['cost'] = number_format($request->post('cost'), 2, '.', '');
        $insert[0]['protect'] = number_format($request->post('protect'), 2, '.', '');

        if (count($insert) > 0) $this->express->insertAll($insert);*/

        self::save_model_1($model, $request->post('expressAmount'));
    }

    public function read($id)
    {
        //等级信息
        $model = $this->model->where('id', '=', $id)->find();
        if (is_null($model)) parent::redirect_exception('/admin/member_grade/index', '等级不存在');

        $express = new ExpressModel();
        $express = $express->column('id');
        $express[] = 0;

        //独立快递费用
//        $amounts = $this->express->where('substation', '=', SUBSTATION)->where('grade', '=', $id)->column('express,amount,cost,protect', 'express');
        $amounts = [];

//        if (SUBSTATION != 0) {

            $class = new ExpressAmountClass();

            foreach ($express as $v) {

                $r = $class->amount($v, $id);

                $a = [];
                $a['express'] = $v;
                $a['amount'] = $r['amount'];
                $a['cost'] = $r['cost'];
                $a['protect'] = $r['protect'];

                $amounts[$v] = $a;
            }
//        }

        //结果数组
        $result = [
            'self' => $model,
            'amount' => $amounts,
        ];

        //反馈
        return $result;
    }

    public function edit($id)
    {
        return self::read($id);
    }

    public function update($id, Request $request)
    {
        $model = $this->model->where('id', '=', $id)->find();

        if (is_null($model)) parent::ajax_exception(000, '等级不存在');

        $test = $this->model->where('substation', '=', $model->substation)->where('id', '<>', $model->id)->where('name', '=', $request->post('name'))->find();
        if (!is_null($test)) parent::ajax_exception(000, '名称重复');

        if (SUBSTATION != '0') {

            $amount_model = new MemberGradeAmountModel();
            $a = $amount_model->where('grade', '=', $model->id)->where('substation', '=', SUBSTATION)->find();
            if (!is_null($a)) {

                $a->status = $request->post('status');
                $a->recharge = $request->post('recharge');
                $a->buy_total = $request->post('buy_total');
                $a->save();
            } else {

                $amount_model->grade = $model->id;
                $amount_model->substation = SUBSTATION;
                $amount_model->status = $request->post('status');
                $amount_model->recharge = $request->post('recharge');
                $amount_model->buy_total = $request->post('buy_total');
                $amount_model->save();
            }
        } else {

            $model->name = $request->post('name');
            $model->sort = $request->post('sort');
            $model->mode = $request->post('mode');
            $model->amount = 0;//number_format($request->post('amount'), 2, '.', '');
            $model->status = $request->post('status');
            $model->recharge = $request->post('recharge');
            $model->buy_total = $request->post('buy_total');
            $model->updated_at = date('Y-m-d H:i:s');
            $model->save();
        }

        self::save_model_1($model, $request->post('expressAmount'));

        return $model;
    }

    public function delete($id)
    {
        $this->model->whereIn('id', $id)->where('change', '=', 'success')->delete();
        Db::table('young_member_grade_express')->whereIn('grade', $id)->delete();
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'name|名称' => 'require|min:1|max:255',
            'sort|排序' => 'require|integer|between:1,999',
//            'amount|统一快递费' => 'require|float|between:0,100000000',
            'mode|模式' => 'require|in:on,off',
            'status|状态' => 'require|in:on,off',
            'expressAmount|快递费' => 'requireIf:mode,off|array',
            'recharge|充值升级' => 'require|integer|between:0,100000000',
            'buy_total|购买升级' => 'require|integer|between:0,100000000',
//            'cost|成本价' => 'require|between:0,100000000',
//            'protect|保护价' => 'require|between:0,100000000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        //验证独立快递费字段
        self::validator_mode_1($request, 0);

        $test = $this->model->where('substation', '=', SUBSTATION)->where('name', '=', $request->post('name'))->find();
        if (!is_null($test)) parent::ajax_exception(000, '名称重复');
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'name|名称' => 'require|min:1|max:255',
            'sort|排序' => 'require|integer|between:1,999',
//            'amount|统一快递费' => 'require|float|between:0,100000000',
            'status|状态' => 'require|in:on,off',
            'mode|模式' => 'require|in:on,off',
            'expressAmount|快递费' => 'requireIf:mode,1|array',
            'recharge|充值升级' => 'require|integer|between:0,100000000',
            'buy_total|购买升级' => 'require|integer|between:0,100000000',
//            'cost|成本价' => 'require|between:0,100000000',
//            'protect|保护价' => 'require|between:0,100000000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        if ($request->post('status') == 'off') {

            if ($id == 1) parent::ajax_exception(000, '无法关闭初始会员等级');

            $member = new MemberModel();
            $test = $member->where('substation', '=', SUBSTATION)->where('grade_id', '=', $id)->count();
            if ($test > 0) parent::ajax_exception(000, '该等级中尚有会员「' . $test . '」人，无法关闭');
        }

        //验证独立快递费字段
        self::validator_mode_1($request, $id);
    }

    public function validator_delete($id)
    {
//        if (in_array(1, $id)) parent::ajax_exception(000, '无法删除初始会员等级');
        $model = new MemberModel();
        $test = $model->whereIn('grade_id', $id)->count();
        if ($test > 0) parent::ajax_exception(000, '该等级中尚有会员『' . $test . '』人，无法删除');
    }

    //验证独立快递费字段
    private function validator_mode_1(Request $request, $id)
    {
//        if ($request->post('mode') == 'off') {

        $rule = [];
        $post = [];

        foreach ($request->post('expressAmount') as $k => $v) {
//dump($request->post('expressAmount'));
//exit;
            if ($v['id'] == 0) continue;

            if (!isset($v['name']) || !isset($v['amount']) || !isset($v['id'])) parent::ajax_exception(000, '请刷新重试');

            $rule[$v['id'] . '|' . $v['name'] . '快递费用'] = 'require|float|between:0,100000000';
            $post[$v['id']] = $v['amount'];

            $result = parent::validator($post, $rule);
            if (!is_null($result)) parent::ajax_exception(000, $result);
        }
//        }

        if (SUBSTATION != 0 && $id != 0) {

            $class = new ExpressAmountClass();

            foreach ($request->post('expressAmount') as &$v) {

                $r = $class->amount($v['id'], $id);

                if ($r['protect'] > $v['amount']) parent::ajax_exception(000, $v['name'] . '销售价不得低于' . $r['protect']);
//                if ($r['cost'] > $v['amount']) parent::ajax_exception(000, $v['name'] . '销售价不得低于' . $r['amount']);
//                if ($r['protect'] > $v['amount']) parent::ajax_exception(000, $v['name'] . '销售价不得低于' . $r['amount']);
            }
        }
    }

    /**
     * 保存关于独立快递费用的修改
     *
     * @param $model
     * @param $expressAmount
     */
    private function save_model_1($model, $expressAmount)
    {
//        if ($model->mode == 'off') {

        Db::table('young_member_grade_express')
            ->where('grade', $model->id)
            ->where('express', '<>', 0)
            ->where('substation', '=', SUBSTATION)
            ->delete();

        $insert = [];

        foreach ($expressAmount as $k => $v) {

            $insert[$k]['substation'] = SUBSTATION;
            $insert[$k]['grade'] = $model->id;
            $insert[$k]['express'] = $v['id'];
            $insert[$k]['amount'] = number_format($v['amount'], 2, '.', '');
            $insert[$k]['cost'] = number_format($v['cost'], 2, '.', '');
            $insert[$k]['protect'] = number_format($v['protect'], 2, '.', '');
        }

        if (count($insert) > 0) $this->express->insertAll($insert);
//        }
    }

    /**
     * 快递列表
     *
     * @return array
     */
    public function express()
    {
        //快递列表
        $express = new ExpressModel();
        $express = $express->order('sort', 'desc')->column('id,name,platform');

        $result = [];
        foreach ($express as $v) {

            $result[$v['platform']][] = $v;
        }

        return $result;
    }

    public function change_member_grade(MemberGradeModel $gradeModel)
    {
        $model = new MemberModel();
        $model->where('grade_id', '=', $gradeModel->id)->update(['grade_name' => $gradeModel->name]);
    }
}