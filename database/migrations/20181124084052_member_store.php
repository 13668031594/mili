<?php

use think\migration\Migrator;
use think\migration\db\Column;

class MemberStore extends Migrator
{
    public function up()
    {
        $table = $this->table('member_store');

        $table->setId('id');

        $table->addColumn(Column::integer('member_id')->setComment('会员id'));
        $table->addColumn(Column::string('name')->setComment('名称'));
        $table->addColumn(Column::integer('sort')->setComment('排序'));
        $table->addColumn(Column::char('platform')->setComment('平台'));
        $table->addColumn(Column::string('man')->setComment('发货人'));
        $table->addColumn(Column::string('phone')->setComment('电话'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('member_store');
    }
}
