<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Goods extends Migrator
{
    public function up()
    {
        $table = $this->table('goods');

        $table->setId('id');
        $table->addColumn(Column::integer('goods_class_id')->setComment('分类id'));
        $table->addColumn(Column::string('goods_class_name')->setComment('分类名称'));

        $table->addColumn(Column::string('name')->setComment('名称'));
        $table->addColumn(Column::string('code')->setComment('编号'));
        $table->addColumn(Column::string('describe')->setComment('描述'));
        $table->addColumn(Column::decimal('amount',18)->setComment('单价'));
        $table->addColumn(Column::integer('sort')->setComment('排序'));
        $table->addColumn(Column::char('status',3)->setDefault('on')->setComment('状态，off下架，on上架'));
        $table->addColumn(Column::integer('cover')->setNullable()->setComment('封面id'));
        $table->addColumn(Column::string('location')->setNullable()->setComment('封面路径'));
        $table->addColumn(Column::integer('stock')->setDefault(0)->setComment('库存'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('goods');
    }
}
