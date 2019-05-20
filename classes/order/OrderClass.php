<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/25
 * Time: 下午2:14
 */

namespace classes\order;

use app\goods\model\GoodsModel;
use app\goods\model\GoodsRecordModel;
use app\member\model\MemberModel;
use app\member\model\MemberRecordModel;
use app\order\model\OrderExpressModel;
use app\order\model\OrderModel;
use app\order\model\OrderSendModel;
use classes\AdminClass;
use classes\system\SystemClass;
use classes\vendor\JushuitanClass;
use classes\vendor\SmsClass;
use think\Db;
use think\Request;

class OrderClass extends AdminClass
{
    public $model;
    public $send;
    public $express;

    public function __construct()
    {
        $this->model = new OrderModel();
        $this->send = new OrderSendModel();
        $this->express = new OrderExpressModel();
    }

    //列表
    public function index()
    {
        $keywordType = input('keywordType');
        $keyword = input('keyword');

        $where = [
            //['substation','=', SUBSTATION]

        ];

        if (!empty($keyword)) switch ($keywordType) {
            case '1':
                $where[] = ['member_account|member_phone', 'like', '%' . $keyword . '%'];
                break;
            case '0':
                $where[] = ['order_number', 'like', '%' . $keyword . '%'];
                break;
            default:
                break;
        }

        return parent::page($this->model, ['where' => $where, 'substation' => '1',]);
    }

    //详情
    public function edit($id)
    {
        $model = $this->model->where('id', '=', $id)->find();

        if (is_null($model)) parent::redirect_exception('/order/index', '订单不存在');

        return $model->getData();
    }

    //删除
    public function delete($id)
    {
        $delete_ids = $this->model->whereIn('id', $id)->column('id');
        $this->model->whereIn('id', $delete_ids)->delete();
        $this->send->whereIn('order_id', $delete_ids)->delete();
        $this->express->whereIn('order_id', $delete_ids)->delete();
    }

    //付款
    public function pay($id)
    {
        $order = $this->model->where('id', '=', $id)->find();

        if (is_null($order)) parent::ajax_exception(0, '订单不存在');
        if ($order->pay_status == '1') parent::ajax_exception(0, '请勿重复付款');

        $order->pay_status = '1';
        $order->pay_type = '2';
        $order->pay_date = date('Y-m-d H:i:s');
        $order->save();
    }

    //快递清单
    public function express($order)
    {
        if ($order['order_status'] == '20') return [];

        $result = $this->express->where('order_id', '=', $order['id'])->find();
        if (is_null($result)) return [];

        $express = [];
        foreach (explode('#$%', $result['content']) as $k => $v) {

            list($express[$k]['name'], $express[$k]['phone'], $express[$k]['address']) = explode('#$', $v);
        }

        return $express;
    }

    public function sends($order)
    {
        if ($order['order_status'] == '20') return [];

        $result = $this->send->where('order_id', '=', $order['id'])->column('*');
        if (empty($result)) return [];

        return $result;
    }

    //发货
    public function send(Request $request)
    {
        //快递号基础验证
        $send = $request->post('sendNo');
        if (!is_array($send)) parent::ajax_exception(000, '请刷新重试');
        if (in_array(null, $send) || in_array('', $send)) parent::ajax_exception(000, '请正确填写所有快递单号');
        $sends = array_unique($send);//去重
        if (count($sends) != count($send)) parent::ajax_exception(000, '快递单号重复');

        //订单状态验证
        $id = $request->post('id');
        $order = $this->model->where('id', '=', $id)->find();
        if (is_null($order)) parent::ajax_exception(000, '订单不存在');
        if ($order->order_status > '15') parent::ajax_exception(000, '订单已锁定');

        //与发货单简单验证
        $express = self::express($order);
        if (empty($express)) parent::ajax_exception(000, '未找到发货清单');
        if (count($express) != count($sends)) parent::ajax_exception(000, '快递数量错误');

        //添加发货单号
        $insert = [];
        $date = date('Y-m-d H:i:s');
        foreach ($express as $k => $v) {

            $insert[$k]['send_create'] = $date;
            $insert[$k]['send_order'] = $order->order_number . '-' . ($k + 1);
            $insert[$k]['order_id'] = $order->id;
            $insert[$k]['order_number'] = $order->order_number;
            $insert[$k]['order_create'] = $order->created_at;
            $insert[$k]['store'] = $order->store_name;
            $insert[$k]['express'] = $order->express_name;
            $insert[$k]['express_no'] = $sends[$k];
            $insert[$k]['goods'] = $order->goods_name;
            $insert[$k]['goods_number'] = $order->goods_number;
            $insert[$k]['consignee'] = $v['name'];
            $insert[$k]['phone'] = $v['phone'];
            $insert[$k]['address'] = $v['address'];
            $insert[$k]['created_at'] = $order->created_at;
        }

        //更新数据库
        if (count($insert) > 0) {

            //获取管理员信息
            $master = parent::master();

            //新增发货列表
            $this->send->insertAll($insert);

            //更新订单信息
            $order->order_status = 20;
            $order->change_id = $master['id'];
            $order->change_nickname = $master['nickname'];
            $order->change_date = $date;
            $order->save();

            $setting = new SystemClass();
            $set = $setting->index();
            $class = new SmsClass();
            $class->TemplateParam = [
                'username' => $order->member_nickname,
                'order' => $order->order_number,
                'web' => $set['webName']
            ];
            $class->sendSms($order->member_phone, '11111', 'SMS_151996093');
//            $class->sendSms('13608302076', '11111', 'SMS_151996093');
        }
    }

    //已有发货单，发货
    public function send2(Request $request)
    {
        //快递号基础验证
        $send = $request->post('sendsNo');
        if (is_null($send)) return false;//没有数据
        if (!is_array($send)) parent::ajax_exception(000, '请刷新重试');
        if (in_array(null, $send) || in_array('', $send)) parent::ajax_exception(000, '请正确填写所有快递单号');
        $sends = array_unique($send);//去重
        if (count($sends) != count($send)) parent::ajax_exception(000, '快递单号重复');

        //订单状态验证
        $id = $request->post('id');
        $order = $this->model->where('id', '=', $id)->find();
        if (is_null($order)) parent::ajax_exception(000, '订单不存在');
        if ($order->order_status > '15') parent::ajax_exception(000, '订单已锁定');

        //与发货单简单验证
        $express = self::sends($order);
        if (empty($express)) parent::ajax_exception(000, '未找到发货清单');
        if (count($express) != count($sends)) parent::ajax_exception(000, '快递数量错误');

        //添加发货单号
        $insert = [];
        $date = date('Y-m-d H:i:s');
        foreach ($express as $k => $v) {

            $insert[$k]['id'] = $v['id'];
            $insert[$k]['send_create'] = $date;
            $insert[$k]['express_no'] = $sends[$v['id']];
        }

        //更新数据库
        if (count($insert) > 0) {

            //获取管理员信息
            $master = parent::master();

            //新增发货列表
            $this->send->saveAll($insert);

            //更新订单信息
            $order->order_status = 20;
            $order->change_id = $master['id'];
            $order->change_nickname = $master['nickname'];
            $order->change_date = $date;
            $order->save();

            $setting = new SystemClass();
            $set = $setting->index();
            $class = new SmsClass();
            $class->TemplateParam = [
                'username' => $order->member_nickname,
                'order' => $order->order_number,
                'web' => $set['webName']
            ];
            $class->sendSms($order->member_phone, '11111', 'SMS_151996093');
//            $class->sendSms('13608302076', '11111', 'SMS_151996093');
        }
    }

    //取消订单时的订单状态验证
    public function validator_back()
    {
        $id = input('id');

        $order = new OrderModel();

        $order = $order->where('id', '=', $id)
            ->where('order_status', 'in', [10, 15])
            ->find();

        if (is_null($order)) parent::ajax_exception(000, '订单已锁定');

        return $order;
    }

    //需要取消的聚水潭订单号
    public function back_sends($order)
    {
        $result = $this->send->where('order_id', '=', $order['id'])->column('send_order');

        return $result;
    }

    //取消聚水潭中的订单
    public function jushuitan_back($orders)
    {
        if (count($orders) <= 0) return;

        //初始化聚水潭操作类
        $jushuitan_class = new JushuitanClass();

        //将订单号50一个分组
        $orders = array_chunk($orders, 50);

        $backs = [];

        $master = parent::master();

        //循环查询订单状体啊
        foreach ($orders as $v) {

            //接口查询订单状态
            $result = $jushuitan_class->orders_single_query($jushuitan_class->set['jushuitanShopid'], $v);

            //没有成功，返回
            if ($result['code'] != '0' || !isset($result['orders'])) continue;

            //组合到结果数组中
            foreach ($result['orders'] as $va) {

                //上传时的状态或者已经取消
                if ($va['status'] == 'WaitConfirm' || $va['status'] == 'Cancelled') {

                    $backs[] = [
                        'shop_id' => $jushuitan_class->set['jushuitanShopid'],
                        'so_id' => $va['so_id'],
                        'remark' => '米礼网后台取消订单，操作人：' . $master['nickname'],
                    ];
                } else {

                    exit('已经有部分订单发货成功，无法取消');
                }
            }
        }

        if (count($backs) <= 0) return;

        //若有订单上传到聚水潭，则取消之
        $result = $jushuitan_class->orders_cancel_upload($backs);
        if (!isset($result['code']) || ($result['code'] != '0')) {

            dump($result);
            Db::rollback();
            parent::ajax_exception(000, '聚水潭撤销订单失败');
        }
    }

    //取消订单
    public function order_back($order)
    {
        $master = parent::master();

        $order->order_status = 30;
        $order->change_id = $master['id'];
        $order->change_nickname = '管理员：' . $master['nickname'];
        $order->change_date = date('Y-m-d H:i:s');
        $order->save();

        $members = new MemberModel();
        $member = $members->where('id', '=', $order->member_id)->find();
        if (!is_null($member)) {

            $member->remind += $order->total;
            $member->save();

            //添加会员钱包记录
            $record = new MemberRecordModel();
            $record->member_id = $member->id;
            $record->account = $member->account;
            $record->nickname = $member->nickname;
            $record->content = '礼品订单（订单号：' . $order->order_number . '）,被管理员取消了,返还余额：' . $order->total;
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
            $record->content = '管理员『' . $master['nickname'] . '』,取消了礼品订单（订单号：' . $order->order_number . '），入库商品『' . $goods->name . '(编号：' . $goods->code . ')』库存：' . $order->goods_number . '件';
            $record->stock_now = $goods->stock;
            $record->type = '1';
            $record->save();
        }

    }
}