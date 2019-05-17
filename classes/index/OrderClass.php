<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/27
 * Time: 下午2:18
 */

namespace classes\index;


use app\express\model\ExpressModel;
use app\goods\model\GoodsAmountModel;
use app\goods\model\GoodsContentModel;
use app\goods\model\GoodsModel;
use app\goods\model\GoodsRecordModel;
use app\member\model\MemberGradeExpressModel;
use app\member\model\MemberGradeModel;
use app\member\model\MemberModel;
use app\member\model\MemberRecordModel;
use app\member\model\MemberStoreModel;
use app\order\model\OrderExpressModel;
use app\order\model\OrderModel;
use app\order\model\OrderSendModel;
use think\Db;
use think\Model;
use think\Request;

class OrderClass extends \classes\IndexClass
{
    private $member;

    public function __construct()
    {
        $this->member = parent::member();
    }

    //礼品-详情
    public function goods_info($id)
    {
        $model = new GoodsModel();

        $result = $model->where('id', '=', $id)->where('status', '=', 'on')->find();

        if (is_null($result)) parent::redirect_exception('/', '商品已下架');

        $result['location'] = (!is_null($result['location']) && file_exists(substr($result['location'], 1))) ? $result['location'] : config('young.image_not_found');
//exit('123');
        if (SUBSTATION != '0') {

            $amount = new GoodsAmountModel();
            $a = $amount->where('goods_id', '=', $result['id'])->where('substation', '=', SUBSTATION)->find();
            if (!is_null($a)) $result['amount'] = $a->amount;
        }

        return $result;
    }

    public function goods_content($id)
    {
        $model = new GoodsContentModel();

        $result = $model->where('goods', '=', $id)->find();

        return $result;
    }

    //快递列表
    public function express()
    {
        $model = new ExpressModel();

        $express = $model->where('substation', '=', SUBSTATION)->where('disabled', '=', 'on')->order('sort', 'desc')->column('id,name');

        $member = parent::member();

        $grade = new MemberGradeModel();
        $grade = $grade->where('id', '=', $member['grade_id'])->find();
        foreach ($express as $k => &$v) {

            if ($grade->mode == 'on') $v .= '/' . $grade->amount;
            else {

                $model = new MemberGradeExpressModel();
                $model = $model->where('express', '=', $k)->where('grade', '=', $grade->id)->find();
                $v .= '/' . ($model ? $model->amount : config('young.default_express_amount'));
            }
        }

        return $express;
    }

    //最大数量提示
    public function prompt($goods)
    {
        $set = parent::set();

        $prompt = str_replace('{$number}', "<span id='max-num'>{$goods['express_number']}</span>", $set['goods_number']);

        return $prompt;
    }

    //已购礼品
    public function goods_had_table()
    {
        $member = session('member');
        $where = [
            ['member_id', '=', $member['id']]
        ];

        //时间筛选
        $time_type = input('timeType');
        switch ($time_type) {
            case '1'://今天
                $date = date('Y-m-d');
                $where[] = ['created_at', '>=', $date . ' 00:00:00'];
                $where[] = ['created_at', '<=', $date . ' 23:59:59'];
                break;
            case '3'://昨天
                $date = date('Y-m-d', strtotime('-1 day'));
                $where[] = ['created_at', '>=', $date . ' 00:00:00'];
                $where[] = ['created_at', '<=', $date . ' 23:59:59'];
                break;
            case '2'://时间段
                $start = input('startTime');
                $end = input('endTime');
                if (!empty($start)) $where[] = ['created_at', '>=', $start];
                if (!empty($end)) $where[] = ['created_at', '<=', $end];
                break;
            default:
                break;
        }

        //店铺筛选
        $store = input('store');
        if (!empty($store)) $where[] = ['store_id', '=', $store];

        //关键字
        $order_type = input('orderType');
        $order = input('order');
        if (!empty($order) && ($order_type == '1')) $where[] = ['order_number', 'like', '%' . $order . '%'];

        $column = 'id,order_status,order_number,total,goods_name,express_number,express_amount,goods_number,goods_amount,created_at,note';

        $other = [
            'column' => $column,
            'where' => $where,
        ];

        $order = new OrderModel();
        return parent::page($order, $other);
    }

    //发货清单
    public function goods_send_table()
    {
        $member = session('member');
        $where = [
            ['o.member_id', '=', $member['id']]
        ];

        //时间筛选
        $time_type = input('timeType');
        switch ($time_type) {
            case '1'://今天
                $date = date('Y-m-d');
                $where[] = ['o.created_at', '>=', $date . ' 00:00:00'];
                $where[] = ['o.created_at', '<=', $date . ' 23:59:59'];
                break;
            case '3'://昨天
                $date = date('Y-m-d', strtotime('-1 day'));
                $where[] = ['o.created_at', '>=', $date . ' 00:00:00'];
                $where[] = ['o.created_at', '<=', $date . ' 23:59:59'];
                break;
            case '2'://时间段
                $start = input('startTime');
                $end = input('endTime');
                if (!empty($start)) $where[] = ['o.created_at', '>=', $start];
                if (!empty($end)) $where[] = ['o.created_at', '<=', $end];
                break;
            default:
                break;
        }

        //店铺筛选
        $store = input('store');
        if (!empty($store)) $where[] = ['o.store_id', '=', $store];

        //关键字
        $order_type = input('orderType');
        $order = input('order');
        if (!empty($order)) {

            switch ($order_type) {
                case '2':
                    $where[] = ['a.express_no', 'like', '%' . $order . '%'];
                    break;
                case '3':
                    $where[] = ['a.phone', 'like', '%' . $order . '%'];
                    break;
                default:
                    $where[] = ['o.order_number', 'like', '%' . $order . '%'];
                    break;
            }
        }

        $column = 'a.id,a.order_number orderNo,a.send_create sendTime,o.goods_location goodsUrl,o.goods_name goodsName,
        o.goods_amount goodsAmount,o.goods_weight goodsWeight,o.goods_number/o.express_number goodsNumber,a.consignee consigneeName,
        a.phone consigneePhone,a.address consigneeAds,a.express_no expressNo,a.express expressName,o.total/o.express_number totalAmount';

        $alias = 'a';

        $leftjoin = [
            'order o',
            'o.id = a.order_id'
        ];

        $other = [
            'alias' => $alias,
            'leftJoin' => $leftjoin,
            'column' => $column,
            'where' => $where,
            'order_name' => 'o.created_at'
        ];

        $order = new OrderSendModel();
        $result = parent::page($order, $other);
        foreach ($result['message'] as &$v) {

            $v['status'] = is_null($v['sendTime']) ? '待发货' : '已发货';
            $v['sendTime'] = is_null($v['sendTime']) ? '暂未发货' : $v['sendTime'];
            $v['expressNo'] = is_null($v['expressNo']) ? '暂无单号' : $v['expressNo'];
            $v['totalAmount'] = number_format($v['totalAmount'], 2, '.', '');
            $v['goodsNumber'] = number_format($v['goodsNumber']);
            if (is_null($v['goodsUrl']) || !file_exists(substr($v['goodsUrl'], 1))) $v['goodsUrl'] = config('young.image_not_found');
        }

        return $result;
    }

    /**
     * 下单验证，并格式化收货地址
     */
    /**
     * 验证输入字段，并解析拆分
     *
     * @param Request $request
     * @return array
     */
    public function validator_order(Request $request)
    {
        $rule = [
            'goods|礼品' => 'require',
            'number|每单数量' => 'require|integer|between:1,1000',
            'store|店铺' => 'require',
            'express|快递' => 'require',
            'type|类型' => 'require|in:0,1',
            'address|发货信息' => 'requireIf:type,0',
            'pay|支付密码' => 'requireIf:confirm,1',
//            'platform' =>
//            'file|导入文件' => 'requireIf:type,1|file',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        $member = parent::member();

        if (!empty($request->post('confirm')) && (md5($request->post('pay')) != $member['pay_pass'])) parent::ajax_exception(000, '支付密码输入错误');

        switch ($request->post('type')) {
            case '0':
                $address = self::address($request);
                break;
            case '1':
                $address = self::file($request);
                break;
            default:
                parent::ajax_exception(000, '下单类型出错');
                exit;
        }

        return $address;
    }

    /**
     * 输入收货地址
     *
     * @param Request $request
     * @return array
     */
    private function address(Request $request)
    {
        $class = new OrderFileClass($request->post('address'));

        $result = $class->file;

        if (!is_array($result)) parent::ajax_exception(000, $result);

        return $result;
    }

    /**
     * 导入收货地址
     *
     * @param Request $request
     * @return array
     */
    public function file(Request $request)
    {
        $filename = 'member_file_excel' . time();
        $url = 'uploads';

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
            } else if ($extension == 'csv') {

                $objReader = \PHPExcel_IOFactory::createReader('CSV');
                if (input('platform') == 'pinduoduo') {

                    $objPHPExcel = $objReader->setInputEncoding('UTF-8')->load($filename);
                } else {

                    $objPHPExcel = $objReader->setInputEncoding('GBK')->load($filename);
                }
            } else {

                unlink($filename);
                parent::ajax_exception(000, '请上传excel格式的文件!');
            }

            $excel_array = $objPHPExcel->getsheet(0)->toArray();   //转换为数组格式

            array_shift($excel_array);//去表头

            if (count($excel_array) > 500) parent::ajax_exception(000, '单次上传表单内数据不得超过500条');

            $files = new OrderFileClass($excel_array);

            $result = $files->file;

            unlink($filename);

            if (!is_array($result)) parent::ajax_exception(000, $result);

            return $result;
        }

        parent::ajax_exception(000, '上传失败!');
    }

    /**
     * 下单验证，并格式化收货地址，结束
     */


    /**
     * @param Request $request
     * @param $address
     * @return array
     */
    public function save(Request $request, $address)
    {
        $express_number = count($address);//快递数

        //商品验证
        $goods = new GoodsModel();
        $goods = $goods->where('status', '=', 'on')->where('id', '=', $request->post('goods'))->find();
        if (is_null($goods)) parent::ajax_exception(000, '该礼品已下架');
        if (SUBSTATION != '0') {

            $amount = new GoodsAmountModel();
            $a = $amount->where('goods_id', '=', $goods->id)->where('substation', '=', SUBSTATION)->find();
            if (!is_null($a)) $goods->amount = $a->amount;
        }

        //库存验证
        $number = $request->post('number');//每单数量
        if ($number > $goods['express_number']) parent::ajax_exception(000, '该礼品每单至多购买『' . $goods['express_number'] . '』件');
        $all = $number * $express_number;//总下单数
        $goods_number = $all;//发货数
        if ($all > $goods['stock']) parent::ajax_exception(000, '该商品库存不足（剩余：' . $goods['stock'] . '，本次下单：' . $all . '）');

        //发货店铺验证
        $store = new MemberStoreModel();
        $store = $store->where('member_id', '=', $this->member['id'])->where('id', '=', $request->post('store'))->find();
        if (is_null($store)) parent::ajax_exception(000, '发货店铺异常');

        //快递验证
        $express = new ExpressModel();
        $express = $express->where('id', '=', $request->post('express'))->find();
        if (is_null($express)) parent::ajax_exception(000, '快递信息异常');

        //会员等级验证
        $grade = new MemberGradeModel();
        $grade = $grade->where('id', '=', $this->member['grade_id'])->find();
        if (is_null($grade)) parent::ajax_exception(000, '会员等级异常');

        //快递费验证
        if ($grade['mode'] == 'on') {

            $amount = $grade['amount'];
        } else {

            $grade_express = new MemberGradeExpressModel();
            $grade_express = $grade_express->where('grade', '=', $grade['id'])->where('express', '=', $express['id'])->find();
            $amount = is_null($grade_express) ? config('young.default_express_amount') : $grade_express['amount'];
        }

        //余额验证
        $express_total = number_format($express_number * $amount, 2, '.', '');//总快递费
        $goods_total = number_format($goods_number * $goods['amount'], 2, '.', '');//总礼品费
        $total = $express_total + $goods_total;
        if ($total > $this->member['remind']) parent::ajax_exception(000, '您的余额不足，共需：' . $total . '，剩余：' . $this->member['remind']);

        //确认下单
        if (!$request->post('confirm')) {

            $result = [
                'address' => $address,//发货地址
                'total' => $total,//总价
                'goods_total' => $goods_total,//商品总价
                'goods_number' => $goods_number,//商品总数
                'goods_amount' => $goods['amount'],//商品单价
                'express_total' => $express_total,//快递总价
                'express_number' => $express_number,//快递总数
                'express_amount' => $amount,//快递单价
                'member_remind' => $this->member['remind'],//下单前余额
                'member_remind_now' => $this->member['remind'] - $total,//下单后余额
            ];

            return $result;
        }

        $insert_express = [];
        $date = date('Y-m-d H:i:s');
        $platform = config('member.store_platform');

        $m = $this->member;
        $p = $platform[$store['platform']];

        Db::startTrans();

        //正式下单
        $insert = new OrderModel();
        $insert->order_number = self::new_order();
        $insert->total = $total;
        $insert->total_express = $express_total;
        $insert->total_goods = $goods_total;
        $insert->express_amount = $amount;
        $insert->express_number = $express_number;
        $insert->goods_number = $goods_number;
        $insert->express_id = $express['id'];
        $insert->express_name = $express['name'];

        $insert->pay_status = '1';
        $insert->pay_date = $date;
        $insert->pay_type = '1';

        $insert->member_id = $m['id'];
        $insert->member_account = $m['account'];
        $insert->member_phone = $m['phone'];
        $insert->member_nickname = $m['nickname'];
        $insert->member_create = $m['created_at'];
        $insert->member_grade_id = $m['grade_id'];
        $insert->member_grade_name = $m['grade_name'];

        $insert->goods_class_id = $goods['goods_class_id'];
        $insert->goods_class_name = $goods['goods_class_name'];
        $insert->goods_id = $goods['id'];
        $insert->goods_name = $goods['name'];
        $insert->goods_code = $goods['code'];
        $insert->goods_describe = $goods['describe'];
        $insert->goods_amount = $goods['amount'];
        $insert->goods_sort = $goods['sort'];
        $insert->goods_status = $goods['status'];
        $insert->goods_cover = $goods['cover'];
        $insert->goods_location = $goods['location'];
        $insert->goods_stock = $goods['stock'];
        $insert->goods_created = $goods['created_at'];
        $insert->goods_weight = $goods['weight'];

        $insert->store_id = $store['id'];
        $insert->store_name = $store['name'];
        $insert->store_sort = $store['sort'];
        $insert->store_platform = $store['platform'];
        $insert->store_platform_name = $p;
        $insert->store_man = $store['man'];
        $insert->store_phone = $store['phone'];
        $insert->store_address = $store['address'];
        $insert->store_created = $store['created_at'];

        $insert->created_at = $date;
        $insert->substation = SUBSTATION;

        $insert->save();
        //正式下单结束

        //添加收货地址
        $content = [];
        foreach ($address as $v) {

            $name = $v['name'];//收货人
            $phone = $v['phone'];//收货人电话
            $address = $v['address'];//收货地址
            $pro = $v['pro'];//收货地址
            $city = $v['city'];//收货地址
            $area = $v['area'];//收货地址
            $add = $v['add'];//收货地址
            $content[] = $name . '#$' . $phone . '#$' . $address . '#$' . $pro . '#$' . $city . '#$' . $area . '#$' . $add;
        }
        $insert_express['order_id'] = $insert->id;
        $insert_express['content'] = implode('#$%', $content);
        $model = new OrderExpressModel();
        $model->save($insert_express);
        //收货地址添加接受

        //添加发货单
        self::insert_send($insert, $content);
        //添加发货单结束

        //扣除会员余额
        $member = new MemberModel();
        $member = $member->where('id', '=', $this->member['id'])->find();
        $member->remind -= $total;
        $member->save();
        //扣除会员余额结束

        //添加会员钱包记录
        $record = new MemberRecordModel();
        $record->member_id = $member->id;
        $record->account = $member->account;
        $record->nickname = $member->nickname;
        $record->content = '添加礼品订单（订单号：' . $insert->order_number . '）,扣除余额：' . $total;
        $record->remind = 0 - $total;
        $record->commis_now = $member->commis;
        $record->commis_all = $member->commis_all;
        $record->remind_now = $member->remind;
        $record->remind_all = $member->remind_all;
        $record->type = 50;
        $record->created_at = date('Y-m-d H:i:s');
        $record->save();

        //扣商品库存
        $goods->stock -= $goods_number;
        $goods->save();

        //扣商品库存记录
        $record = new GoodsRecordModel();
        $record->goods_id = $goods->id;
        $record->name = $goods->name;
        $record->code = $goods->code;
        $record->created_at = date('Y-m-d H:i:s');
        $record->stock = 0 - $goods_number;
        $record->content = '会员『' . $member['nickname'] . '』,添加了礼品订单（订单号：' . $insert->order_number . '），扣除商品『' . $goods->name . '(编号：' . $goods->code . ')』库存：' . $goods_number . '件';
        $record->stock_now = $goods->stock;
        $record->type = '2';

        $record->save();

        //添加会员钱包记录结束
        Db::commit();

        return [
            'order_number' => $insert->order_number
        ];
    }

    public function insert_send(Model $order, $express)
    {
        $insert_send = [];
        $o = $order->getData();
        $i = 0;
        $date = date('Y-m-d H:i:s');

        foreach ($express as $ke => $va) {

            list($name, $phone, $address, $pro, $city, $area, $add) = explode('#$', $va);

            $insert_send[$i]['send_order'] = $o['order_number'] . '-' . ($ke + 1);
            $insert_send[$i]['order_id'] = $o['id'];
            $insert_send[$i]['order_number'] = $o['order_number'];
            $insert_send[$i]['order_create'] = $o['created_at'];
            $insert_send[$i]['store'] = $o['store_name'];
            $insert_send[$i]['express'] = $o['express_name'];
            $insert_send[$i]['goods'] = $o['goods_name'];
            $insert_send[$i]['goods_number'] = $o['goods_number'];
            $insert_send[$i]['consignee'] = $name;
            $insert_send[$i]['phone'] = $phone;
            $insert_send[$i]['address'] = $address;
            $insert_send[$i]['pro'] = $pro;
            $insert_send[$i]['city'] = $city;
            $insert_send[$i]['area'] = $area;
            $insert_send[$i]['add'] = $add;
            $insert_send[$i]['created_at'] = $date;
            $i++;
        }
       /* $first = $insert_send[$i - 1];
        $insert_send = [];
        for ($i = 0; $i < 500; $i++) {
            $f = $first;
            $f['send_order'] = $first['send_order'] . $i;
            $insert_send[] = $f;
        }*/
        if (count($insert_send) > 0) {

            $model = new OrderSendModel();
            $model->insertAll($insert_send);
        }
    }

    //获取新的订单号
    public function new_order()
    {
        $key = date('Ymd');

        $number = new OrderModel();
        $number = $number->where('created_at', '>=', date('Y-m-d') . ' 00:00:00')->count();
        $number++;

        $len = strlen('20181127000001');

        for ($i = ($len - strlen($key) - strlen($number)); $i > 0; $i--) {

            $key .= '0';
        }

        $key .= $number;

        //验证订单号是否被占用
        $test = new OrderModel();
        $test = $test->where('order_number', '=', $key)->find();

        if (!is_null($test)) {

            return self::new_order();
        } else {

            return $key;
        }
    }


    public function order_back()
    {
        $id = input('id');

        $member = parent::member();

        $order = new OrderModel();

        $order = $order->where('id', '=', $id)
            ->where('member_id', '=', $member['id'])
            ->where('order_status', '=', '10')
            ->find();

        if (is_null($order)) parent::ajax_exception(000, '订单已锁定');

        Db::startTrans();

        $order->order_status = 30;
        $order->change_id = $member['id'];
        $order->change_nickname = $member['nickname'];
        $order->change_date = date('Y-m-d H:i:s');
        $order->save();

        $members = new MemberModel();
        $member = $members->where('id', '=', $member['id'])->find();
        if (!is_null($member)) {

            $member->remind += $order->total;
            $member->save();

            //添加会员钱包记录
            $record = new MemberRecordModel();
            $record->member_id = $member->id;
            $record->account = $member->account;
            $record->nickname = $member->nickname;
            $record->content = '取消礼品订单（订单号：' . $order->order_number . '）,返还余额：' . $order->total;
            $record->remind = $order->total;
            $record->commis_now = $member->commis;
            $record->commis_all = $member->commis_all;
            $record->remind_now = $member->remind;
            $record->remind_all = $member->remind_all;
            $record->type = 50;
            $record->created_at = date('Y-m-d H:i:s');
            $record->save();
        }

        $goods = new GoodsModel();
        $goods = $goods->where('id', '=', $order->goods_id)->find();
        if (!is_null($goods)) {

            //加商品库存
            $goods->stock += $order->goods_number;
            $goods->save();

            //加商品库存记录
            $record = new GoodsRecordModel();
            $record->goods_id = $goods->id;
            $record->name = $goods->name;
            $record->code = $goods->code;
            $record->created_at = date('Y-m-d H:i:s');
            $record->stock = $order->goods_number;
            $record->content = '会员『' . $member['nickname'] . '』,取消了礼品订单（订单号：' . $order->order_number . '），入库商品『' . $goods->name . '(编号：' . $goods->code . ')』库存：' . $order->goods_number . '件';
            $record->stock_now = $goods->stock;
            $record->type = '1';
            $record->save();
        }

        Db::commit();
    }

    public function order_info($id)
    {
        $model = new OrderModel();

        $model = $model->where('id', '=', $id)->find();

        if (is_null($model)) parent::redirect_exception('/', '订单不存在');

        return $model->getData();
    }

    public function order_note(Request $request)
    {
        $id = $request->get('id');
        $note = $request->get('value');
        if (strlen($note) > 255) parent::ajax_exception(000, '备注太长了~~');
        $member = parent::member();
        $order = new OrderModel();
        $order->where('id', '=', $id)->where('member_id', '=', $member['id'])->update(['note' => $note]);
    }
}