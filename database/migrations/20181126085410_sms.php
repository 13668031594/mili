<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Sms extends Migrator
{
    public function up()
    {
        $table = $this->table('sms');
        $table->setId('id');

        //签到基础
        $table->addColumn(Column::string('phone')->setComment('电话号码'));
        $table->addColumn(Column::string('end')->setComment('过期时间'));
        $table->addColumn(Column::string('code')->setComment('验证码'));
        $table->addColumn(Column::timestamp('created_at')->setComment('发送时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('sms');
    }
}
