<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/25
 * Time: 下午5:12
 */

namespace classes\order;

use app\order\model\OrderExpressModel;
use app\order\model\OrderModel;
use app\order\model\OrderSendModel;
use classes\AdminClass;
use classes\system\SystemClass;
use classes\vendor\SmsClass;
use think\Loader;
use think\Request;

class SendClass extends AdminClass
{
    public $model;
    public $dir = 'uploads';

    public function __construct()
    {
        $this->model = new OrderSendModel();

        if (!is_dir($this->dir)) mkdir($this->dir);
    }

    //发货单列表数据
    public function index(Request $request)
    {
        $id = $request->get('id');//id
        $keywordType = $request->get('keywordType');//关键字筛选类型
        $keyword = $request->get('keyword');//关键字
        $timeType = $request->get('timeType');//时间筛选类型
        $startTime = $request->get('startTime');//起始时间
        $endTime = $request->get('endTime');//结束时间

        $where = [];//初始化筛选

        if (!empty($id)) $where[] = ['order_id', '=', $id];//id筛选
        if (!empty($keyword)) switch ($keywordType) {
            case '1':
                $where[] = ['order_number', 'like', '%' . $keyword . '%'];
                break;
            case '2':
                $where[] = ['express_no', 'like', '%' . $keyword . '%'];
                break;
            default:
                break;
        }
        if (!empty($startTime)) switch ($timeType) {
            case '1':
                $where[] = ['order_create', '>=', $startTime];
                break;
            case '2':
                $where[] = ['send_create', '>=', $startTime];
                break;
            default:
                break;
        }
        if (!empty($endTime)) switch ($timeType) {
            case '1':
                $where[] = ['order_create', '<=', $endTime];
                break;
            case '2':
                $where[] = ['send_create', '<=', $endTime];
                break;
            default:
                break;
        }

        $result = [
            'where' => $where
        ];

        return parent::page($this->model, $result);
    }

    //添加发货单(导出时)
    public function store_send()
    {
        $date = input('time');
//        $date = '1991-03-15 01:01:01';
//        $date = date('Y-m-d') . ' 00:00:00';

//        if (empty($date)) parent::ajax_exception(000, '请选择下单时间');
        if (empty($date)) $date = date('Y-m-d') . ' 00:00:00';

        //寻找已经生成了发货单且没发货的订单
        $send = new OrderSendModel();
        $send_id = $send->where('order_create', '>=', $date)->where('send_create', '=', null)->group('order_id')->column('order_id');

        //寻找需要生成发货单的订单
        $order = new OrderModel();
        if (!empty($send_id)) $order = $order->whereNotIn('id', $send_id);
        $order = $order->where('created_at', '>=', $date)->where('order_status', '=', '10')->column('*');

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
        $fileName = $this->dir.'/' . $fileName . "_" . time() . ".xls";

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
        $date = input('time');
//        $date = '1991-03-15 01:01:01';
//        $date = date('Y-m-d') . ' 00:00:00';

        $send = new OrderSendModel();
        $sends = $send->where('order_create', '>=', $date)->where('send_create', '=', null)->column('*');
        if (count($sends) <= 0) parent::ajax_exception(000, '没有需要发货的订单');

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
        ];

        $data = [];
        $order_numbers = [];
        foreach ($sends as $k => $v) {

            $order_numbers[] = $v['order_number'];

            $data[$k]['id'] = $v['id'];
            $data[$k]['order_number'] = $v['order_number'];
            $data[$k]['order_create'] = $v['order_create'];
            $data[$k]['store'] = $v['store'];
            $data[$k]['express'] = $v['express'];
            $data[$k]['goods'] = $v['goods'];
            $data[$k]['consignee'] = $v['consignee'];
            $data[$k]['phone'] = $v['phone'];
            $data[$k]['address'] = $v['address'];
            $data[$k]['express_no'] = $v['express_no'];
        }

        //修改订单状态
        $model = new OrderModel();
        $model->whereIn('order_number',$order_numbers)->update(['order_status' => '15']);

        return self::excelExport($name, $header, $data);
    }

    //删除过期excel
    public function excel_delete()
    {
        if (!is_dir($this->dir)) return;//不是文件夹

        $files = scandir($this->dir);//读取文件

        $delete_time = strtotime('-1 day');//过期时间，1天前

        //循环文件
        foreach ($files as $v) {

            if ($v == '.' || $v == '..') continue;//过滤

            $file = $this->dir . '/' . $v;//文件路径

            $time = filectime($file);//获取创建时间

            if ($time <= $delete_time) unlink($file);//删除过期文件
        }
    }

    /**
     * 批量上传
     *
     * @param Request $request
     * @return int
     */
    public function read_send(Request $request)
    {
        $filename = 'file_excel' . time();
        $url = $this->dir;

        //获取表单上传文件
        $file = $request->file('file');
        $info = $file->move($url, $filename);
        if ($info) {

            $filename = $url . '/' . $info->getSaveName();
            unset($info);
            //判断版本，这里有的网上的版本没有进行判断，导致会报大概这样的错误：
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if ($extension == 'xlsx') {
                $objReader = \PHPExcel_IOFactory::createReader('Excel2007');
                //加载文件内容,编码utf-8
                $objPHPExcel = $objReader->load($filename);
            } else if ($extension == 'xls') {
                $objReader = \PHPExcel_IOFactory::createReader('Excel5');
                $objPHPExcel = $objReader->load($filename);
            } else {
                unlink($filename);
                parent::ajax_exception(000, '请上传excel格式的文件!');
            }

            $excel_array = $objPHPExcel->getsheet(0)->toArray();   //转换为数组格式

            array_shift($excel_array);//去表头

            //格式化并验证
            $result = [];
            $ids = [];
            $nos = [];
            $date = date('Y-m-d H:i:s');
            foreach ($excel_array as $k => $v) {

                if (count($v) != 2) {
                    unlink($filename);
                    parent::ajax_exception(000, '导入文件格式有误');
                }
                list($id, $no) = $v;

                if (in_array($id, $ids)) {
                    unlink($filename);
                    parent::ajax_exception(000, '发货编号『' . $id . '』重复(第' . ($k + 2) . '行)');
                }
                if (empty($id)) {
                    unlink($filename);
                    parent::ajax_exception(000, '发货编号不得为空(第' . ($k + 2) . '行)');
                }
                if (in_array($no, $nos)) {
                    unlink($filename);
                    parent::ajax_exception(000, '快递单号『' . $id . '』重复(第' . ($k + 2) . '行)');
                }
                if (empty($no)) {
                    unlink($filename);
                    parent::ajax_exception(000, '快递单号不得为空(第' . ($k + 2) . '行)');
                }
                $ids[] = $id;
                $nos[] = $no;

                $result[] = [
                    'id' => $id,
                    'express_no' => $no,
                    'send_create' => $date,
                ];
            }

            if (count($result) > 0) {

                $model = new OrderSendModel();
                $model->isUpdate()->saveAll($result);

                //发送发货短信
                $order_ids = new OrderSendModel();
                $order_ids = $order_ids->whereIn('id', $ids)->group('order_id')->column('order_id');
                if (count($order_ids) > 0) {

                    $order = new OrderModel();
                    $order->whereIn('id', $order_ids)->update(['order_status' => '20']);

                    $orders = new OrderModel();
                    $orders = $orders->whereIn('id', $order_ids)->column('id,member_phone,order_number,member_nickname');

                    //发送短信
                    $setting = new SystemClass();
                    $set = $setting->index();
                    $class = new SmsClass();
                    $class->TemplateParam = [
                        'web' => $set['webName']
                    ];
                    foreach ($orders as $v) {

                        $class->TemplateParam['username'] = $v['member_nickname'];
                        $class->TemplateParam['order'] = $v['order_number'];
                        $class->sendSms($v['member_phone'], $v['order_number'], 'SMS_151996093');
                    }
                }
            }

            unlink($filename);

            return count($result);
        }

        parent::ajax_exception(000, '上传失败!');
    }
}