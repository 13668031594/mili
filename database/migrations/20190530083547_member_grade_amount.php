<?php

use think\migration\Migrator;
use think\migration\db\Column;

class MemberGradeAmount extends Migrator
{
    public function up()
    {
        $table = $this->table('member_grade_amount');

        $table->setId('id');
        $table->addColumn(Column::integer('grade')->setComment('等级id'));
        $table->addColumn(Column::string('substation')->setComment('分站id'));
        $table->addColumn(Column::string('status')->setDefault('on')->setComment('分站id'));
        $table->addColumn(Column::decimal('recharge', 18)->setComment('充值升级'));
        $table->addColumn(Column::decimal('buy_total', 18)->setComment('购买升级'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('member_grade_amount');
    }
}
