<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/6/13
 * Time: 下午6:09
 */

namespace app\youyunbao\controller;

use app\index\controller\IndexController;
use app\master\model\MasterModel;
use app\member\model\MemberModel;
use app\order\model\OrderModel;
use app\recharge\model\RechargeModel;
use app\recharge\model\RechargeOrderModel;
use app\substation\model\SubstationModel;
use app\substation\model\SubstationRechargeModel;
use app\substation\model\SubstationRechargeOrderModel;
use app\substation\model\SubstationRecordModel;
use app\youyunbao\model\YouyunbaoOrderModel;
use app\youyunbao\model\YouyunbaoPayModel;
use classes\recharge\RechargeClass;
use classes\vendor\Youyunbao\YouyunbaoClass;
use think\Db;
use think\Request;

class YouyunbaoController extends IndexController
{
    public function alipayh5()
    {
        $class = new YouyunbaoClass();

        $sdata = $class->alipayh5();
//dd($sdata);
        return parent::view('alipayh5', ['sdata' => $sdata]);
    }

    public function orderajax()
    {
        $class = new YouyunbaoClass();

        $class->orderajax();

        exit;
    }

    public function youyunbao_notify(Request $request)
    {
        Db::startTrans();

        $post = $request->post();

        $class = new YouyunbaoClass();
        if ($post['key'] != $class->config->config['appkey']) {

            self::notify_ok();
        }

        //添加回调订单
        $model = new YouyunbaoPayModel();
        $model->ddh = $post['ddh'];
        $model->money = $post['money'];
        $model->name = $post['name'];
        $model->key = $post['key'];
        $model->paytime = $post['paytime'];
        $model->lb = $post['lb'];
        $model->type = $post['type'];
        $model->substation = SUBSTATION;
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

        //寻找支付订单
        $order_model = new YouyunbaoOrderModel();
        $order_model = $order_model->where('datas', '=', $model->name)->find();

        if (is_null($order_model)) {

            $model->notify = '未找到相关充值订单';
            $model->save();
            self::notify_ok();
        };

        if ($order_model->orderstatus == 1) {

            $model->notify = '相关充值订单已经支付过了';
            $model->save();
            self::notify_ok();
        };

        //赋值支付id
        $order_model->pay_id = $model->id;

        //根据订单号，分类回调路径
        list($type, $time, $id) = explode('_', $model->name);

        if ($type == 'u') self::user_notify($model, $order_model);
        if ($type == 's') self::substation_notify($model, $order_model);
    }

    public function user_notify(YouyunbaoPayModel $model, YouyunbaoOrderModel $order_model)
    {
        //寻找会员
        $member_id = $order_model->member_id;
        $member = new MemberModel();
        $member = $member->find($member_id);
        if (is_null($member)) {

            $order_model->save();

            $model->notify = '下单用户未找到';
            $model->save();
            self::notify_ok();
        }

        //添加订单号
        $order = new RechargeOrderModel();
        $order->member_id = $member_id;
        $order->created_at = date('Y-m-d H:i:s');
        $order->substation = $member->substation;
        $order->save();
        $order->order_number = 'R' . (37957 + $order->id);
        $order->save();

        //赋值支付订单号并保存
        $order_model->recharge_order = $order->order_number;
        $order_model->save();

        //添加充值订单
        $recharge = new RechargeModel();
        $recharge->order_number = $order->order_number;
        $recharge->total = $model->money;
        $recharge->remind = $recharge->total;
        $recharge->member_id = $member['id'];
        $recharge->member_account = $member['account'];
        $recharge->member_phone = $member['phone'];
        $recharge->member_nickname = $member['nickname'];
        $recharge->member_create = $member['created_at'];
        $recharge->created_at = date('Y-m-d H:i:s');
        $recharge->updated_at = date('Y-m-d H:i:s');
        $recharge->status = 1;
        $recharge->change_id = 0;
        $recharge->change_nickname = '自动到账';
        $recharge->change_date = date('Y-m-d H:i:s');
        $recharge->substation = SUBSTATION;
        $recharge->from = $order_model->from;
        $recharge->save();

        //完成订单结束后的操作
        $class = new RechargeClass();
        $class->integralAdd($recharge->getData());
        $class->levelUp($recharge->getData());

        $model->notify = '充值成功';
        $model->save();

        self::notify_ok();
    }

    public function substation_notify(YouyunbaoPayModel $model, YouyunbaoOrderModel $order_model)
    {
        $date = date('Y-m-d H:i:s');

        $master = new MasterModel();
        $master = $master->find($order_model->member_id);
        if (is_null($master)) {

            $master['id'] = $order_model->member_id;
            $master['nickname'] = '未找到';
        }

        $substation = new SubstationModel();
        $substation = $substation->find($order_model->substation);
        if (is_null($substation)) {

            $order_model->save();

            $model->notify = '下单分站未找到';
            $model->save();
            self::notify_ok();
        }

        $recharge = new SubstationRechargeOrderModel();
        $recharge->created_at = $date;
        $recharge->substation = $substation->id;
        $recharge->save();
        $recharge->order_number = 'S' . (37957 + $recharge->id);
        $recharge->save();

        $order = new SubstationRechargeModel();
        $order->order_number = $recharge->order_number;
        $order->total = $model->money;
        $order->remind = $order->total;
        $order->master_nickname = $master['nickname'];
        $order->master_id = $master['id'];
        $order->created_at = $date;
        $order->updated_at = $date;
        $order->substation = $substation->id;
        $order->status = 1;
        $order->change_id = 0;
        $order->change_nickname = '自动到账';
        $order->change_date = $date;
        $recharge->from = $order_model->from;
        $order->save();

        //状态为处理，发放积分

        //余额添加
        $substation->balance += $order->remind;
        $substation->save();

        //余额记录
        $record = new SubstationRecordModel();
        $record->substation = $order->substation;
        $record->balance = $order->remind;
        $record->balance_now = $substation->balance;
        $record->type = 10;
        $record->content = '余额自主充值成功，余额增加：' . $order->remind;
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

        if (count($orders) > 0) {

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

        $model->notify = '充值成功';
        $model->save();

        self::notify_ok();
    }

    public function notify_ok()
    {
        Db::commit();
        exit('ok');
    }
}