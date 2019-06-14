<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/6/14
 * Time: 下午6:07
 */

namespace classes\substation;

use app\order\model\OrderModel;
use app\Substation\model\SubstationModel;
use app\substation\model\SubstationRechargeModel;
use app\substation\model\SubstationRechargeOrderModel;
use app\substation\model\SubstationRecordModel;
use classes\AdminClass;
use classes\member\MemberStoreClass;
use classes\system\SystemClass;
use classes\vendor\StorageClass;
use think\Request;

class SubstationRechargeClass extends AdminClass
{
    public function bank()
    {
        $substation = new SubstationModel();
        $substation = $substation->find(SUBSTATION);

        $pid = $substation->pid;

        $name = empty($pid) ? 'bankSetting.txt' : 'bankSetting_' . $pid . '.txt';

        $storage = new StorageClass($name);

        //读取设定文件
        $set = $storage->get();

        //获取默认配置
        $result = self::defaults();

        //设定文件存在，修改返回配置
        if (!is_array($set)) {

            //格式化配置信息
            $set = json_decode($set, true);

            //循环设定数据
            foreach ($result as $k => &$v) {

                //设定文件中有的设定，修改之
                if (isset($set[$k])) $v = $set[$k];
            }
        }

        $result['balance'] = $substation->balance;

        //返回设定文件
        return $result;
    }

    //默认数据
    private function defaults()
    {
        return [
            'file' => '收款设置',
        ];
    }

    public function order()
    {

        $order = new SubstationRechargeOrderModel();
        $order = $order->where('substation', '=', SUBSTATION)->where('status', '=', 0)->find();

        if (is_null($order)) {

            $order = new SubstationRechargeOrderModel();
            $order->created_at = date('Y-m-d H:i:s');
            $order->substation = SUBSTATION;
            $order->save();
            $order->order_number = 'S' . (37957 + $order->id);
            $order->save();
        }

        return $order->order_number;
    }

    public function validator_recharge(Request $request)
    {
        $rule = [
            'total|充值金额' => 'require|integer',
            'order|单号' => 'require',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        $set = self::store_set();

        $total = $request->post('total');

        if ($total < $set['rechargeBase']) parent::ajax_exception(000, '充值金额不得小于：' . $set['rechargeBase']);

        if ($total % $set['rechargeTimes']) parent::ajax_exception(000, '充值金额必须为：' . $set['rechargeTimes'] . '的正整数倍');

        $test = new SubstationRechargeModel();
        $test = $test->where('substation', '=', SUBSTATION)->where('status', '=', 0)->find();
        if (!is_null($test)) parent::ajax_exception(000, '您还有未处理的充值订单');

        $order_number = $request->post('order');
        $order = new SubstationRechargeOrderModel();
        $order = $order->where('order_number', '=', $order_number)->find();
        if (!is_null($order)) {

            $order->status = 1;
            $order->save();
        }

        $test = new SubstationRechargeModel();
        $test = $test->where('order_number', '=', $order_number)->find();
        if (!is_null($test)) {

            parent::ajax_exception(000, '请刷新重试');
        }
    }

    public function recharge(Request $request)
    {
        $master = parent::master();

        $recharge = new SubstationRechargeModel();
        $recharge->order_number = $request->post('order');
        $recharge->total = $request->post('total');
        $recharge->remind = $recharge->total;
        $recharge->master_nickname = $master['nickname'];
        $recharge->master_id = $master['id'];
        $recharge->created_at = date('Y-m-d H:i:s');
        $recharge->updated_at = date('Y-m-d H:i:s');
        $recharge->substation = SUBSTATION;
        $recharge->save();
    }

    public function store_set()
    {
        $substation = new SubstationModel();
        $substation = $substation->find(SUBSTATION);

        $pid = $substation->pid;

        $name = empty($pid) ? 'sysSetting.txt' : 'sysSetting_' . $pid . '.txt';

        $store = new StorageClass($name);

        //读取设定文件
        $set = $store->get();

        //获取默认配置
        $result = self::defaults2();

        //设定文件存在，修改返回配置
        if (!is_array($set)) {

            //格式化配置信息
            $set = json_decode($set, true);

            //循环设定数据
            foreach ($result as $k => &$v) {

                //设定文件中有的设定，修改之
                if (isset($set[$k])) $v = $set[$k];
            }
        }

        //返回设定文件
        return $result;
    }

    //默认数据
    private function defaults2()
    {
        return [
            'webName' => '米礼网',
            'webTitle' => '米礼网',
            'webKeyword' => '米礼网',
            'webDesc' => '米礼网',
            'webSwitch' => 'on',
            'webCloseReason' => '网站维护中',
            'fwb-content' => '请谨慎下单',
            'logo' => config('young.image_not_found'),
            'webCopyright' => '版权',
            'userCommiss' => '100',
            'rechargeBase' => '100',
            'rechargeTimes' => '10',
            'rechargeSwitch' => 'on',
            'rechargeGradeSwitch' => 'on',
            'login' => config('young.image_not_found'),
            'loginUrl' => 'http://',
            'reg' => config('young.image_not_found'),
            'regUrl' => 'http://',
            'goods_number' => '每单至多购买{$number}件该商品',
            'self_default' => '请完善个人资料',
            'withdraw' => '将在24小时内处理您的提现申请',
            'loginReason' => '我们提供赠品采购、发货、一站式服务。',
            'qq' => '',
        ];
    }

    public function recharge_index(Request $request)
    {
        $where = [
            ['substation', '=', SUBSTATION]
        ];

        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');
        $status = $request->get('status');
        $keyword = $request->get('keyword');
        $keywordType = $request->get('keywordType');

        if (!empty($startTime)) {
            $where[] = ['created_at', '>=', $startTime];
        }
        if (!empty($endTime)) {
            $where[] = ['created_at', '<', $endTime];
        }
        if (!empty($keyword)) {
            switch ($keywordType) {
                case '1':
                    $where[] = ['order_number', 'like', '%' . $keyword . '%'];
                    break;
                default:
                    break;
            }
        }
        if (!empty($status) || ($status == '0')) {
            $where[] = ['status', '=', $status];
        }

        $model = new SubstationRechargeModel();

        return parent::page($model, ['where' => $where]);
    }

    public function index(Request $request)
    {
        $substation = new SubstationModel();
//        $ps = $substation->find(SUBSTATION);

        $sbs = $substation->where('pid', '=', SUBSTATION)->column('id');
        $whereIn = [
            'substation' => $sbs
        ];
//        dd($sbs);

        $where = [
//            ['substation','=', SUBSTATION]
        ];

        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');
        $status = $request->get('status');
        $keyword = $request->get('keyword');
        $keywordType = $request->get('keywordType');

        if (!empty($startTime)) {
            $where[] = ['created_at', '>=', $startTime];
        }
        if (!empty($endTime)) {
            $where[] = ['created_at', '<', $endTime];
        }
        if (!empty($keyword)) {
            switch ($keywordType) {
                case '1':
                    $where[] = ['order_number', 'like', '%' . $keyword . '%'];
                    break;
                default:
                    break;
            }
        }
        if (!empty($status) || ($status == '0')) {
            $where[] = ['status', '=', $status];
        }

        $model = new SubstationRechargeModel();

        return parent::page($model, ['where' => $where, 'whereIn' => $whereIn]);
    }

    public function status(Request $request)
    {

        $id = $request->get('id');

        $model = new SubstationRechargeModel();

        //订单获取
        $order = $model->where('id', '=', $id)->find();

        //获取成功
        if (is_null($order)) parent::ajax_exception(0, '订单不存在');

        //未锁定
        if ($order->status != '0') parent::ajax_exception(0, '订单已锁定');

        //新状态获取
        $status = input('value');

        //合法的状态码
        $array = [1, 3];

        //状态码合法
        if (!in_array($status, $array)) parent::ajax_exception(0, '状态错误');

        //获取管理员
        $master = parent::master();

        //修改订单状态
        $order->status = $status;
        $order->change_id = $master['id'];
        $order->change_nickname = $master['nickname'];
        $order->change_date = date('Y-m-d H:i:s');
        $order->save();

        //状态为处理，发放积分
        if ($status == '1') {

            //余额添加
            $substation = new SubstationModel();
            $substation = $substation->find($order->substation);
            if (is_null($substation)) return;
            $substation->balance += $order->remind;
            $substation->save();

            $date = date('Y-m-d H:i:s');

            //余额记录
            $record = new SubstationRecordModel();
            $record->substation = $order->substation;
            $record->balance = $order->remind;
            $record->balance_now = $substation->balance;
            $record->type = 10;
            $record->content = '余额充值成功，余额增加：' . $order->remind;
            $record->other = '';
            $record->created_at = $date;
            $record->save();

            $cost = 0;//扣除余额
            $num = 0;//订单数量

            $order_model = new OrderModel();
            $orders = $order_model->where('substation', '=', $substation->id)
                ->where('substation_pay', '=', '0')
                ->order('created_at asc')
                ->column('id,express_cost_all,goods_cost_all');

            if (count($orders) <= 0) return;

            foreach ($orders as $v) {

                $all = $v['express_cost_all'] + $v['goods_cost_all'];

                if ($all > $substation->balance) continue;

                $cost += $all;
                $num += 1;

                $o = $order_model->find($v['id']);
                $o->substation_pay = 1;
                $o->save();
            }

            if ($cost > 0) {

                $substation->balance -= $cost;
                $substation->save();

                $record = new SubstationRecordModel();
                $record->substation = $order->substation;
                $record->balance = -$cost;
                $record->balance_now = $substation->balance;
                $record->type = 20;
                $record->content = '订单扣款，合计：' . $cost . '，涉及订单：' . $num . '条';
                $record->other = '';
                $record->created_at = $date;
                $record->save();
            }
        }
    }

    public function record_array()
    {
        return config('member.sub_record');
    }

    public function record(Request $request)
    {
        $where = [];

        $where['substation'] = ['=', SUBSTATION];

        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');

        if (!empty($startTime)) {
            $where[] = ['created_at', '>=', $startTime];
        }
        if (!empty($endTime)) {
            $where[] = ['created_at', '<', $endTime];
        }

        $model = new SubstationRecordModel();

        return parent::page($model, ['where' => $where,'order_name' => 'id','order_type'=> 'desc']);
    }
}