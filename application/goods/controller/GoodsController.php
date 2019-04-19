<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/24
 * Time: 下午7:25
 */

namespace app\goods\controller;

use app\http\controller\AdminController;
use classes\goods\GoodsClass;
use think\Request;

class GoodsController extends AdminController
{
    private $class;

    public function __construct(Request $request)
    {
        //初始化类库
        $this->class = new GoodsClass();

        //删除过期图片
        $this->class->image_delete();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getIndex()
    {
        $goods_class = $this->class->create();

        //视图
        return parent::view('index', ['goods_class' => $goods_class]);
    }

    /**
     * json返回列表数据
     *
     * @return \think\response\Json
     */
    public function getTable()
    {
        $result = $this->class->index();

        return parent::tables($result);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function getCreate()
    {
        $goods_class = $this->class->create();

        return parent::view('goods', ['goods_class' => $goods_class]);
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int $id
     * @return \think\Response
     */
    public function getEdit($id)
    {
        //获取数据
        $result = $this->class->edit($id);

        $result['goods_class'] = $this->class->create();

        //视图
        return parent::view('goods', $result);
    }

    /**
     * 删除指定资源
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function getDelete(Request $request)
    {
        $ids = explode(',', $request->get('id'));

        //验证资源
        $this->class->validator_delete($ids);

        //删除
        $this->class->delete($ids);

        //反馈成功
        return parent::success('/goods/index');
    }

    /**
     * 保存资源
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function postSave(Request $request)
    {
        $id = $request->post('id');

        if (empty($id)) return self::save($request);
        else return self::update($id, $request);
    }

    /**
     * 保存新建的资源
     *
     * @param Request $request
     * @return \think\response\Json
     */
    private function save(Request $request)
    {
        //验证字段
        $this->class->validator_save($request);

        //添加
        $this->class->save($request);

        //反馈成功
        return parent::success('/goods/index');
    }

    /**
     * 保存更新的资源
     *
     * @param $id
     * @param Request $request
     * @return \think\response\Json
     */
    private function update($id, Request $request)
    {
        //验证字段
        $this->class->validator_update($id, $request);

        //更新
        $this->class->update($id, $request);

        //反馈成功
        return parent::success('/goods/index');
    }

    /**
     * 上传图片
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function postImage(Request $request)
    {
        $result = $this->class->image($request);

        return parent::success('', null, $result);
    }

    /**
     * 库存操作页面
     *
     * @param $id
     * @return \think\response\View
     */
    public function getStock($id)
    {
//        $id = $request->get('id');

        $self = $this->class->read($id);

        return parent::view('stock', ['self' => $self]);
    }

    /**
     * 库存变更
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function postStock(Request $request)
    {
        $this->class->validator_stock($request);

        $this->class->stock($request);

        return parent::success('/admin/goods');
    }

    public function getRecord($id)
    {
        $self = $this->class->read($id);

        return parent::view('record',['self' => $self]);
    }

    public function getRecordTable(Request $request)
    {
        $result = $this->class->record($request);

        return parent::tables($result);
    }

    public function postImageContent(Request $request)
    {
        $src = $this->class->image_content($request);

        if (!is_array($src)) {

            $result = [
                'code' => '1',
                'msg' => $src,
                'data' => [
                    'src' => '',
                    'total' => ''
                ]
            ];
        } else {

            $result = [
                'code' => '0',
                'msg' => '',
                'data' => $src
            ];
        }

        return json_encode($result);
    }
}