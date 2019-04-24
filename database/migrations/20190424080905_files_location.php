<?php

use think\migration\Migrator;
use think\migration\db\Column;

class FilesLocation extends Migrator
{
    public function up()
    {
        $table = $this->table('files_location');
        $table->setId('id');
        $table->addColumn(Column::string('master')->setNullable()->setComment('归属'));
        $table->addColumn(Column::string('location')->setComment('路径'));
        $table->addColumn(Column::timestamp('created_at')->setNullable()->setComment('创建时间'));
        $table->save();
    }

    public function down()
    {
        $this->dropTable('files_location');
    }
}
