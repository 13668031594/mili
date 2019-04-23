<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Substation extends Migrator
{
    public function up()
    {
        $table = $this->table('substation');
        $table->setId('id');
        $table->addColumn(Column::string('name')->setDefault(0)->setComment('分站名称'));
        $table->addColumn(Column::string('localhost')->setDefault(0)->setComment('域名'));
        $table->addColumn(Column::integer('pid')->setDefault(0)->setComment('上级网站'));
        $table->addColumn(Column::integer('top')->setDefault(0)->setComment('顶级网站'));
        $table->addColumn(Column::char('status', 4)->setDefault('off')->setComment('分站状态'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('下单时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('substation');
    }
}
