<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/27
 * Time: 下午2:18
 */

namespace classes\index;


use app\order\model\OrderExpressModel;
use app\order\model\OrderModel;
use app\order\model\OrderSendModel;
use think\Request;

class OrderDownloadClass extends \classes\IndexClass
{
    private $ids;
    public $model;
    public $dir = 'orderdownload';

    public function __construct()
    {
        $this->model = new OrderSendModel();

        if (!is_dir($this->dir)) mkdir($this->dir);
    }

    //验证导出间隔
    public function test_time()
    {
        session(['expire' => 3600]);

        //获取可下载时间
        $time = session($this->dir);

        //还未到时间
//        if ($time && ($time > time())) parent::ajax_exception(000, '操作太频繁了，请休息一下再试');

        //保存下次下载时间
       session($this->dir, (time() + 60 * 5));
    }

    //添加发货单(导出时)
    public function store_send()
    {
        //ID确认
        $id = input('id');dd($id);
        if (empty($id)) parent::ajax_exception(000, '请选择需要导出的订单');
        $ids = $this->ids = explode(',', $id);

        //寻找已经生成了发货单且没发货的订单
        $send = new OrderSendModel();
        $send_id = $send->whereIn('order_id', $ids)->group('order_id')->column('order_id');

        //寻找需要生成发货单的订单
        $order = new OrderModel();
        if (!empty($send_id)) $ids = array_diff($ids, $send_id);
        $order = $order->whereIn('id', $ids)->column('*');

        //没有需要生成的
        if (count($order) <= 0) return;

        //添加发货单号
        $insert = [];
        $date = date('Y-m-d H:i:s');
        $express = new OrderExpressModel();
        $express = $express->whereIn('order_id', array_keys($order))->column('*');
        foreach ($express as $k => $v) {

            $o = $order[$v['order_id']];
            $i = 0;

            foreach (explode('#$%', $v['content']) as $ke => $va) {

                list($name, $phone, $address) = explode('#$', $va);

                $insert[$i]['send_order'] = $o['order_number'] . '-' . ($ke + 1);
                $insert[$i]['order_id'] = $o['id'];
                $insert[$i]['order_number'] = $o['order_number'];
                $insert[$i]['order_create'] = $o['created_at'];
                $insert[$i]['store'] = $o['store_name'];
                $insert[$i]['express'] = $o['express_name'];
                $insert[$i]['goods'] = $o['goods_name'];
                $insert[$i]['goods_number'] = $o['goods_number'];
                $insert[$i]['consignee'] = $name;
                $insert[$i]['phone'] = $phone;
                $insert[$i]['address'] = $address;
                $insert[$i]['created_at'] = $date;
                $i++;
            }

            if (count($insert) > 0) {

                $model = new OrderSendModel();
                $model->insertAll($insert);
            }
        }
    }

    /**
     * excel表格导出
     *
     * @param string $fileName
     * @param array $headArr
     * @param array $data
     * @return string
     */
    private function excelExport($fileName = '', $headArr = [], $data = [])
    {
        $fileName = $this->dir . '/' . $fileName . "_" . time() . ".xls";

        $objPHPExcel = new \PHPExcel();

        $objPHPExcel->getProperties();

        $key = ord("A"); // 设置表头

        foreach ($headArr as $v) {

            $colum = chr($key);

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);

            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum . '1', $v);

            $key += 1;

        }

        $column = 2;

        $objActSheet = $objPHPExcel->getActiveSheet();

        foreach ($data as $key => $rows) { // 行写入

            $span = ord("A");

            foreach ($rows as $keyName => $value) { // 列写入

                $objActSheet->setCellValue(chr($span) . $column, $value);

                $span++;

            }

            $column++;

        }

        $objPHPExcel->setActiveSheetIndex(0); // 设置活动单指数到第一个表,所以Excel打开这是第一个表

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

        $objWriter->save($fileName);

        return $fileName;
    }

    /**
     * 返回
     *
     * @return string
     */
    public function excel()
    {
        $send = new OrderSendModel();

        $sends = $send->whereIn('order_id', $this->ids)->column('*');
        if (count($sends) <= 0) parent::ajax_exception(000, '没有找到发货单');

        $name = 'order' . date('Y-m-d');

        $header = [
            '发货编号',
            '订单号',
            '下单时间',
            '店铺名称',
            '快递名称',
            '商品名称',
            '收件人',
            '收件人电话',
            '收件地址',
            '快递单号',
            '发货时间',
        ];

        $data = [];
        $order_numbers = [];
        foreach ($sends as $k => $v) {

            $order_numbers[] = $v['order_number'];

            $data[$k]['send_order'] = $v['send_order'];
            $data[$k]['order_number'] = $v['order_number'];
            $data[$k]['order_create'] = $v['order_create'];
            $data[$k]['store'] = $v['store'];
            $data[$k]['express'] = $v['express'];
            $data[$k]['goods'] = $v['goods'];
            $data[$k]['consignee'] = $v['consignee'];
            $data[$k]['phone'] = $v['phone'];
            $data[$k]['address'] = $v['address'];
            $data[$k]['express_no'] = $v['express_no'];
            $data[$k]['send_create'] = $v['send_create'];
        }

        return self::excelExport($name, $header, $data);
    }

    //删除过期excel
    public function excel_delete()
    {
        if (!is_dir($this->dir)) return;//不是文件夹

        $files = scandir($this->dir);//读取文件

        $delete_time = strtotime('-5 minutes');//过期时间，1小时

        //循环文件
        foreach ($files as $v) {

            if ($v == '.' || $v == '..') continue;//过滤

            $file = $this->dir . '/' . $v;//文件路径

            $time = filectime($file);//获取创建时间

            if ($time <= $delete_time) unlink($file);//删除过期文件
        }
    }
}