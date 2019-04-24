<?php

use think\migration\Migrator;
use think\migration\db\Column;

class RepairClass extends Migrator
{
    public function up()
    {
        $table = $this->table('repair');
        $table->setId('id');
        $table->addColumn(Column::string('name')->setComment('分类名称'));
        $table->addColumn(Column::integer('sort')->setComment('分类排序'));
        $table->addColumn(Column::string('back')->setComment('背景板'));
        $table->addColumn(Column::char('show', 4)->setComment('是否显示'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('下单时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('repair');
    }
}
