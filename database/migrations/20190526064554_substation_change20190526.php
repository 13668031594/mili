<?php

use think\migration\Migrator;
use think\migration\db\Column;

class SubstationChange20190526 extends Migrator
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
        $table = $this->table('substation');
        $table->addColumn(Column::integer('level_id')->setDefault(1)->setComment('等级id'));
        $table->addColumn(Column::string('level_name')->setDefault('普通分站')->setComment('等级名称'));
        $table->save();
    }
}
