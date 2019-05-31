<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/23
 * Time: 下午12:23
 */

namespace classes\goods;

use app\goods\model\GoodsAmountModel;
use app\goods\model\GoodsClassModel;
use app\goods\model\GoodsModel;
use classes\AdminClass;
use classes\ListInterface;
use classes\vendor\GoodsAmountClass;
use think\Request;

class GoodsClassClass extends AdminClass implements ListInterface
{
    public $model;
    public $express;

    public function __construct()
    {
        $this->model = new GoodsClassModel();
    }

    public function index()
    {
        $where = [
            //['substation'  , '=' , SUBSTATION],
        ];

        $other = [
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
        $model->substation = SUBSTATION;
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();
    }

    public function read($id)
    {
        //分类信息
        $model = $this->model->where('id', '=', $id)->find();
        if (is_null($model)) parent::redirect_exception('/admin/goods_class/index', '分类不存在');

        //反馈
        return $model;
    }

    public function edit($id)
    {
        return self::read($id);
    }

    public function update($id, Request $request)
    {
        $model = $this->model->where('id', '=', $id)->find();

        if (is_null($model)) parent::ajax_exception(000, '分类不存在');

        $model->name = $request->post('name');
        $model->sort = $request->post('sort');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->save();

        return $model;
    }

    public function delete($id)
    {
        $this->model->whereIn('id', $id)->delete();
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'name|名称' => 'require|min:1|max:255|unique:goods_class,name',
            'sort|排序' => 'require|integer|between:1,999',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'name|名称' => 'require|min:1|max:255|unique:goods_class,name,' . $id . ',id',
            'sort|排序' => 'require|integer|between:1,999',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_delete($id)
    {
        $goods = new GoodsModel();

        $goods = $goods->whereIn('goods_class_id', $id)->find();

        if (!is_null($goods)) parent::ajax_exception(000, '请先删除分类下的商品');
    }

    public function change_goods_class(GoodsClassModel $classModel)
    {
        $model = new GoodsModel();
        $model->where('goods_class_id', '=', $classModel->id)->update(['goods_class_name' => $classModel->name]);
    }

    public function amount(Request $request)
    {
        $number = $request->post('number');
        if (empty($number)) parent::ajax_exception(000, '请输入需要调价的金额');
        $number = number_format($number, 2, '.', '');
        if ($number == 0) parent::ajax_exception(000, '调价金额只能保留2位小数');

        $goods = new GoodsModel();
        $goods = $goods->where('goods_class_id', '=', $request->post('id'))->all();

        $class = new GoodsAmountClass();
        foreach ($goods as $v) {

            if (SUBSTATION == 0) {

                $v->amount += $number;
                if ($v->amount <= '0.01') $v->amount = '0.01';
                $v->save();
            } else {


                $amount_model = new GoodsAmountModel();
                $a = $amount_model->where('goods_id', '=', $v->id)->where('substation', '=', SUBSTATION)->find();
                if (!is_null($a)) {

                    $result = $class->amount($v->id, ($a->amount + $number), $v->cost, $v->protect);
                    $amount = $result['amount'];

                    $a->amount = $amount;
                    $a->updated_at = date('Y-m-d H:i:s');
                    $a->save();
                } else {

                    $result = $class->amount($v->id, ($v->amount + $number), $v->cost, $v->protect);
                    $amount = $result['amount'];

                    $amount_model->goods_id = $v->id;
                    $amount_model->substation = SUBSTATION;
                    $amount_model->amount = $amount;
                    $amount_model->cost = $result['cost'];
                    $amount_model->protect = $result['protect'];
                    $amount_model->created_at = date('Y-m-d H:i:s');
                    $amount_model->updated_at = date('Y-m-d H:i:s');
                    $amount_model->save();
                }
            }
        }
    }
}