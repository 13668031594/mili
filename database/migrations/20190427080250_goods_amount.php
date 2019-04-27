<?php

use think\migration\Migrator;
use think\migration\db\Column;

class GoodsAmount extends Migrator
{
    public function up()
    {
        $table = $this->table('goods_amount');

        $table->setId('id');
        $table->addColumn(Column::integer('goods_id')->setComment('商品id'));
        $table->addColumn(Column::decimal('amount',18)->setComment('描述'));
        $table->addColumn(Column::string('substation')->setComment('分站id'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('goods_amount');
    }
}
