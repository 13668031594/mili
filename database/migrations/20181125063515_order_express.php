<?php

use think\migration\Migrator;
use think\migration\db\Column;

class OrderExpress extends Migrator
{
    public function up()
    {
        $table = $this->table('order_express');

        $table->setId('id');
        $table->addColumn(Column::integer('order_id')->setComment('订单id'));
        $table->addColumn(Column::text('content')->setComment('内容'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('order_express');
    }
}
