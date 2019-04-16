<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Notice extends Migrator
{
    public function up()
    {
        $table = $this->table('notice');
        $table->setId('id');
        $table->addColumn(Column::string('title')->setComment('标题'));
        $table->addColumn(Column::text('content')->setComment('内容'));
        $table->addColumn(Column::integer('sort')->setComment('排序'));
        $table->addColumn(Column::string('show')->setComment('显示'));
        $table->addColumn(Column::string('author')->setComment('发布人昵称'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('notice');
    }
}
