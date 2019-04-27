<?php

use think\migration\Migrator;
use think\migration\db\Column;

class RepairDefault extends Migrator
{
    public function up()
    {
        $table = $this->table('repair_default');
        $table->setId('id');
        $table->addColumn(Column::string('repair_class_id')->setComment('分类id'));
        $table->addColumn(Column::string('repair_class_name')->setComment('分类名称'));
        $table->addColumn(Column::integer('sort')->setComment('排序'));
        $table->addColumn(Column::string('title')->setComment('标题'));
        $table->addColumn(Column::char('show', 4)->setComment('是否显示'));
        $table->addColumn(Column::text('content')->setComment('内容'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('repair_default');
    }
}
