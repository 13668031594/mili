<?php

use think\migration\Migrator;
use think\migration\db\Column;

class GoodsContent extends Migrator
{
    public function up()
    {
        $table = $this->table('goods_content');

        $table->setId('id');

        $table->addColumn(Column::integer('goods')->setComment('商品id'));
        $table->addColumn(Column::text('content')->setNullable()->setComment('正文'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('goods_content');
    }
}
