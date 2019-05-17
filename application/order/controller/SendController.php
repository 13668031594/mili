<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/25
 * Time: 下午5:34
 */

namespace app\order\controller;


use app\http\controller\AdminController;
use classes\order\SendClass;
use think\Db;
use think\Request;

class SendController extends AdminController
{
    private $class;

    public function __construct()
    {
        $this->class = new SendClass();
    }

    //发货单首页
    public function getIndex(Request $request)
    {
        $id = $request->get('id');

        return parent::view('sendList', ['id' => $id]);
    }

    //发货单数据
    public function getTable(Request $request)
    {
        $result = $this->class->index($request);

        return parent::tables($result);
    }

    //批量发货页面
    public function getBat()
    {
        return parent::view('sendBat');
    }

    //发货单导出
    public function getBats()
    {

        Db::startTrans();

        //删除过期的excel文件
        $this->class->excel_delete();

        //添加发货单
        $this->class->store_send();

        if (input('type') == '1') {

            //生成excel
            $url = $this->class->excel();

            $result = [
                'status' => 'success',
                'url' => '/' . $url,
                'message' => '生成成功',
            ];

            Db::commit();

            return json($result);
        } else {

            $this->class->jushuitan_order();

            Db::commit();

            $this->class->jushuitan_ok();
        }
    }

    //导入
    public function postSends(Request $request)
    {
        $number = $this->class->read_send($request);

        return parent::success('', '操作成功', ['number' => $number]);
    }
}