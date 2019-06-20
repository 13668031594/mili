<?php

use think\migration\Migrator;
use think\migration\db\Column;

class YouyunbaoPay extends Migrator
{
    public function up()
    {
        $table = $this->table('youyunbao_pay');

        $table->setId('id');

        $table->addColumn(Column::string('ddh')->setNullable()->setComment('支付宝/微信/QQ钱包交易单号'));
        $table->addColumn(Column::decimal('money', 18, 2)->setNullable()->setComment('支付金额'));
        $table->addColumn(Column::string('name')->setNullable()->setComment('网站单号/支付宝备注'));
        $table->addColumn(Column::string('key')->setNullable()->setComment('密钥'));
        $table->addColumn(Column::string('paytime')->setNullable()->setComment('支付成功日期'));
        $table->addColumn(Column::string('lb')->setNullable()->setComment('支付类型 1支付宝，2QQ钱包，3微信'));
        $table->addColumn(Column::string('type')->setNullable()->setComment('支付类型 1支付宝，2QQ钱包，3微信'));

        $table->addColumn(Column::string('notify')->setNullable()->setComment('回调结果'));
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('回调站点'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('添加时间'));

        $table->save();
    }

    public function down()
    {
        $this->dropTable('youyunbao_pay');
    }
}
