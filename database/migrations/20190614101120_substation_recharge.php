<?php

use think\migration\Migrator;
use think\migration\db\Column;

class SubstationRecharge extends Migrator
{
    public function up()
    {
        $table = $this->table('substation_recharge');
        $table->setId('id');

        //订单基础
        $table->addColumn(Column::string('order_number')->setComment('订单号'));
        $table->addColumn(Column::decimal('total', 18)->setComment('支付金额'));
        $table->addColumn(Column::decimal('remind', 18)->setComment('获得余额'));

        //操作情况
        $table->addColumn(Column::char('status', 1)->setDefault(0)->setComment('订单状态，0待处理，1已处理，2已取消'));
        $table->addColumn(Column::integer('change_id')->setNullable()->setComment('操作人id'));
        $table->addColumn(Column::string('change_nickname')->setNullable()->setComment('操作人昵称'));
        $table->addColumn(Column::timestamp('change_date')->setNullable()->setComment('操作人时间'));

        //会员情况
        $table->addColumn(Column::integer('substation')->setComment('下单分站'));
        $table->addColumn(Column::string('master_nickname')->setComment('管理员昵称'));
        $table->addColumn(Column::string('master_id')->setComment('管理员id'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('下单时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('substation_recharge');
    }
}
