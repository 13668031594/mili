<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/23
 * Time: 下午12:23
 */

namespace classes\member;

use app\express\model\ExpressModel;
use app\member\model\MemberGradeExpressModel;
use app\member\model\MemberGradeModel;
use app\member\model\MemberModel;
use classes\AdminClass;
use classes\ListInterface;
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
        $where = [
//            ['substation'  , '=' , SUBSTATION],
        ];

        $other = [
            'substation' => '1',
            'order_name' => 'sort',
            'where' => $where,
        ];

        return parent::page($this->model, $other);
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
        $model->amount = number_format($request->post('amount'), 2, '.', '');
        $model->recharge = $request->post('recharge');
        $model->buy_total = $request->post('buy_total');
        $model->created_at = date('Y-m-d H:i:s');
        $model->substation = SUBSTATION;
        $model->save();

        self::save_model_1($model, $request->post('expressAmount'));
    }

    public function read($id)
    {
        //等级信息
        $model = $this->model->where('id', '=', $id)->find();
        if (is_null($model)) parent::redirect_exception('/admin/member_grade/index', '等级不存在');

        //独立快递费用
        $amounts = $this->express->where('grade', '=', $id)->column('express,amount', 'express');

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

        $model->name = $request->post('name');
        $model->sort = $request->post('sort');
        $model->mode = $request->post('mode');
        $model->amount = number_format($request->post('amount'), 2, '.', '');
        $model->recharge = $request->post('recharge');
        $model->buy_total = $request->post('buy_total');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->save();

        self::save_model_1($model, $request->post('expressAmount'));

        return $model;
    }

    public function delete($id)
    {
        $this->model->whereIn('id', $id)->delete();
        Db::table('young_member_grade_express')->where('change','=','success')->whereIn('grade', $id)->delete();

    }

    public function validator_save(Request $request)
    {
        $rule = [
            'name|名称' => 'require|min:1|max:255|unique:member_grade,name',
            'sort|排序' => 'require|integer|between:1,999',
            'amount|统一快递费' => 'require|float|between:0,100000000',
            'mode|模式' => 'require',
            'expressAmount|快递费' => 'requireIf:mode,off|array',
            'recharge|充值升级' => 'require|integer|between:0,100000000',
            'buy_total|购买升级' => 'require|integer|between:0,100000000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        if (!in_array($request->post('mode'), ['on', 'off'])) parent::ajax_exception(000, '模式错误');

        //验证独立快递费字段
        self::validator_mode_1($request);
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'name|名称' => 'require|min:1|max:255|unique:member_grade,name,' . $id . ',id',
            'sort|排序' => 'require|integer|between:1,999',
            'amount|统一快递费' => 'require|float|between:0,100000000',
            'mode|模式' => 'require',
            'expressAmount|快递费' => 'requireIf:mode,1|array',
            'recharge|充值升级' => 'require|integer|between:0,100000000',
            'buy_total|购买升级' => 'require|integer|between:0,100000000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        if (!in_array($request->post('mode'), ['on', 'off'])) parent::ajax_exception(000, '模式错误');

        //验证独立快递费字段
        self::validator_mode_1($request);
    }

    public function validator_delete($id)
    {
//        if (in_array(1, $id)) parent::ajax_exception(000, '无法删除初始会员等级');
    }

    //验证独立快递费字段
    private function validator_mode_1(Request $request)
    {
        if ($request->post('mode') == 'off') {

            $rule = [];
            $post = [];

            foreach ($request->post('expressAmount') as $k => $v) {
//                parent::ajax_exception(000, json_encode());
                if (!isset($v['name']) || !isset($v['amount']) || !isset($v['id'])) parent::ajax_exception(000, '请刷新重试');

                $rule[$v['id'] . '|' . $v['name'] . '快递费用'] = 'require|float|between:0,100000000';
                $post[$v['id']] = $v['amount'];

                $result = parent::validator($post, $rule);
                if (!is_null($result)) parent::ajax_exception(000, $result);
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
        if ($model->mode == 'off') {

            Db::table('young_member_grade_express')->where('grade', $model->id)->delete();

            $insert = [];

            foreach ($expressAmount as $k => $v) {

                $insert[$k]['grade'] = $model->id;
                $insert[$k]['express'] = $v['id'];
                $insert[$k]['amount'] = number_format($v['amount'], 2, '.', '');
            }

            if (count($insert) > 0) $this->express->insertAll($insert);
        }
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
        return $express->order('sort', 'desc')->column('id,name');
    }

    public function change_member_grade(MemberGradeModel $gradeModel)
    {
        $model = new MemberModel();
        $model->where('grade_id', '=', $gradeModel->id)->update(['grade_name' => $gradeModel->name]);
    }
}