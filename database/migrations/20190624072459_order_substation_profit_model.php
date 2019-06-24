<?php

use think\migration\Migrator;
use think\migration\db\Column;

class OrderSubstationProfitModel extends Migrator
{
    public function up()
    {
        $table = $this->table('order_substation_profit');
        $table->setId('id');
        //id信息
        $table->addColumn(Column::integer('order_id')->setComment('订单id'));
        $table->addColumn(Column::integer('goods_number')->setComment('订单商品总数'));
        $table->addColumn(Column::integer('express_number')->setComment('订单快递总数'));

        //站点信息
        $table->addColumn(Column::integer('order_sub')->setComment('订单来源分站id'));
        $table->addColumn(Column::integer('child_sub')->setComment('下级分站id'));
        $table->addColumn(Column::integer('my_sub')->setComment('收益分站id'));

        //订单信息
        $table->addColumn(Column::decimal('order_cost_all', 18, 2)->setComment('订单总成本'));
        $table->addColumn(Column::decimal('child_cost_all', 18, 2)->setComment('下级分站总成本价'));
        $table->addColumn(Column::decimal('my_cost_all', 18, 2)->setComment('收益分站总成本价'));
        $table->addColumn(Column::decimal('profit_all', 18, 2)->setComment('收益分站总收益'));

        //商品信息
        $table->addColumn(Column::decimal('order_cost_goods', 18, 2)->setComment('订单商品成本价格'));
        $table->addColumn(Column::decimal('child_cost_goods', 18, 2)->setComment('下级分站商品成本价格'));
        $table->addColumn(Column::decimal('my_cost_goods', 18, 2)->setComment('收益分站商品成本价格'));
        $table->addColumn(Column::decimal('profit_goods', 18, 2)->setComment('收益分站商品收益，单个'));
        $table->addColumn(Column::decimal('profit_goods_all', 18, 2)->setComment('收益分站商品收益，总计'));

        //快递信息
        $table->addColumn(Column::decimal('order_cost_express', 18, 2)->setComment('订单快递成本价格'));
        $table->addColumn(Column::decimal('child_cost_express', 18, 2)->setComment('下级分站快递成本价格'));
        $table->addColumn(Column::decimal('my_cost_express', 18, 2)->setComment('收益分站快递成本价格'));
        $table->addColumn(Column::decimal('profit_express', 18, 2)->setComment('收益分站快递收入，单个'));
        $table->addColumn(Column::decimal('profit_express_all', 18, 2)->setComment('收益分站快递收入，总计'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('order_substation_profit');
    }
}
