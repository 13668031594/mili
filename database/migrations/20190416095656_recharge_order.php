<?php

use think\migration\Migrator;
use think\migration\db\Column;

class RechargeOrder extends Migrator
{
    public function up()
    {
        $table = $this->table('recharge_order');
        $table->setId('id');

        //订单基础
        $table->addColumn(Column::string('order_number')->setDefault(0)->setComment('订单号'));
        //操作情况
        $table->addColumn(Column::char('status', 1)->setDefault(0)->setComment('订单状态，0未使用，1已使用'));
        //会员情况
        $table->addColumn(Column::integer('member_id')->setComment('会员id'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('下单时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('recharge_order');
    }
}
