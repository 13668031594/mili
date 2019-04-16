<?php

use think\migration\Migrator;
use think\migration\db\Column;

class MemberGrade extends Migrator
{
    public function up()
    {
        $table = $this->table('member_grade');
        $table->setId('id');
        $table->addColumn(Column::string('name')->setComment('等级名称'));
        $table->addColumn(Column::integer('sort')->setComment('排序'));
        $table->addColumn(Column::decimal('amount', 18)->setComment('统一快递费'));
        $table->addColumn(Column::char('mode', 3)->setDefault('on')->setComment('快递费模式，0统一，1独立'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('member_grade');
    }
}
