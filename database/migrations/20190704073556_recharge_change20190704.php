<?php

use think\migration\Migrator;
use think\migration\db\Column;

class RechargeChange20190704 extends Migrator
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
        $table = $this->table('recharge');
        $table->addColumn(Column::string('from')->setDefault('bank')->setComment('充值来源'));
        $table->save();

        $table = $this->table('substation_recharge');
        $table->addColumn(Column::string('from')->setDefault('bank')->setComment('充值来源'));
        $table->save();

        $table = $this->table('youyunbao_order');
        $table->addColumn(Column::string('from')->setDefault('wechat')->setComment('充值来源'));
        $table->save();
    }
}
