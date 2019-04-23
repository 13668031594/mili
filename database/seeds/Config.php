<?php

use think\migration\Seeder;

class Config extends Seeder
{
    private $date;

    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
    public function run()
    {
        $this->date = date('Y-m-d H:i:s');
        \think\Db::query("ALTER TABLE young_member AUTO_INCREMENT = 10000");
        \think\Db::query("ALTER TABLE young_order_send AUTO_INCREMENT = 10000");

        self::master();//初始管理员
//        self::express();//初始快递
        self::member_grade();//初始会员等级

    }

    private function master()
    {
        $master = new \app\master\model\MasterModel();

        $test = $master->find();

        if (is_null($test)) {

            $insert = [
                'id' => '1',
                'nickname' => '超级管理员',
                'account' => 'admins',
                'password' => md5('asdasd123'),
                'created_at' => $this->date,
            ];

            $master->insert($insert);
        }
    }

    private function express()
    {
        $express = new \app\express\model\ExpressModel();

        $test = $express->find();

        if (is_null($test)) {

            $insert = [
                'id' => '1',
                'name' => '邮政',
                'sort' => 50,
                'disable' => 'on',
                'created_at' => $this->date,
            ];

            $express->insert($insert);
        }
    }

    private function member_grade()
    {
        $grade = new \app\member\model\MemberGradeModel();

        $test = $grade->find();

        if (is_null($test)) {

            $insert = [
                'id' => '1',
                'name' => '会员',
                'sort' => 50,
                'amount' => '10',
                'mode' => 'on',
                'change' => 'fail',
                'created_at' => $this->date,
            ];

            $grade->insert($insert);
        }
    }
}