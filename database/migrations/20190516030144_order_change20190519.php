<?php

use think\migration\Migrator;
use think\migration\db\Column;

class OrderChange20190519 extends Migrator
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
        $table = $this->table('order_send');
        $table->addColumn(Column::string('pro')->setNullable()->setComment('收货省'));
        $table->addColumn(Column::string('city')->setNullable()->setComment('收货市'));
        $table->addColumn(Column::string('area')->setNullable()->setComment('收货区'));
        $table->addColumn(Column::string('add')->setNullable()->setComment('收货地址'));
        $table->save();
    }
}
