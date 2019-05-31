<?php

use think\migration\Migrator;
use think\migration\db\Column;

class OrderChange20190531 extends Migrator
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
        $table = $this->table('order');
        $table->addColumn(Column::decimal('express_cost', 18, 2)->setDefault(0)->setComment('快递成本价'));
        $table->addColumn(Column::decimal('express_cost_all', 18, 2)->setDefault(0)->setComment('快递总成本价'));
        $table->addColumn(Column::decimal('goods_cost_all', 18, 2)->setDefault(0)->setComment('商品总成本价'));
        $table->save();
    }
}
