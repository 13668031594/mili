<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/6/13
 * Time: 下午6:09
 */

namespace app\youyunbao\controller;

use app\index\controller\IndexController;
use app\member\model\MemberModel;
use app\recharge\model\RechargeModel;
use app\recharge\model\RechargeOrderModel;
use app\Youyunbao\model\YouyunbaoOrderModel;
use app\youyunbao\model\YouyunbaoPayModel;
use classes\recharge\RechargeClass;
use classes\vendor\StorageClass;
use classes\vendor\Youyunbao\YouyunbaoClass;
use think\Db;
use think\Request;

class YouyunbaoController extends IndexController
{
    public function alipayh5()
    {
        $class = new YouyunbaoClass();

        $sdata = $class->alipayh5();

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

        $storage = new StorageClass('youyunbao.txt');
        $storage->save(json_encode($post));

        $model = new YouyunbaoPayModel();
        $model->ddh = $post['ddh'];
        $model->money = $post['money'];
        $model->name = $post['name'];
        $model->key = $post['key'];
        $model->paytime = $post['paytime'];
        $model->lb = $post['lb'];
        $model->type = $post['type'];
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

        $order_model = new YouyunbaoOrderModel();
        $order_model->where('datas', '=', $model->name)->find();

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
        $recharge->save();

        //完成订单结束后的操作
        $class = new RechargeClass();
        $class->integralAdd($order->getData());
        $class->levelUp($order->getData());

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