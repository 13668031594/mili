<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/7/4
 * Time: 下午7:08
 */

namespace classes\recharge;

use app\recharge\model\RechargeModel;
use app\Substation\model\SubstationModel;
use classes\AdminClass;
use classes\order\SendClass;

class RechargeDownClass extends AdminClass
{
    public $dir = 'uploads';

    public function __construct()
    {
        if (!is_dir($this->dir)) mkdir($this->dir);
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
     * 返回
     *
     * @return string
     */
    public function excel()
    {
        $request = request();

        $where = [
            ['substation', '=', SUBSTATION]
        ];

        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');
        $status = $request->get('status');
        $from = $request->get('from');
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
                case '0':
                    $where[] = ['member_account|member_phone', 'like', '%' . $keyword . '%'];
                    break;
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
        if (!empty($from)) {
            $where[] = ['from', '=', $from];
        }

        $model = new RechargeModel();
        $orders = $model->where($where)->order('id', 'desc')->column('*');
        if (count($orders) <= 0) parent::ajax_exception(000, '没有需要导出的订单');

        $name = 'recharge' . date('Y-m-d');

        //获得站点信息
        $sub = new SubstationModel();
        $sub = $sub->column('id,name');
        $sub[0] = '主站';

        $from = [
            'bank' => '银行转账',
            'wechat' => '微信',
            'alipay' => '支付宝',
            'qq' => 'QQ',
        ];

        $status = [
            0 => '待处理',
            1 => '已处理',
            2 => '已取消',
            3 => '已拒绝',
        ];

        $header = [
            '订单号',
            '下单时间',
            '充值方式',
            '支付金额',
            '获得余额',
            '订单状态',
            '操作人',
            '操作时间',
            '会员账号',
            '会员电话',
            '会员昵称',
            '会员注册时间',
            '站点',
        ];

        $data = [];
        foreach ($orders as $k => $v) {

            $data[$k]['order_number'] = $v['order_number'];
            $data[$k]['created_at'] = $v['created_at'];
            $data[$k]['from'] = $from[$v['from']];
            $data[$k]['total'] = $v['total'];
            $data[$k]['remind'] = $v['remind'];
            $data[$k]['status'] = $status[$v['status']];
            $data[$k]['change_nickname'] = $v['change_nickname'];
            $data[$k]['change_date'] = $v['change_date'];
            $data[$k]['member_account'] = $v['member_account'];
            $data[$k]['member_phone'] = $v['member_phone'];
            $data[$k]['member_nickname'] = $v['member_nickname'];
            $data[$k]['member_create'] = $v['member_create'];
            $data[$k]['substation'] = $sub[$v['substation']];
        }

        $class = new SendClass();
        return $class->excelExport($name, $header, $data);
    }
}