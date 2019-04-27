<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Repair extends Migrator
{
    public function up()
    {
        $table = $this->table('repair');
        $table->setId('id');
        $table->addColumn(Column::string('order_number')->setComment('工单编号'));
        $table->addColumn(Column::integer('uid')->setComment('会员id'));
        $table->addColumn(Column::integer('repair_class_id')->setComment('分类id'));
        $table->addColumn(Column::string('repair_class_name')->setComment('分类名称'));
        $table->addColumn(Column::string('nickname')->setComment('昵称'));
        $table->addColumn(Column::string('phone')->setComment('联系电话'));
        $table->addColumn(Column::text('content')->setComment('内容'));
        $table->addColumn(Column::char('status', 2)->setDefault(10)->setComment('状态'));
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('编辑时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('repair');
    }
}
