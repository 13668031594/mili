<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/23
 * Time: 下午12:23
 */

namespace classes\repair;

use app\article\model\ArticleModel;
use app\repair\model\RepairClassModel;
use app\repair\model\RepairDefaultModel;
use classes\AdminClass;
use classes\ListInterface;
use think\Request;

class RepairDefaultClass extends AdminClass implements ListInterface
{
    public $model;

    public function __construct()
    {
        $this->model = new RepairDefaultModel();
    }

    public function index()
    {
        $where = [];

        $repair = input('repair_class');

        if (!empty($repair))$where[] = ['repair_class_id','=',$repair];

        $other = [
            'column' => 'id,title,sort,repair_class_name,show,created_at',
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
        $class = new RepairClassModel();
        $class = $class->where('id', '=', $request->post('repair_class'))->find();
        if (is_null($class)) parent::ajax_exception(000, '分类不存在');

        $model = $this->model;
        $model->title = $request->post('title');
        $model->content = $request->post('fwb-content');
        $model->sort = $request->post('sort');
        $model->show = $request->post('show');
        $model->repair_class_id = $class->id;
        $model->repair_class_name = $class->name;
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();
    }

    public function read($id)
    {
        $model = $this->model->where('id', '=', $id)->find();

        if (is_null($model)) parent::redirect_exception('/admin/repair_default/index', '推荐方案不存在');

        return $model;
    }

    public function edit($id)
    {
        return self::read($id);
    }

    public function update($id, Request $request)
    {
        $model = $this->model->where('id', '=', $id)->find();
        if (is_null($model)) parent::ajax_exception(000, '推荐方案不存在');

        $class = new RepairClassModel();
        $class = $class->where('id', '=', $request->post('repair_class'))->find();
        if (is_null($class)) parent::ajax_exception(000, '分类不存在');

        $model->title = $request->post('title');
        $model->content = $request->post('fwb-content');
        $model->sort = $request->post('sort');
        $model->show = $request->post('show');
        $model->repair_class_id = $class->id;
        $model->repair_class_name = $class->name;
        $model->save();
    }

    public function delete($id)
    {
        $this->model->whereIn('id', $id)->delete();
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'title|标题' => 'require|min:1|max:48|unique:repair_default,title',
            'sort|排序' => 'require|integer|between:1,999',
            'show|状态' => 'require',
            'fwb-content|内容' => 'require|min:1|max:20000',
            'repair_class|分类' => 'require',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'title|标题' => 'require|min:1|max:48|unique:repair_default,title',
            'sort|排序' => 'require|integer|between:1,999',
            'show|状态' => 'require',
            'fwb-content|内容' => 'require|min:1|max:20000',
            'repair_class|分类' => 'require',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_delete($id)
    {
    }

    public function classes()
    {
        $model = new RepairClassModel();
        $result = $model->order('sort','desc')->column('id,name');

        return $result;
    }
}