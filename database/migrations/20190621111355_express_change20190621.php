<?php

use think\migration\Migrator;
use think\migration\db\Column;

class ExpressChange20190621 extends Migrator
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
        $table = $this->table('express');
        $table->addColumn(Column::decimal('cost', 18, 2)->setDefault(0)->setComment('成本价'));
        $table->addColumn(Column::decimal('protect', 18, 2)->setDefault(0)->setComment('保护价'));
        $table->save();
    }
}
