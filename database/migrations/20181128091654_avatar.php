<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Avatar extends Migrator
{
    public function up()
    {
        $table = $this->table('avatar');
        $table->setId('id');
        $table->addColumn(Column::string('title', 255)->setComment('描述'));
        $table->addColumn(Column::char('show', 3)->setComment('显示'));
        $table->addColumn(Column::integer('image')->setComment('图片id'));
        $table->addColumn(Column::string('location')->setComment('图片路径'));
        $table->addColumn(Column::integer('sort')->setComment('排序'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('avatar');
    }
}
