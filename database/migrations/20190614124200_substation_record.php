<?php

use think\migration\Migrator;
use think\migration\db\Column;

class SubstationRecord extends Migrator
{
    public function up()
    {
        $table = $this->table('substation_record');

        $table->setId('id');

        //会员字段
        $table->addColumn(Column::integer('substation')->setComment('站点id'));

        //积分
        $table->addColumn(Column::decimal('balance', 18)->setDefault(0)->setComment('变化余额'));
        $table->addColumn(Column::decimal('balance_now', 18)->setDefault(0)->setComment('变化后余额'));

        //其他
        $table->addColumn(Column::char('type', 2)->setDefault('0')->setComment('类型'));
        $table->addColumn(Column::string('content')->setDefault('无')->setComment('文字描述'));
        $table->addColumn(Column::string('other')->setNullable()->setComment('额外字段'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('substation_record');
    }
}
