<?php

use think\migration\Migrator;
use think\migration\db\Column;

class RechargeGradeUp extends Migrator
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
        $table = $this->table('member');
        $table->addColumn(Column::integer('recharge')->setDefault(0)->setComment('累计充值'));
        $table->save();

        $table = $this->table('member_grade');
        $table->addColumn(Column::integer('recharge')->setDefault(0)->setComment('充值自动升级数'));
        $table->addColumn(Column::integer('buy_total')->setDefault(0)->setComment('购买升级金额'));
        $table->save();
    }
}
