<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Master extends Migrator
{
    public function up()
    {
        $table = $this->table('master');
        $table->setId('id');
        $table->addColumn(Column::string('nickname', 255)->setComment('昵称'));
        $table->addColumn(Column::string('account', 255)->setComment('账号'));
        $table->addColumn(Column::string('password', 255)->setComment('密码'));
        $table->addColumn(Column::integer('login_times')->setDefault(0)->setComment('登录次数'));
        $table->addColumn(Column::string('login_ip')->setNullable()->setComment('登录ip'));
        $table->addColumn(Column::string('login_ass')->setNullable()->setComment('登录验证器'));
        $table->addColumn(Column::timestamp('login_time')->setNullable()->setComment('登录时间'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('master');
    }
}
