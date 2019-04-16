<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Express extends Migrator
{
    public function up()
    {
        $table = $this->table('express');
        $table->setId('id');
        $table->addColumn(Column::string('name', 255)->setComment('快递名称'));
        $table->addColumn(Column::integer('sort')->setComment('排序'));
        $table->addColumn(Column::char('disabled', 3)->setComment('状态'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('express');
    }
}
