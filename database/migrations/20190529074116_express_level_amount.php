<?php

use think\migration\Migrator;
use think\migration\db\Column;

class ExpressLevelAmount extends Migrator
{
    public function up()
    {
        $table = $this->table('express_level_amount');

        $table->addColumn(Column::integer('express')->setComment('快递id'));
        $table->addColumn(Column::integer('substation')->setComment('分站id'));
        $table->addColumn(Column::integer('level_id')->setComment('分站等级id'));
        $table->addColumn(Column::decimal('cost', 18)->setComment('等级成本价'));
        $table->addColumn(Column::decimal('protect', 18)->setComment('等级保护价'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('express_level_amount');
    }
}
