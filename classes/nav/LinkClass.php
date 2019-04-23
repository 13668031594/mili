<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/22
 * Time: 下午3:51
 */

namespace classes\nav;

use app\nav\model\LinkModel;
use classes\AdminClass;
use classes\ListInterface;
use classes\vendor\StorageClass;
use think\Request;

class LinkClass extends AdminClass implements ListInterface
{
    public $model;
    public $number = 5;

    public function __construct()
    {
        $this->model = new LinkModel();
    }

    public function index()
    {
        $where = [
            //['substation','=', SUBSTATION]
        ];

        $other = [
            'order_name' => 'sort',
            'where' => $where,
            'substation' => '1',
        ];

        $result = parent::page($this->model, $other);

        return $result;
    }

    public function create()
    {
        // TODO: Implement create() method.
    }

    public function save(Request $request)
    {
        $model = $this->model;
        $model->title = $request->post('title');
        $model->sort = $request->post('sort');
        $model->show = $request->post('show');
        $model->hot = $request->post('hot');
        $model->link = $request->post('link');
        $model->substation = SUBSTATION;
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();
    }

    public function read($id)
    {
        $model = $this->model->where('id', '=', $id)->find();

        if (is_null($model)) parent::redirect_exception('/admin/link/index', '链接不存在');

        return $model->getData();
    }

    public function edit($id)
    {
        return self::read($id);
    }

    public function update($id, Request $request)
    {
        $model = $this->model->where('id', '=', $id)->find();
        if (is_null($model)) parent::ajax_exception(000, '链接不存在');

        $model->title = $request->post('title');
        $model->sort = $request->post('sort');
        $model->show = $request->post('show');
        $model->hot = $request->post('hot');
        $model->link = $request->post('link');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->save();
    }

    public function delete($id)
    {
        $this->model->whereIn('id', $id)->delete();
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'title|标题' => 'require|min:1|max:255',
            'show|显示' => 'require',
            'hot|加热' => 'require',
            'sort|排序' => 'require|integer|between:1,999',
            'link|链接' => 'require|min:1|max:255',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        if ($request->post('show') == 'on') {

            $model = new LinkModel();
            $test = $model->where('show', '=', 'on')->count();
            if ($test >= $this->number) parent::ajax_exception(000, '至多只能显示『' . $this->number . '』个链接');
        }
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'title|标题' => 'require|min:1|max:255',
            'show|显示' => 'require',
            'hot|加热' => 'require',
            'sort|排序' => 'require|integer|between:1,999',
            'link|链接' => 'require|min:1|max:255',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        if ($request->post('show') == 'on') {

            $model = new LinkModel();
            $test = $model->where('show', '=', 'on')->where('id','<>',$id)->count();
            if ($test >= $this->number) parent::ajax_exception(000, '至多只能显示『' . $this->number . '』个链接');
        }
    }

    public function validator_delete($id)
    {
        // TODO: Implement validator_delete() method.
    }
}