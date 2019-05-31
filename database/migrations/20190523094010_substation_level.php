<?php

use think\migration\Migrator;
use think\migration\db\Column;

class SubstationLevel extends Migrator
{
    public function up()
    {
        $table = $this->table('substation_level');
        $table->setId('id');
        $table->addColumn(Column::string('name', 255)->setComment('等级名称'));
        $table->addColumn(Column::integer('sort')->setComment('排序'));

        $table->addColumn(Column::decimal('goods_up', 18, 2)->setComment('商品上浮价'));
        $table->addColumn(Column::decimal('express_up', 18, 2)->setComment('快递上浮价'));

        $table->addColumn(Column::decimal('goods_cost_up', 18, 2)->setComment('商品上浮成本价'));
        $table->addColumn(Column::decimal('express_cost_up', 18, 2)->setComment('快递上浮成本价'));

        $table->addColumn(Column::decimal('goods_protect_up', 18, 2)->setComment('商品上浮成本价'));
        $table->addColumn(Column::decimal('express_protect_up', 18, 2)->setComment('快递上浮成本价'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'));
        $table->addColumn(Column::timestamp('updated_at')->setNullable()->setComment('更新时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('substation_level');
    }
}
