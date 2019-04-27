<?php

use think\migration\Migrator;
use think\migration\db\Column;

class RepairNote extends Migrator
{
    public function up()
    {
        $table = $this->table('repair_note');
        $table->setId('id');
        $table->addColumn(Column::integer('uid')->setComment('会员id'));
        $table->addColumn(Column::integer('repair_id')->setComment('工单id'));
        $table->addColumn(Column::char('type', 1)->setComment('发布人类型,0后台，1会员'));
        $table->addColumn(Column::text('content')->setComment('内容'));
        $table->addColumn(Column::char('status', 2)->setDefault(10)->setComment('状态'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('repair_note');
    }
}
