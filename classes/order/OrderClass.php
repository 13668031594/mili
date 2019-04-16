<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/25
 * Time: 下午2:14
 */

namespace classes\order;

use app\order\model\OrderExpressModel;
use app\order\model\OrderModel;
use app\order\model\OrderSendModel;
use classes\AdminClass;
use classes\system\SystemClass;
use classes\vendor\SmsClass;
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

        $where = [];

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


        return parent::page($this->model, ['where' => $where]);
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
}