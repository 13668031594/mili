<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/22
 * Time: 下午3:51
 */

namespace classes\repair;

use app\files\model\FilesLocationModel;
use app\repair\model\RepairClassModel;
use app\repair\model\RepairDefaultModel;
use app\repair\model\RepairModel;
use classes\AdminClass;
use classes\ListInterface;
use think\Request;

class RepairClassClass extends AdminClass implements ListInterface
{
    public $model;
    public $image;
    private $names = 'repair_class_';

    public function __construct()
    {
        $this->model = new RepairClassModel();
        $this->image = new FilesLocationModel();
    }

    public function index()
    {
        $other = [
            'order_name' => 'sort',
        ];

        $result = parent::page($this->model, $other);

        foreach ($result['message'] as &$v) {

            if (is_null($v['location'])) $v['location'] = config('young.image_not_found');
        }

        return $result;
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function save(Request $request)
    {
//        $image = $this->image->where('id', $request->post('imageId'))->find();
//        if (is_null($image)) parent::ajax_exception(000, '请重新上传图片');

        $model = $this->model;
        $model->name = $request->post('name');
//        $model->image = $image->id;
//        $model->location = $image->location;
        $model->sort = $request->post('sort');
        $model->show = $request->post('show');
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

//        $image->master = $this->names. $model->id;
//        $image->save();
    }

    public function read($id)
    {
        $model = $this->model->where('id', '=', $id)->find();

        if (is_null($model)) parent::redirect_exception('/admin/repair_class/index', '分类不存在');

//        if (is_null($model->location)) $model->location = config('young.image_not_found');

        return $model->getData();
    }

    public function edit($id)
    {
        return self::read($id);
    }

    public function update($id, Request $request)
    {
//        $image = $this->image->where('id', $request->post('imageId'))->find();
//        if (is_null($image)) parent::ajax_exception(000, '请重新上传图片');

        $model = $this->model->where('id', '=', $id)->find();
        if (is_null($model)) parent::ajax_exception(000, '分类不存在');

        $model->name = $request->post('name');
//        $model->image = $image->id;
//        $model->location = $image->location;
        $model->sort = $request->post('sort');
        $model->show = $request->post('show');
        $model->save();

        $repair = new RepairModel();
        $repair->where('repair_class_id', '=', $id)->update(['repair_class_name' => $model->name]);

        $default = new RepairDefaultModel();
        $default->where('repair_class_id', '=', $id)->update(['repair_class_name' => $model->name]);
//        $image->master = $this->names  . $model->id;
//        $image->save();

//        $images = new FilesLocationModel();
//        $images->where('master', '=', $image->master)->where('id', '<>', $image->id)->update(['master' => null]);
    }

    public function delete($id)
    {
        $this->model->whereIn('id', $id)->delete();

//        foreach ($id as &$v)$v = $this->names . $v;

//        $this->image->whereIn('master', $id)->update(['master' => null]);
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'name|分类名' => 'require|min:1|max:255',
            'show|显示' => 'require',
            'sort|排序' => 'require|integer|between:1,999',
//            'imageId|背景图' => 'require',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'name|分类名' => 'require|min:1|max:255',
            'show|显示' => 'require',
            'sort|排序' => 'require|integer|between:1,999',
//            'imageId|背景图' => 'require',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_delete($id)
    {
        // TODO: Implement validator_delete() method.
    }
}