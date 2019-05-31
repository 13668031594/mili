<?php

use think\migration\Migrator;
use think\migration\db\Column;

class GoodsLevelAmount extends Migrator
{
    public function up()
    {
        $table = $this->table('goods_level_amount');

        $table->addColumn(Column::integer('goods_id')->setComment('商品id'));
        $table->addColumn(Column::decimal('cost', 18)->setComment('等级成本价'));
        $table->addColumn(Column::decimal('protect', 18)->setComment('等级保护价'));
        $table->addColumn(Column::integer('substation')->setComment('分站id'));
        $table->addColumn(Column::integer('level_id')->setComment('分站等级id'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('goods_level_amount');
    }
}
