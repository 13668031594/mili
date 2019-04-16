<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Change20181128 extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('banner');
        $table->addColumn(Column::string('link')->setDefault('http://www.baidu.com')->setComment('链接'));
        $table->save();

        $table = $this->table('adv');
        $table->addColumn(Column::string('link')->setDefault('http://www.baidu.com')->setComment('链接'));
        $table->save();

        $table = $this->table('member_store');
        $table->addColumn(Column::char('show',3)->setDefault('on')->setComment('显示'));
        $table->save();

        $table = $this->table('goods');
        $table->addColumn(Column::decimal('weight',18)->setDefault(0)->setComment('重量'));
        $table->save();

        $table = $this->table('order_send');
        $table->addColumn(Column::string('send_order')->setComment('发货编号，订单号+编号'));
        $table->save();

        $table = $this->table('member');
        $table->addColumn(Column::string('cover')->setNullable()->setComment('头像'));
        $table->save();
    }
}
