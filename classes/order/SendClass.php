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
use classes\vendor\JushuitanClass;
use classes\vendor\SmsClass;
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

        if (!empty($id)) $where[] = ['a.order_id', '=', $id];//id筛选
        if (!empty($keyword)) switch ($keywordType) {
            case '1':
                $where[] = ['a.order_number', 'like', '%' . $keyword . '%'];
                break;
            case '2':
                $where[] = ['a.express_no', 'like', '%' . $keyword . '%'];
                break;
            default:
                break;
        }
        if (!empty($startTime)) switch ($timeType) {
            case '1':
                $where[] = ['a.order_create', '>=', $startTime];
                break;
            case '2':
                $where[] = ['a.send_create', '>=', $startTime];
                break;
            default:
                break;
        }
        if (!empty($endTime)) switch ($timeType) {
            case '1':
                $where[] = ['a.order_create', '<=', $endTime];
                break;
            case '2':
                $where[] = ['a.send_create', '<=', $endTime];
                break;
            default:
                break;
        }

        $whereIn = ['o.substation' => parent::substation_ids()];

        $leftJoin = [
            'order o',
            'o.id = a.order_id'
        ];

        $result = [
            'whereIn' => $whereIn,
            'where' => $where,
            'alias' => 'a',
            'leftJoin' => $leftJoin,
            'column' => 'a.*',
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
        if (empty($date)) $date = date('Y-m-d 00:00:00', strtotime('-3 day'));

        //寻找已经生成了发货单且没发货的订单
        $send = new OrderSendModel();
        $send_id = $send->where('order_create', '>=', $date)->where('send_create', '=', null)->group('order_id')->column('order_id');

        //寻找需要生成发货单的订单
        $order = new OrderModel();
        if (!empty($send_id)) $order = $order->whereNotIn('id', $send_id);
        $order = $order->whereIn('substation', parent::substation_ids())->where('created_at', '>=', $date)->where('order_status', '=', '10')->column('*');

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

//                list($name, $phone, $address) = explode('#$', $va);
                list($name, $phone, $address, $pro, $city, $area, $add) = explode('#$', $va);

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
                $insert[$i]['pro'] = $pro;
                $insert[$i]['city'] = $city;
                $insert[$i]['area'] = $area;
                $insert[$i]['add'] = $add;
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
        $date = input('time');

        $send = new OrderSendModel();
        $sends = $send->alias('a')
            ->leftJoin('order o', 'o.id = a.order_id')
//            ->where('o.substation', 'in', parent::substation_ids())
            ->where('o.substation', '=', input('sub_station'))
            ->where('o.order_status', 'in', [10, 15])
            ->where('a.order_create', '>=', $date)
            ->where('a.send_create', '=', null)
            ->column('a.*');

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
            '省',
            '市',
            '区',
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
            $data[$k]['pro'] = $v['pro'];
            $data[$k]['city'] = $v['city'];
            $data[$k]['area'] = $v['area'];
            $data[$k]['add'] = $v['add'];
            $data[$k]['express_no'] = $v['express_no'];
        }

        //修改订单状态
        $model = new OrderModel();
        $model->whereIn('order_number', $order_numbers)->update(['order_status' => '15']);

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

                if (count($v) != 13) {
                    unlink($filename);
                    parent::ajax_exception(000, '导入文件格式有误');
                }
                $id = $v[0];
                $no = $v[12];
//                list($id, $no) = $v;

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

    //上传订单到聚水潭
    public function jushuitan_order()
    {
        $class = new JushuitanClass();

        //获取时间
        $date = input('time');

        //获得需要上传的订单
        $send = new OrderSendModel();
        $sends = $send->alias('a')
            ->leftJoin('order o', 'o.id = a.order_id')
            ->leftJoin('goods g', 'g.id = o.goods_id')
            ->where('o.substation', '=', input('sub_station'))
            ->where('o.order_status', 'in', [10])
            ->where('a.order_create', '>=', $date)
            ->where('a.send_create', '=', null)
            ->column('a.*,o.total_goods,o.goods_amount,o.express_amount,o.express_number,o.store_address,g.code,o.member_account,o.total');

        if (count($sends) <= 0) parent::ajax_exception(000, '没有需要发货的订单');

        //商品编号
        $sku_id = [];
        foreach ($sends as $v) if (!in_array($v['code'], $sku_id) && !empty($v['code'])) $sku_id[] = $v['code'];
        if (empty($sku_id)) parent::ajax_exception(000, '请先核对商品编号1');

        //50个一组
        $sku_id = array_chunk($sku_id, 50);
        //可用编号
        $sku = [];
        //寻找在库存中的商品
        foreach ($sku_id as $v) {

            //通过接口获取数据
            $result = $class->inventory_query(implode(',', $v));

            //没有成功获取到数据
            if (!isset($result['inventorys'])) continue;

            //将返回的商品编号放入可用编号数组中
            foreach ($result['inventorys'] as $va) $sku[] = $va['sku_id'];
        }

        if (empty($sku)) parent::ajax_exception(000, '请先核对商品编号2');

        $data = [];
        $order_numbers = [];
        $set = new \classes\system\JushuitanClass();
        $set = $set->index();
        foreach ($sends as $k => $v) {

            //商品编号聚水潭中没有，直接下一个
            if (!in_array($v['code'], $sku)) continue;

            //放入需要编辑为发货的订单号
            $order_numbers[] = $v['order_number'];

            //初始化上传数组
            $data[$k]['shop_id'] = $set['jushuitanShopid'];
            $data[$k]['so_id'] = $v['send_order'];
            $data[$k]['order_date'] = $v['order_create'];
            $data[$k]['shop_status'] = 'WAIT_SELLER_SEND_GOODS';//等待发货
            $data[$k]['shop_buyer_id'] = $v['consignee'];//买家昵称
            $data[$k]['receiver_state'] = $v['pro'];//收货省
            $data[$k]['receiver_city'] = $v['city'];//收货市
            $data[$k]['receiver_district'] = $v['area'];//收货区
            $data[$k]['receiver_address'] = $v['add'];//收货地址
            $data[$k]['receiver_name'] = $v['consignee'];//收货人
            $data[$k]['receiver_mobile'] = $v['phone'];//收货电话
            $data[$k]['pay_amount'] = number_format(($v['total_goods'] / $v['express_number']), 2, '.', '');//应付金额，保留两位小数，单位（元）
            $data[$k]['freight'] = $v['express_amount'];//运费，保留两位小数，单位（元）
            $data[$k]['remark'] = '发货：' . $v['store_address'];//备注
            $data[$k]['shop_modified'] = '';//店铺修改日期
            $data[$k]['logistics_company'] = $v['express'];//快递公司
            $data[$k]['items'] = [
                [
                    'sku_id' => $v['code'],//商品sku
                    'shop_sku_id' => $v['code'],//网站sku
                    'amount' => $v['goods_amount'],//应付金额，保留两位小数，单位（元）；备注：可能存在人工改价
                    'base_price' => $v['goods_amount'],//基本价（拍下价格），保留两位小数，单位（元）
                    'qty' => $v['goods_number'],//购买数量
                    'name' => $v['goods'],//商品名称
                    'outer_oi_id' => $v['id'],//id
                ],
            ];
            $data[$k]['pay'] = [
                'outer_pay_id' => $v['send_order'],//外部支付单号，最大50
                'pay_date' => $v['order_create'],//支付日期
                'payment' => '米礼网余额支付',//支付方式
                'seller_account' => $v['member_account'],//卖家账号
                'buyer_account' => $v['phone'],//买家账号
                'amount' => number_format(($v['total'] / $v['express_number']), 2, '.', ''),//支付金额
            ];
        }

        if (count($data) <= 0) parent::ajax_exception(000, '请先核对商品编号3');

        $result = $class->orders_upload(array_values($data));

        if ($result['code'] != '0') parent::ajax_exception(000, '上传失败：' . $result['msg'] . '。code：' . $result['code']);

        //修改订单状态
        $model = new OrderModel();
        $model->whereIn('order_number', $order_numbers)->update(['order_status' => '15']);
    }

    public function jushuitan_ok()
    {
        parent::ajax_exception(101, '上传成功，请登录聚水潭账号管理');
    }

    public function send_info($id)
    {
        $model = new OrderSendModel();

        $a = $model->find($id);

        return $a;
    }

    public function send(Request $request)
    {
        $rule = [
            'id|发货单号' => 'require',
            'express_no|快递号' => 'require|length:1,255',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        $model = new OrderSendModel();

        $a = $model->find($request->post('id'));
        $a->express_no = $request->post('express_no');
        $a->send_create = date('Y-m-d H:i:s');
        $a->save();
    }
}