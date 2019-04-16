<?php

use think\migration\Migrator;
use think\migration\db\Column;

class MemberGradeExpress extends Migrator
{
    public function up()
    {
        $table = $this->table('member_grade_express');
        $table->setId(false);
        $table->addColumn(Column::integer('express')->setComment('快递id'));
        $table->addColumn(Column::integer('grade')->setComment('等级id'));
        $table->addColumn(Column::decimal('amount', 18)->setComment('快递费'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('member_grade_express');
    }
}
