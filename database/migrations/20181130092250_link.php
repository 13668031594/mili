<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Link extends Migrator
{
    public function up()
    {
        $table = $this->table('link');
        $table->setId('id');
        $table->addColumn(Column::string('title')->setComment('标题'));
        $table->addColumn(Column::integer('sort')->setComment('排序'));
        $table->addColumn(Column::char('show',3)->setDefault('on')->setComment('显示'));
        $table->addColumn(Column::char('hot',3)->setDefault('on')->setComment('加热'));
        $table->addColumn(Column::string('link')->setComment('地址'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('link');
    }
}
