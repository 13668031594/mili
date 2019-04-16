<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/24
 * Time: 下午4:36
 */

namespace classes\member;


use app\member\model\MemberModel;
use app\member\model\MemberStoreModel;
use classes\AdminClass;
use classes\ListInterface;
use think\Request;

class MemberStoreClass extends AdminClass implements ListInterface
{
    public $store;
    public $member;

    public function __construct()
    {
        $this->store = new MemberStoreModel();

        $model = new MemberModel();
        $this->member = $model->where('id', '=', input('member_id'))->find();
        if (is_null($this->member)) $this->member = $model;
    }

    public function index()
    {
        $where = [
            ['member_id','=', $this->member['id']]
        ];

        $name = input('storeName');
        if (!empty($name)) $where[] = ['name', 'like', '%' . $name . '%'];

        $other['where'] = $where;

        return parent::page($this->store, $other);
    }

    public function create()
    {
        return config('member.store_platform');
    }

    public function save(Request $request)
    {
        $model = $this->store;
        $model->member_id = $request->post('member_id');
        $model->name = $request->post('name');
        $model->sort = $request->post('sort');
        $model->platform = $request->post('platform');
        $model->man = $request->post('man');
        $model->phone = $request->post('phone');
        $model->show = $request->post('show');

        $model->created_at = date('Y-m-d H:i:s');
        $model->save();
    }

    public function read($id)
    {
        $store = $this->store->where('id', '=', $id)->find();

        if (is_null($store)) parent::redirect_exception('/admin/store/index', '店铺不存在');

        $this->member = $this->member->where('id', '=', $store['member_id'])->find();

        return $store->getData();
    }

    public function edit($id)
    {
        return self::read($id);
    }

    public function update($id, Request $request)
    {
        $model = $this->store->where('id', '=', $id)->find();

        if (is_null($model)) parent::redirect_exception('/admin/store/index', '店铺不存在');

        $model->name = $request->post('name');
        $model->sort = $request->post('sort');
        $model->platform = $request->post('platform');
        $model->man = $request->post('man');
        $model->phone = $request->post('phone');
        $model->show = $request->post('show');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->save();
    }

    public function delete($id)
    {
        $store = new MemberStoreModel();

        $store = $store->whereIn('id', $id)->find();

        if (is_null($store)) parent::ajax_exception(000, '没有找到删除项');

        $this->member = $this->member->where('id', '=', $store['member_id'])->find();

        $this->store->whereIn('id', $id)->delete();
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'member_id' => 'require',
            'name|名称' => 'require|length:1,255',
            'sort|排序' => 'require|integer|between:1,999',
            'platform|平台' => 'require|in:' . implode(',', array_keys(config('member.store_platform'))),
            'man|发货人' => 'require|length:1,255',
            'phone|联系电话' => 'require|length:11',
            'show|显示' => 'require|in:on,off',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(502, $result);
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'name|名称' => 'require|length:1,255',
            'sort|排序' => 'require|integer|between:1,999',
            'platform|平台' => 'require|in:' . implode(',', array_keys(config('member.store_platform'))),
            'man|发货人' => 'require|length:1,255',
            'phone|联系电话' => 'require|length:11',
            'show|显示' => 'require|in:on,off',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(502, $result);
    }

    public function validator_delete($id)
    {
    }
}