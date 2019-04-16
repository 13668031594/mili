<?php

use think\migration\Migrator;
use think\migration\db\Column;

class OrderSend extends Migrator
{
    public function up()
    {
        $table = $this->table('order_send');

        $table->setId('id');
        $table->addColumn(Column::timestamp('send_create')->setNullable()->setComment('发货时间'));

        $table->addColumn(Column::integer('order_id')->setComment('订单id'));
        $table->addColumn(Column::string('order_number')->setComment('订单号'));
        $table->addColumn(Column::timestamp('order_create')->setComment('下单时间'));

        $table->addColumn(Column::string('store')->setComment('店铺名称'));
        $table->addColumn(Column::string('express')->setComment('快递名称'));
        $table->addColumn(Column::string('express_no')->setNullable()->setComment('快递号'));
        $table->addColumn(Column::string('goods')->setComment('商品名称'));

        $table->addColumn(Column::string('consignee')->setComment('收件人'));
        $table->addColumn(Column::string('phone')->setComment('收件人电话'));
        $table->addColumn(Column::string('address')->setComment('收件地址'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('order_send');
    }
}
