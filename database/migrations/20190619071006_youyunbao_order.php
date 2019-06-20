<?php

use think\migration\Migrator;
use think\migration\db\Column;

class YouyunbaoOrder extends Migrator
{
    public function up()
    {
        $table = $this->table('youyunbao_order');

        $table->setId('id');

        $table->addColumn(Column::string('state')->setNullable()->setComment('请求状态，1成功，0失败'));
        $table->addColumn(Column::string('qrcode')->setNullable()->setComment('请求二维码的数据'));
        $table->addColumn(Column::string('order')->setNullable()->setComment('云端单号'));
        $table->addColumn(Column::string('datas')->setNullable()->setComment('本地单号'));
        $table->addColumn(Column::decimal('money', 18, 2)->setNullable()->setComment('请求金额'));
        $table->addColumn(Column::string('times')->setNullable()->setComment('订单时间'));
        $table->addColumn(Column::string('orderstatus')->setDefault(0)->setComment('订单状态，0待付款，1已经付款'));
        $table->addColumn(Column::string('text')->setNullable()->setComment('代码标识'));

        $table->addColumn(Column::integer('member_id')->setNullable()->setComment('会员id'));

        $table->addColumn(Column::string('recharge_order')->setNullable()->setComment('站内支付订单号'));
        $table->addColumn(Column::string('pay_id')->setNullable()->setComment('支付记录id'));
        $table->addColumn(Column::decimal('pay_money', 18, 2)->setNullable()->setComment('实际支付金额'));

        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('下单站点'));
        $table->addColumn(Column::string('recharge_type')->setDefault(0)->setComment('充值类型，0会员充值，1分站充值'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('youyunbao_order');
    }
}
