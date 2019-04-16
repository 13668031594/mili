<?php

use think\migration\Migrator;
use think\migration\db\Column;

class GoodsRecord extends Migrator
{
    public function up()
    {
        $table = $this->table('goods_record');

        $table->setId('id');

        //会员字段
        $table->addColumn(Column::integer('goods_id')->setComment('商品id'));
        $table->addColumn(Column::string('name')->setComment('名称'));
        $table->addColumn(Column::string('code')->setComment('编号'));

        //积分
        $table->addColumn(Column::integer('stock')->setDefault(0)->setComment('变化库存'));
        $table->addColumn(Column::integer('stock_now')->setDefault(0)->setComment('变化后库存'));

        //其他
        $table->addColumn(Column::char('type', 2)->setDefault('1')->setComment('类型,1入库，2出库'));
        $table->addColumn(Column::string('content')->setDefault('无')->setComment('文字描述'));
        $table->addColumn(Column::string('other')->setNullable()->setComment('额外字段'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('goods_record');
    }
}
