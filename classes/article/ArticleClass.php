<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/23
 * Time: 下午12:23
 */

namespace classes\article;

use app\article\model\ArticleModel;
use app\notice\model\NoticeModel;
use classes\AdminClass;
use classes\ListInterface;
use think\Request;

class ArticleClass extends AdminClass implements ListInterface
{
    public $model;

    public function __construct()
    {
        $this->model = new ArticleModel();
    }

    public function index()
    {
        $column = 'id,title,sort,show,author,describe,created_at';

        $other = [
            'order_name' => 'sort',
            'column' => $column,
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
        $model->title = $request->post('title');
        $model->content = $request->post('fwb-content');
        $model->sort = $request->post('sort');
        $model->show = $request->post('show');
        $model->author = $request->post('author');
        $model->describe = $request->post('describe');
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();
    }

    public function read($id)
    {
        $model = $this->model->where('id', '=', $id)->find();

        if (is_null($model)) parent::redirect_exception('/admin/notice/index', '文章不存在');

        return $model;
    }

    public function edit($id)
    {
        return self::read($id);
    }

    public function update($id, Request $request)
    {
        $model = $this->model->where('id', '=', $id)->find();

        if (is_null($model)) parent::ajax_exception(000, '文章不存在');

        $model->title = $request->post('title');
        $model->content = $request->post('fwb-content');
        $model->sort = $request->post('sort');
        $model->show = $request->post('show');
        $model->author = $request->post('author');
        $model->describe = $request->post('describe');
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
            'title|标题' => 'require|min:1|max:48|unique:notice,title',
            'describe|描述' => 'require|min:1|max:255',
            'sort|排序' => 'require|integer|between:1,999',
            'show|状态' => 'require',
            'author|发布人' => 'require|min:1|max:40',
            'fwb-content|内容' => 'require|min:1|max:20000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'title|标题' => 'require|min:1|max:48|unique:notice,title,' . $id . ',id',
            'describe|描述' => 'require|min:1|max:255',
            'sort|排序' => 'require|integer|between:1,999',
            'show|状态' => 'require',
            'author|发布人' => 'require|min:1|max:40',
            'fwb-content|内容' => 'require|min:1|max:20000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_delete($id)
    {
    }
}