<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Order extends Migrator
{
    public function up()
    {
        $table = $this->table('order');
        $table->setId('id');

        //订单基础
        $table->addColumn(Column::string('order_number')->setComment('订单号'));
        $table->addColumn(Column::decimal('total', 18)->setComment('总金额'));
        $table->addColumn(Column::decimal('total_express', 18)->setComment('快递总金额'));
        $table->addColumn(Column::decimal('total_goods', 18)->setComment('商品总金额'));
        $table->addColumn(Column::decimal('express_amount', 18)->setComment('快递单价'));
        $table->addColumn(Column::integer('express_number')->setComment('快递数量'));
        $table->addColumn(Column::integer('goods_number')->setComment('商品数量'));
        $table->addColumn(Column::integer('express_id')->setComment('快递id'));
        $table->addColumn(Column::string('express_name')->setComment('快递名'));

        //付款情况
        $table->addColumn(Column::char('pay_status', 1)->setDefault(0)->setComment('付款状态，0待付款，1付款'));
        $table->addColumn(Column::char('pay_type', 1)->setDefault(0)->setComment('付款状态，0未付款，1前台付款，2后台付款'));
        $table->addColumn(Column::timestamp('pay_date')->setNullable()->setComment('付款时间'));

        //操作情况
        $table->addColumn(Column::char('order_status', 2)->setDefault(10)->setComment('订单状态，10待处理，20已发货，30撤回，40取消'));
        $table->addColumn(Column::integer('change_id')->setNullable()->setComment('操作人id'));
        $table->addColumn(Column::string('change_nickname')->setNullable()->setComment('操作人昵称'));
        $table->addColumn(Column::timestamp('change_date')->setNullable()->setComment('操作人时间'));

        //会员信息
        $table->addColumn(Column::integer('member_id')->setComment('会员id'));
        $table->addColumn(Column::string('member_account')->setComment('会员账号'));
        $table->addColumn(Column::string('member_phone')->setComment('会员电话'));
        $table->addColumn(Column::string('member_nickname')->setComment('会员昵称'));
        $table->addColumn(Column::timestamp('member_create')->setComment('会员注册时间'));
        $table->addColumn(Column::integer('member_grade_id')->setComment('身份id'));
        $table->addColumn(Column::string('member_grade_name')->setComment('身份'));

        //商品信息
        $table->addColumn(Column::integer('goods_class_id')->setComment('分类id'));
        $table->addColumn(Column::string('goods_class_name')->setComment('分类名称'));
        $table->addColumn(Column::string('goods_name')->setComment('名称'));
        $table->addColumn(Column::string('goods_code')->setComment('编号'));
        $table->addColumn(Column::string('goods_describe')->setComment('描述'));
        $table->addColumn(Column::decimal('goods_amount', 18)->setComment('描述'));
        $table->addColumn(Column::integer('goods_sort')->setComment('排序'));
        $table->addColumn(Column::char('goods_status', 3)->setDefault('on')->setComment('状态，off下架，on上架'));
        $table->addColumn(Column::integer('goods_cover')->setComment('封面id'));
        $table->addColumn(Column::string('goods_location')->setComment('封面路径'));
        $table->addColumn(Column::integer('goods_stock')->setComment('库存'));
        $table->addColumn(Column::timestamp('goods_created')->setComment('商品添加时间'));

        //发货店铺信息
        $table->addColumn(Column::string('store_name')->setComment('名称'));
        $table->addColumn(Column::integer('store_sort')->setComment('排序'));
        $table->addColumn(Column::char('store_platform')->setComment('平台id'));
        $table->addColumn(Column::string('store_platform_name')->setComment('平台名称'));
        $table->addColumn(Column::string('store_man')->setComment('发货人'));
        $table->addColumn(Column::string('store_phone')->setComment('电话'));
        $table->addColumn(Column::timestamp('store_created')->setNullable()->setComment('平台添加时间'));

        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('下单时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('order');
    }
}
