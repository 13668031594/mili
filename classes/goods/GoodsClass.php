<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/24
 * Time: 下午7:25
 */

namespace classes\goods;


use app\goods\model\GoodsAmountModel;
use app\goods\model\GoodsContentModel;
use app\goods\model\GoodsImagesModel;
use app\goods\model\GoodsClassModel;
use app\goods\model\GoodsModel;
use app\goods\model\GoodsRecordModel;
use classes\AdminClass;
use classes\ListInterface;
use classes\vendor\StorageClass;
use think\Db;
use think\Request;

class GoodsClass extends AdminClass implements ListInterface
{
    public $model;
    public $image;
    public $content;
    private $dir = 'goods_image';

    public function __construct()
    {
        $this->model = new GoodsModel();
        $this->image = new GoodsImagesModel();
        $this->content = new GoodsContentModel();
        if (!is_dir($this->dir)) mkdir($this->dir);//新建文件夹
    }

    public function index()
    {
        $where = [
            //['substation', '=', SUBSTATION],
        ];

        $goods_class = input('goodsClass');
        if (!empty($goods_class)) $where[] = ['goods_class_id', '=', $goods_class];
        $name = input('goodsName');
        if (!empty($name)) $where[] = ['name', 'like', '%' . $name . '%'];

        $other = [
            'order_name' => 'sort',
            'where' => $where,
        ];

        $result = parent::page($this->model, $other);

        $amount = new GoodsAmountModel();

        foreach ($result['message'] as &$v) {

            if (is_null($v['location']) || !file_exists(substr($v['location'], 1))) $v['location'] = config('young.image_not_found');
            if (SUBSTATION != '0') {

                $a = $amount->where('goods_id', '=', $v['id'])->where('substation', '=', SUBSTATION)->find();
                if (!is_null($a)) $v['amount'] = $a->amount;
            }
        }

        return $result;
    }

    public function create()
    {
        $model = new GoodsClassModel();

        return $model->order('sort', 'desc')->column('id,name');
    }

    public function save(Request $request)
    {
        $class = new GoodsClassModel();
        $class = $class->where('id', '=', $request->post('goods_class'))->find();
        if (is_null($class)) parent::ajax_exception(000, '分类不存在');

        if (SUBSTATION != '0') {

            parent::ajax_exception(000, '无权添加商品');
        }

        $model = $this->model;
        $model->goods_class_id = $class['id'];
        $model->goods_class_name = $class['name'];
        $model->name = $request->post('name');
        $model->code = $request->post('code');
        $model->describe = $request->post('describe');
        $model->amount = number_format($request->post('amount'), 2, '.', '');
        $model->sort = $request->post('sort');
        $model->status = $request->post('status');
        $model->express_number = $request->post('express_number');
        $model->weight = $request->post('weight');
        $model->created_at = date('Y-m-d H:i:s');
        $model->substation = SUBSTATION;
        $model->save();

        $content = $this->content;
        $content->goods = $model->id;
        $content->content = $request->post('fwb-content');
        $content->created_at = $model->created_at;
        $content->save();

        self::image_save($model, $request);
    }

    public function read($id)
    {
        //商品
        $model = $this->model->where('id', '=', $id)->find();
        if (is_null($model)) parent::redirect_exception('/admin/goods/index', '商品不存在');

        $model->location = is_null($model->location) ? config('young.image_not_found') : $model->location;


        if (SUBSTATION != '0') {

            $amount = new GoodsAmountModel();
            $a = $amount->where('goods_id', '=', $model->id)->where('substation', '=', SUBSTATION)->find();
            if (!is_null($a)) $model->amount = $a->amount;
        }

        return $model->getData();
    }

    public function edit($id)
    {
        $model = self::read($id);

        //图片
        $images = $this->image->where('pid', '=', $id)->where('id', '<>', $model['cover'])->column('*');
        $image = [];
        $i = 1;
        foreach ($images as $k => $v) {

            $image[$i]['id'] = $v['id'];
            $image[$i]['location'] = is_null($v['location']) ? config('young.image_not_found') : $v['location'];

            $i++;
        }
        ksort($image);

        //正文
        $content = $this->content->where('goods', '=', $id)->find();

        //集合
        return [
            'self' => $model,
            'images' => $image,
            'content' => $content
        ];
    }

    public function update($id, Request $request)
    {
        $model = $this->model->where('id', '=', $id)->find();
        if (is_null($model)) parent::ajax_exception(000, '商品不存在');

        $class = new GoodsClassModel();
        $class = $class->where('id', '=', $request->post('goods_class'))->find();
        if (is_null($class)) parent::ajax_exception(000, '分类不存在');

        if (SUBSTATION != '0') {

            $amount = new GoodsAmountModel();
            $a = $amount->where('goods_id', '=', $model->id)->where('substation', '=', SUBSTATION)->find();
            if (!is_null($a)) {

                $a->amount = number_format($request->post('amount'), 2, '.', '');
                $a->updated_at = date('Y-m-d H:i:s');
                $a->save();
            } else {

                $amount->goods_id = $model->id;
                $amount->substation = SUBSTATION;
                $amount->amount = number_format($request->post('amount'), 2, '.', '');
                $amount->created_at = date('Y-m-d H:i:s');
                $amount->updated_at = date('Y-m-d H:i:s');
                $amount->save();
            }
        } else {

            $model->goods_class_id = $class['id'];
            $model->goods_class_name = $class['name'];
            $model->name = $request->post('name');
            $model->code = $request->post('code');
            $model->describe = $request->post('describe');
            $model->amount = number_format($request->post('amount'), 2, '.', '');
            $model->sort = $request->post('sort');
            $model->status = $request->post('status');
            $model->express_number = $request->post('express_number');
            $model->weight = $request->post('weight');
            $model->updated_at = date('Y-m-d H:i:s');
            $model->save();

            $content = $this->content->where('goods', '=', $model->id)->find();
            $content->content = $request->post('fwb-content');
            $content->updated_at = $model->updated_at;
            $content->save();
        }


        self::image_save($model, $request);
    }

    public function delete($id)
    {
        $this->model->whereIn('id', $id)->delete();
        $this->image->whereIn('pid', $id)->update(['pid' => null]);
        $this->content->whereIn('goods', $id)->delete();
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'goods_class|分类' => 'require',
            'name|名称' => 'require|length:1,255',
            'code|编号' => 'require|length:1,255|unique:goods,code',
            'describe|描述' => 'require|length:1,255',
            'amount|单价' => 'require|between:0.01,100000000',
            'sort|排序' => 'require|integer|between:1,999',
            'status|状态' => 'require|in:on,off',
            'imageId|图片' => 'require|array',
            'fwb-content|详情' => 'require|max:20000',
            'express_number|每单数量' => 'require|integer|between:1,1000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'goods_class|分类' => 'require',
            'name|名称' => 'require|length:1,255',
            'code|编号' => 'require|length:1,255|unique:goods,code,' . $id . ',id',
            'describe|描述' => 'require|length:1,255',
            'amount|单价' => 'require|between:0.01,100000000',
            'sort|排序' => 'require|integer|between:1,999',
            'status|状态' => 'require|in:on,off',
            'imageId|图片' => 'require|array',
            'fwb-content|详情' => 'require|max:20000',
            'express_number|每单数量' => 'require|integer|between:1,1000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function validator_delete($id)
    {
        if (SUBSTATION != '0') parent::ajax_exception(000, '你无权这样做');
    }

    //保存商品与图片关系
    public function image_save(GoodsModel $model, Request $request)
    {
        //id
        $ids = $request->post('imageId');

        //清除旧图片绑定
        $images = new GoodsImagesModel();
        $images->where('pid', '=', $model['id'])->whereNotIn('pid', $ids)->update(['pid' => null]);

        //添加新图片绑定
        $this->image->whereIn('id', $ids)->update(['pid' => $model['id']]);

        //添加第一张图片到商品封面信息
        $first = array_shift($ids);//获取id
        $images = new GoodsImagesModel();//初始化模型
        $image = $images->where('id', '=', $first)->find();//寻找信息
        if (!is_null($image)) {//找到信息

            //赋值并保存
            $model->cover = $image->id;
            $model->location = $image->location;
            $model->save();
        }
    }

    //图片上传
    public function image(Request $request)
    {
        // 获取表单上传文件 例如上传了001.jpg
        $file = $request->file('images');

        $location = 'goods_' . time();

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size' => (1024 * 1024), 'ext' => 'jpg,png,gif,jpeg,bmp'])->move($this->dir, $location);

        // 上传失败获取错误信息
        if (!$info) parent::ajax_exception(000, $file->getError());

        $model = $this->image;
        $model->location = '/' . $this->dir . '/' . $info->getSaveName();
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

        return [
            'image' => $model->location,
            'imageId' => $model->id,
            'index' => $request->post('index'),
        ];
    }

    //过期文件删除
    public function image_delete()
    {
        //过期时间
        $date = date('Y-m-d', strtotime('-1 day')) . ' 00:00:00';

        //验证今天是否执行过删除
        $storage = new StorageClass('goods_image_delete');
        $over = $storage->get();
        if (!is_array($over) && ($over >= $date)) return;//执行过

        //寻找并删除文件
        $model = new GoodsImagesModel();
        $result = $model->where('created_at', '<', $date)->where('pid', '=', null)->select();
        if (count($result) > 0) foreach ($result as $v) {

            if (!is_null($v->location) && file_exists(substr($v->location, 1))) unlink(substr($v->location, 1));
        }

        //删除数据
        $model = new GoodsImagesModel();
        $model->where('created_at', '<', $date)->where('pid', null)->delete();

        //保存删除时间
        $storage->save($date);
    }

    //库存变更验证
    public function validator_stock(Request $request)
    {
        $rule = [
            'id' => 'require',
            'type|操作类型' => 'require|in:1,2',
            'number|数量' => 'require|integer|between:1,100000000',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    //库存变更
    public function stock(Request $request)
    {
        $model = $this->model->where('id', '=', $request->post('id'))->find();

        if (is_null($model)) parent::ajax_exception(000, '商品不存在');

        $record = new GoodsRecordModel();
        $record->goods_id = $model->id;
        $record->name = $model->name;
        $record->code = $model->code;
        $record->created_at = date('Y-m-d H:i:s');

        $master = parent::master();
        $number = $request->post('number');
        $type = $request->post('type');
        switch ($type) {
            case '1':
                $model->stock += $number;

                $record->stock = $number;
                $record->content = '管理员『' . $master['nickname'] . '』,入库商品『' . $model->name . '(编号：' . $model->code . ')』：' . $number . '件';
                break;
            case '2':
                $model->stock -= $number;

                $record->stock = 0 - $number;
                $record->content = '管理员『' . $master['nickname'] . '』,出库商品『' . $model->name . '(编号：' . $model->code . ')』：' . $number . '件';
                break;
            default:
                parent::ajax_exception(000, '类型错误');
                break;
        }

        $record->stock_now = $model->stock;
        $record->type = $type;

        $record->save();
        $model->save();
    }

    public function record(Request $request)
    {
        $model = new GoodsRecordModel();

        $where = [];

        $where[] = ['goods_id', '=', $request->get('id')];

        $type = $request->get('type');

        if (!empty($type)) $where[] = ['type', '=', $type];

        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');

        if (!empty($startTime)) {
            $where[] = ['created_at', '>=', $startTime];
        }
        if (!empty($endTime)) {
            $where[] = ['created_at', '<', $endTime];
        }

        return parent::page($model, ['where' => $where]);
    }

    //上传新的logo
    public function image_content(Request $request)
    {
        $dir = 'goods_content';
        if (!is_dir($dir)) mkdir($dir);

        // 获取表单上传文件 例如上传了001.jpg
        $file = $request->file('file');

        $location = 'goods_content_' . time();

        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->validate(['size' => (1024 * 1024), 'ext' => 'jpg,png,gif,jpeg,bmp'])->move($dir, $location);

        // 上传失败获取错误信息
        if (!$info) $file->getError();

        $location = '/' . $dir . '/' . $info->getSaveName();

        return [
            'src' => $location,
        ];
    }
}