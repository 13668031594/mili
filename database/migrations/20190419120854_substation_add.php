<?php

use think\migration\Migrator;
use think\migration\db\Column;

class SubstationAdd extends Migrator
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
        $table = $this->table('adv');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('article');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('avatar');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('banner');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('express');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('goods');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('goods_class');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('link');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('master');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('member');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('member_grade');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('notice');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('order');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('recharge');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();

        $table = $this->table('withdraw');
        $table->addColumn(Column::string('substation')->setDefault(0)->setComment('站点id'));
        $table->save();
    }
}
