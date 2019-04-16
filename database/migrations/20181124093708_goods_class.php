<?php

use think\migration\Migrator;
use think\migration\db\Column;

class GoodsClass extends Migrator
{
    public function up()
    {
        $table = $this->table('goods_class');

        $table->setId('id');

        $table->addColumn(Column::string('name')->setComment('名称'));
        $table->addColumn(Column::integer('sort')->setComment('排序'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('goods_class');
    }
}
