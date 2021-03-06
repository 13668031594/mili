<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/27
 * Time: 下午2:18
 */

namespace classes\index;


use app\express\model\ExpressModel;
use app\goods\model\GoodsContentModel;
use app\goods\model\GoodsModel;
use app\goods\model\GoodsRecordModel;
use app\member\model\ExpressLevelAmountModel;
use app\member\model\MemberGradeModel;
use app\member\model\MemberModel;
use app\member\model\MemberRecordModel;
use app\member\model\MemberStoreModel;
use app\order\model\OrderExpressModel;
use app\order\model\OrderModel;
use app\order\model\OrderSendModel;
use app\order\model\OrderSubstationProfitModel;
use app\substation\model\SubstationModel;
use app\substation\model\SubstationRecordModel;
use classes\substation\SubstationLevelClass;
use classes\vendor\ExpressAmountClass;
use classes\vendor\GoodsAmountClass;
use classes\vendor\GradeExpressAmountClass;
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

        if (SUBSTATION != '0') {

            $class = new GoodsAmountClass();
            $amount = $class->amount($id, $result['amount'], $result['cost'], $result['protect']);

            $result['amount'] = $amount['amount'];
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
    public function express($goods_code)
    {
        $model = new ExpressModel();

        $express = $model->where('disabled', '=', 'on')->order('sort', 'desc')->column('id,name,platform,goods_code');

        $member = parent::member();

        $express_self = new SubstationLevelClass();
        $express_self = $express_self->self_express();

        $amount_class = new GradeExpressAmountClass();

        $grade = new MemberGradeModel();
        $grade = $grade->where('id', '=', $member['grade_id'])->find();

        $platform = array_keys(config('member.store_platform'));

        $result = [];
        foreach ($platform as $v) $result[$v] = [];

        $code_express = [];
        foreach ($express as $k => $v) {

            if (!empty($v['goods_code'])) {
                $str = str_replace(" ", '', $v['goods_code']);//去空格
                $code = explode("\r\n", $str);//按行分组
                $codes = [];
                foreach ($code as $va) {

                    $va = preg_replace("/(，)/", ',', $va);//去逗号
                    $codes = array_merge($codes, explode(',', $va));
                }

                if (!in_array($goods_code, $codes)) continue;
                else $code_express[] = $v['id'];
            }

            if ($grade->mode == 'on') {

                $amount = $amount_class->amount(0, $member['grade_id'], $express_self[0]['protect']);
            } else {

                $amount = $amount_class->amount($v['id'], $member['grade_id'], $express_self[$v['id']]['protect']);
            }

            $result[$v['platform']][$v['id']] = [
                'name' => $v['name'],
                'amount' => $amount,
            ];
        }

        if (!empty($code_express)) foreach ($platform as $v) foreach ($result[$v] as $key => $val) {

            if (!in_array($key, $code_express)) unset($result[$v][$key]);
        }

        return $result;
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
            'express' . $request->post('express') . '|快递' => 'require',
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

        if (count($address) <= 0)parent::ajax_exception(000,'没有符合条件的收货人');

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

            $amount = new GoodsAmountClass();
            $a = $amount->amount($goods->id, $goods->amount, $goods->cost, $goods->protect);
            $goods->amount = $a['amount'];
            $goods->cost = $a['cost'];
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
        $express = $express->where('id', '=', $request->post('express' . $request->post('express')))->find();
        if (is_null($express)) parent::ajax_exception(000, '快递信息异常');

        //会员等级验证
        $grade = new MemberGradeModel();
        $grade = $grade->where('id', '=', $this->member['grade_id'])->find();
        if (is_null($grade)) parent::ajax_exception(000, '会员等级异常');

        $express_self = new SubstationLevelClass();
        $express_self = $express_self->self_express();
        $b = new GradeExpressAmountClass();
        //快递费验证
        if ($grade['mode'] == 'on') {

            $b = $b->amount(0, $grade->id, $express_self[0]['protect']);
        } else {

            $b = $b->amount($express->id, $grade->id, $express_self[$express->id]['protect']);
        }
        $amount = $b;
        $cost = $express_self[$express->id]['cost'];
        $protect = $express_self[$express->id]['protect'];


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

        //新增成本价
        $insert->goods_cost = $goods['cost'];
        $insert->goods_cost_all = $goods['cost'] * $goods_number;
        $insert->express_cost = $cost;
        $insert->express_cost_all = $cost * $express_number;

        $insert->substation_pay = 0;
        $insert->save();
        //正式下单结束

        if (SUBSTATION != 0) {

            //分站扣款
            $substation = new SubstationModel();
            $substation = $substation->find(SUBSTATION);

            $all = $insert->goods_cost_all + $insert->express_cost_all;

            if ($all <= $substation->balance) {

                $substation->balance -= $all;
                $substation->save();

                $record = new SubstationRecordModel();
                $record->substation = SUBSTATION;
                $record->balance = -$all;
                $record->balance_now = $substation->balance;
                $record->type = 20;
                $record->content = '订单扣款，合计：' . $all . '，订单号：' . $insert->order_number;
                $record->other = '';
                $record->created_at = $date;
                $record->save();

                $insert->substation_pay = 1;
                $insert->save();
            }
        } else {

            $insert->substation_pay = 1;
            $insert->save();
        }


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

        //添加站点收益信息
        self::order_profit($insert);

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

        return self::test_number($key);
    }

    private function test_number($key)
    {
        //验证订单号是否被占用
        $test = new OrderModel();
        $test = $test->where('order_number', '=', $key)->find();

        if (!is_null($test)) {

            return self::test_number(($key + 1));
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

    public function order_profit($order)
    {
        //若是主站下单，无需添加成本
        if ($order->substation == 0) return;

        //列出查询需要的信息
        $goods_id = $order->goods_id;
        $express_id = $order->express_id;

        //列出本单信息
        $order_id = $order->id;//订单id
        $sub_id = $order->substation;//下单站点id
        $goods_number = $order->goods_number;//商品总数
        $express_number = $order->express_number;//快递总数
        $order_cost_goods = $order->goods_cost_all;//商品总成本
        $order_cost_express = $order->express_cost_all;//快递总成本
        $order_cost_all = $order_cost_goods + $order_cost_express;//订单总成本

        //初始化要用到的model
        $sub_model = new SubstationModel();//分站模型
        $goods_model = new GoodsModel();//商品模型
        $express_model = new ExpressModel();//快递模型

        //获取商品信息
        $goods = $goods_model->find($goods_id);
        if (is_null($goods)) return;

        //获取快递信息
        $express = $express_model->find($express_id);
        if (is_null($express)) return;

        //寻找上级站点
        $p_sub = $sub_model->find($sub_id);//找到自己的站点信息
        if (is_null($p_sub)) return;//没有找到分站信息
        $pid = $p_sub->pid;//赋值上级站点id

        //初始化要用的class
        $goods_amount_class = new GoodsAmountClass($pid);
        $express_amount_class = new ExpressAmountClass($pid);

        //获取上级站的成本信息
        $goods_amount = $goods_amount_class->amount($goods->id, $goods->amount, $goods->cost, $goods->protect);
        $express_amount = $express_amount_class->amount($express->id, $express->cost, $express->protect);

        //计算上级站成本与收益情况
        $goods_cost = $goods_amount['cost'] * $goods_number;
        $express_cost = $express_amount['cost'] * $express_number;
        $goods_profit = $order->goods_cost - $goods_amount['cost'];
        $express_profit = $order->express_cost - $express_amount['cost'];
        $goods_profit_all = $order_cost_goods - $goods_cost;
        $express_profit_all = $order_cost_express - $express_cost;

        $profit = new OrderSubstationProfitModel();
        $profit->order_id = $order_id; //订单id'
        $profit->goods_number = $goods_number; //订单商品总数
        $profit->express_number = $express_number; //订单快递总数
        $profit->order_sub = $sub_id; //订单来源分站id'
        $profit->child_sub = $sub_id; //下级分站id'
        $profit->my_sub = $pid; //收益分站id'
        $profit->order_cost_all = $order_cost_all; //订单总成本
        $profit->child_cost_all = $order_cost_all; //下级分站总成本价
        $profit->my_cost_all = $goods_cost + $express_cost; //收益分站总成本价
        $profit->profit_all = $goods_profit_all + $express_profit_all; //收益分站总收益
        $profit->order_cost_goods = $order->goods_cost; //订单商品成本价格
        $profit->child_cost_goods = $order->goods_cost; //下级分站商品成本价格
        $profit->my_cost_goods = $goods_amount['cost']; //收益分站商品成本价格
        $profit->profit_goods = $goods_profit; //收益分站商品收益，单个
        $profit->profit_goods_all = $goods_profit_all; //收益分站商品收益，总计
        $profit->order_cost_express = $order->express_cost; //订单快递成本价格
        $profit->child_cost_express = $order->express_cost; //下级分站快递成本价格
        $profit->my_cost_express = $express_amount['cost']; //收益分站快递成本价格
        $profit->profit_express = $express_profit;//收益分站快递收入，单个
        $profit->profit_express_all = $express_profit_all;//收益分站快递收入，总计
        $profit->created_at = $order->created_at;//创建时间'
        $profit->save();

        //若是分分站下单，继续添加主站利润
        if ($p_sub->id == 0)return;

        //初始化要用的class
        $goods_amount_class = new GoodsAmountClass(0);
        $express_amount_class = new ExpressAmountClass(0);

        //获取上级站的成本信息
        $goods_amount_2 = $goods_amount_class->amount($goods->id, $goods->amount, $goods->cost, $goods->protect);
        $express_amount_2 = $express_amount_class->amount($express->id, $express->cost, $express->protect);

        //计算上级站成本与收益情况
        $goods_cost_2 = $goods_amount_2['cost'] * $goods_number;
        $express_cost_2 = $express_amount_2['cost'] * $express_number;
        $goods_profit_2 = $goods_amount['cost'] - $goods_amount_2['cost'];
        $express_profit_2 = $express_amount['cost'] - $express_amount_2['cost'];
        $goods_profit_all_2 = $goods_cost - $goods_cost_2;
        $express_profit_all_2 = $express_cost - $express_cost_2;

        $profit_2 = new OrderSubstationProfitModel();
        $profit_2->order_id = $order_id; //订单id'
        $profit_2->goods_number = $goods_number; //订单商品总数
        $profit_2->express_number = $express_number; //订单快递总数
        $profit_2->order_sub = $sub_id; //订单来源分站id'
        $profit_2->child_sub = $profit->my_sub; //下级分站id'
        $profit_2->my_sub = 0; //收益分站id'
        $profit_2->order_cost_all = $order_cost_all; //订单总成本
        $profit_2->child_cost_all = $profit->my_cost_all; //下级分站总成本价
        $profit_2->my_cost_all = $goods_cost_2 + $express_cost_2; //收益分站总成本价
        $profit_2->profit_all = $goods_profit_all_2 + $express_profit_all_2; //收益分站总收益
        $profit_2->order_cost_goods = $order->goods_cost; //订单商品成本价格
        $profit_2->child_cost_goods = $profit->my_cost_goods; //下级分站商品成本价格
        $profit_2->my_cost_goods = $goods_amount_2['cost']; //收益分站商品成本价格
        $profit_2->profit_goods = $goods_profit_2; //收益分站商品收益，单个
        $profit_2->profit_goods_all = $goods_profit_all_2; //收益分站商品收益，总计
        $profit_2->order_cost_express = $order->express_cost; //订单快递成本价格
        $profit_2->child_cost_express = $profit->my_cost_express; //下级分站快递成本价格
        $profit_2->my_cost_express = $express_amount_2['cost']; //收益分站快递成本价格
        $profit_2->profit_express = $express_profit_2;//收益分站快递收入，单个
        $profit_2->profit_express_all = $express_profit_all_2;//收益分站快递收入，总计
        $profit_2->created_at = $order->created_at;//创建时间'
        $profit_2->save();
    }
}