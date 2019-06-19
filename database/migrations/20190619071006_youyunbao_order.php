<?php

use think\migration\Migrator;
use think\migration\db\Column;

class YouyunbaoOrder extends Migrator
{
    public function up()
    {
        $table = $this->table('youyunbao_order');

        $table->setId('id');

        $table->addColumn(Column::string('state')->setNullable()->setComment('请求状态，1成功，0失败'));
        $table->addColumn(Column::string('qrcode')->setNullable()->setComment('请求二维码的数据'));
        $table->addColumn(Column::string('order')->setNullable()->setComment('云端单号'));
        $table->addColumn(Column::string('datas')->setNullable()->setComment('本地单号'));
        $table->addColumn(Column::decimal('money', 18, 2)->setNullable()->setComment('请求金额'));
        $table->addColumn(Column::string('times')->setNullable()->setComment('订单时间'));
        $table->addColumn(Column::string('orderstatus')->setDefault(0)->setComment('订单状态，0待付款，1已经付款'));
        $table->addColumn(Column::string('text')->setNullable()->setComment('代码标识'));

        $table->addColumn(Column::integer('member_id')->setNullable()->setComment('会员id'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('youyunbao_order');
    }
}
