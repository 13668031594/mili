<?php

namespace app\repair\controller;

use app\http\controller\AdminController;
use app\repair\model\RepairModel;
use classes\repair\RepairClass;
use think\Db;
use think\Request;

class RepairController extends AdminController
{
    private $class;

    public function __construct(Request $request = null)
    {
        $this->class = new RepairClass();
    }

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function getIndex()
    {
        $repair_class = $this->class->classes();

        return parent::view('repair', ['repair_class' => $repair_class]);
    }

    public function getTable()
    {
        $result = $this->class->index();

        return parent::tables($result);
    }

    public function getStatus(Request $request)
    {
        Db::startTrans();

        $this->class->status($request);

        Db::commit();

        return parent::success('/admin/repair/index');
    }

    public function getRead()
    {
        $id = input('id');

        $self = $this->class->read($id);


        $status = new RepairModel();

        $result = [
            'self' => $self,
//            'note' => $note,
            'status' => $status->status(),
        ];

        return parent::view('repair_note', $result);
    }

    public function postNote(Request $request)
    {
        $this->class->validator_note($request);

        $this->class->note($request);

        return parent::success('/admin/repair/index');
    }

    public function getNote()
    {
        $note = $this->class->get_note(input('id'));

        return parent::tables($note);
//        return parent::success('/admin/repair/index');
    }
}
