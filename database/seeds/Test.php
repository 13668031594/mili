<?php

use think\migration\Seeder;

class Test extends Seeder
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
//        self::recharge();
//        self::withdraw();
//        self::order();
    }

    private function recharge()
    {
        $member = new \app\member\model\MemberModel();

        $member = $member->column('id,nickname,created_at,account,phone');

        if (count($member) <= 0) return;

        $insert = [];
        $i = 1;
        $date = date('Y-m-d H:i:s');

        foreach ($member as $v) {

            $insert[$i]['order_number'] = 'ABC0000' . $i;
            $insert[$i]['total'] = 1000;
            $insert[$i]['remind'] = 1000;
            $insert[$i]['member_id'] = $v['id'];
            $insert[$i]['member_nickname'] = $v['nickname'];
            $insert[$i]['member_create'] = $v['created_at'];
            $insert[$i]['member_account'] = $v['account'];
            $insert[$i]['member_phone'] = $v['phone'];
            $insert[$i]['created_at'] = $date;

            $i++;
        }

        if (count($insert) > 0) {
            $model = new \app\recharge\model\RechargeModel();
            $model->insertAll($insert);
        }
    }

    private function withdraw()
    {
        $member = new \app\member\model\MemberModel();

        $member = $member->column('id,nickname,created_at,account,phone,bank_no');

        if (count($member) <= 0) return;

        $insert = [];
        $i = 1;
        $date = date('Y-m-d H:i:s');

        foreach ($member as $v) {

            $insert[$i]['order_number'] = 'ABC0000' . $i;
            $insert[$i]['total'] = 1000;
            $insert[$i]['commis'] = 1000;
            $insert[$i]['member_id'] = $v['id'];
            $insert[$i]['member_nickname'] = $v['nickname'];
            $insert[$i]['member_create'] = $v['created_at'];
            $insert[$i]['member_account'] = $v['account'];
            $insert[$i]['member_phone'] = $v['phone'];
            $insert[$i]['member_bank_no'] = $v['bank_no'];
            $insert[$i]['created_at'] = $date;

            $i++;
        }

        if (count($insert) > 0) {
            $model = new \app\withdraw\model\WithdrawModel();
            $model->insertAll($insert);
        }
    }

    private function order()
    {
        $member = new \app\member\model\MemberModel();
        $member = $member->column('*');
        if (count($member) <= 0) return '1';

        $grade = new \app\member\model\MemberGradeModel();
        $grade = $grade->column('*');
        if (count($grade) <= 0) return '2';

        $store = new \app\member\model\MemberStoreModel();
        $store = $store->column('*');
        if (count($store) <= 0) return '3';

        $goods = new \app\goods\model\GoodsModel();
        $goods = $goods->where('status', '=', 'on')->column('*');
        if (count($goods) <= 0) return '4';

        $first_express = new \app\express\model\ExpressModel();
        $first_express = $first_express->find();
        if (is_null($first_express))return '5';

        $last_order = new \app\order\model\OrderModel();
        $last_order = $last_order->order('id', 'desc')->find();
        $i = is_null($last_order) ? 1 : ($last_order['id'] + 1);

        $insert = [];
        $insert_express = [];
        $date = date('Y-m-d H:i:s');
        $platform = config('member.store_platform');
        $goods_number = 10;//发货数
        $express_number = 100;//快递数
        foreach ($store as $v) {

            $m = $member[$v['member_id']];
            $p = $platform[$v['platform']];

            $express_amount = $grade[$m['grade_id']]['amount'];//快递费
            $express_total = number_format($express_number * $express_amount, 2, '.', '');//总快递费

            foreach ($goods as $va) {

                $goods_amount = $va['amount'];
                $goods_total = number_format($express_number * $goods_number * $goods_amount, 2, '.', '');//总快递费

                $insert[$i]['order_number'] = $m['phone'] . '-' . $i;
                $insert[$i]['total'] = ($express_total + $goods_total);
                $insert[$i]['total_express'] = $express_total;
                $insert[$i]['total_goods'] = $goods_total;
                $insert[$i]['express_amount'] = $express_amount;
                $insert[$i]['express_number'] = $express_number;
                $insert[$i]['goods_number'] = $goods_number;
                $insert[$i]['express_id'] = $first_express['id'];
                $insert[$i]['express_name'] = $first_express['name'];

                $insert[$i]['member_id'] = $m['id'];
                $insert[$i]['member_account'] = $m['account'];
                $insert[$i]['member_phone'] = $m['phone'];
                $insert[$i]['member_nickname'] = $m['nickname'];
                $insert[$i]['member_create'] = $m['created_at'];
                $insert[$i]['member_grade_id'] = $m['grade_id'];
                $insert[$i]['member_grade_name'] = $m['grade_name'];

                $insert[$i]['goods_class_id'] = $va['goods_class_id'];
                $insert[$i]['goods_class_name'] = $va['goods_class_name'];
                $insert[$i]['goods_id'] = $va['id'];
                $insert[$i]['goods_name'] = $va['name'];
                $insert[$i]['goods_code'] = $va['code'];
                $insert[$i]['goods_describe'] = $va['describe'];
                $insert[$i]['goods_amount'] = $va['amount'];
                $insert[$i]['goods_sort'] = $va['sort'];
                $insert[$i]['goods_status'] = $va['status'];
                $insert[$i]['goods_cover'] = $va['cover'];
                $insert[$i]['goods_location'] = $va['location'];
                $insert[$i]['goods_stock'] = $va['stock'];
                $insert[$i]['goods_created'] = $va['created_at'];

                $insert[$i]['store_id'] = $v['id'];
                $insert[$i]['store_name'] = $v['name'];
                $insert[$i]['store_sort'] = $v['sort'];
                $insert[$i]['store_platform'] = $v['platform'];
                $insert[$i]['store_platform_name'] = $p;
                $insert[$i]['store_man'] = $v['man'];
                $insert[$i]['store_phone'] = $v['phone'];
                $insert[$i]['store_created'] = $v['created_at'];

                $insert[$i]['created_at'] = $date;

                $content = [];
                for ($s = $express_number; $s > 0; $s--) {

                    $name = '收货人：' . $i . '-' . $s;
                    $phone = '收货电话：' . $m['phone'] . '-' . $i . '-' . $s;
                    $address = '收货地址：ABC-' . $i . '-' . $s;
                    $content[] = $name . '#$' . $phone . '#$' . $address;
                }
                $insert_express[$i]['order_id'] = $i;
                $insert_express[$i]['content'] = implode('#$%', $content);

                $i++;
            }
        }

        if (count($insert) > 0) {
            $insert_model = new \app\order\model\OrderModel();
            $insert_model->insertAll($insert);
        }

        if (count($insert_express) > 0) {

            $insert_express_model = new \app\order\model\OrderExpressModel();
            $insert_express_model->insertAll($insert_express);
        }
    }
}