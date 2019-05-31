<?php

use think\migration\Migrator;
use think\migration\db\Column;

class SubstationLevelUp extends Migrator
{
    public function up()
    {
        $table = $this->table('substation_level_up');

        $table->setId('id');
        $table->addColumn(Column::integer('level_id')->setComment('等级id'));
        $table->addColumn(Column::integer('substation')->setComment('分站id'));
        $table->addColumn(Column::decimal('goods_up', 18, 2)->setComment('商品上浮价'));
        $table->addColumn(Column::decimal('express_up', 18, 2)->setComment('快递上浮价'));
        $table->addColumn(Column::decimal('goods_cost_up', 18, 2)->setComment('商品上浮成本价'));
        $table->addColumn(Column::decimal('express_cost_up', 18, 2)->setComment('快递上浮成本价'));
        $table->addColumn(Column::decimal('goods_protect_up', 18, 2)->setComment('商品上浮成本价'));
        $table->addColumn(Column::decimal('express_protect_up', 18, 2)->setComment('快递上浮成本价'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('substation_level_up');
    }
}
