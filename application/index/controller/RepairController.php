<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/27
 * Time: 下午5:54
 */

namespace app\index\controller;

use app\repair\model\RepairModel;
use classes\index\RepairClass;
use think\Request;

class RepairController extends \app\http\controller\IndexController
{
    private $class;

    public function __construct()
    {
        parent::__construct();

        $this->class = new RepairClass();
    }

    //充值页面
    public function getIndex()
    {
        $result = [
            'choice' => '/repair-list',
            'classes' => $this->class->index(),
            'no' => '1',
        ];
//dump($result);
//exit;
        return parent::view('work', $result);
    }

    //充值页面
    public function getDefault()
    {
        $result = [
            'choice' => '/repair-list',
            'classes' => $this->class->index(),
            'default' => $this->class->repair_default(input('id')),
        ];
//dump($result);
//exit;
        return parent::view('work-list', $result);
    }

    public function getStore()
    {
        $result = [
            'choice' => '/repair-list',
            'classes' => $this->class->index(),
            'member' => $this->class->member(),
        ];

        return parent::view('work-create', $result);
    }

    public function postStore(Request $request)
    {
        $this->class->validator_save($request);
        $this->class->save($request);
        return parent::success('repair-my');
    }

    public function getMy()
    {
        $result = $this->class->my();

        return parent::view('work-mgr', $result);
    }

    public function getMyTable()
    {
        $result = $this->class->my_table();

        return parent::tables($result);
    }

    public function getRepair()
    {
        $class = new \classes\repair\RepairClass();

        $result = [
            'self' => $class->read(input('id')),
        ];

        return parent::view('work-detail', $result);
    }

    public function getNote()
    {
        $class = new \classes\repair\RepairClass();

        $result = $class->get_note(input('id'));

        return parent::tables($result);
    }

    public function postNote(Request $request)
    {
        $class = new RepairClass();

        $class->validator_note($request);

        $class->note($request);

        return parent::success();
    }

    public function getSuccess()
    {
        $class = new RepairClass();

        $class->success();

        return self::getMy();
    }
}