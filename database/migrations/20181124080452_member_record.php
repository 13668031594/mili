<?php

use think\migration\Migrator;
use think\migration\db\Column;

class MemberRecord extends Migrator
{
    public function up()
    {
        $table = $this->table('member_record');

        $table->setId('id');

        //会员字段
        $table->addColumn(Column::integer('member_id')->setComment('会员id'));
        $table->addColumn(Column::string('account')->setComment('账号'));
        $table->addColumn(Column::string('nickname')->setComment('昵称'));

        //积分
        $table->addColumn(Column::decimal('remind', 18)->setDefault(0)->setComment('变化余额'));
        $table->addColumn(Column::decimal('remind_now', 18)->setDefault(0)->setComment('变化后余额'));
        $table->addColumn(Column::decimal('remind_all', 18)->setDefault(0)->setComment('变化后余额流水'));

        //资产
        $table->addColumn(Column::decimal('commis', 18)->setDefault(0)->setComment('变化佣金'));
        $table->addColumn(Column::decimal('commis_now', 18)->setDefault(0)->setComment('变化后佣金'));
        $table->addColumn(Column::decimal('commis_all', 18)->setDefault(0)->setComment('变化后佣金流水'));

        //其他
        $table->addColumn(Column::char('type', 2)->setDefault('0')->setComment('类型'));
        $table->addColumn(Column::string('content')->setDefault('无')->setComment('文字描述'));
        $table->addColumn(Column::string('other')->setNullable()->setComment('额外字段'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('member_record');
    }
}
