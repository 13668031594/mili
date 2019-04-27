<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/4/25
 * Time: 下午3:10
 */

namespace classes\repair;

use app\files\model\FilesLocationModel;
use app\repair\model\RepairClassModel;
use app\repair\model\RepairModel;
use app\repair\model\RepairNoteModel;
use classes\AdminClass;
use think\Db;
use think\Request;

class RepairClass extends AdminClass
{
    public $model;

    public function __construct()
    {
        $this->model = new RepairModel();
    }

    public function classes()
    {
        $model = new RepairClassModel();
        $result = $model->order('sort', 'desc')->column('id,name');

        return $result;
    }

    public function index()
    {
        $where = [];

        $repair = input('repair_class');
        $account = input('account');
        $status = input('status');
        $keyword = input('keyword');
        $keywordType = input('keywordType');
        if (!empty($keyword)) {
            switch ($keywordType) {
                case '0':
                    $where[] = ['phone', 'like', '%' . $keyword . '%'];
                    break;
                case '1':
                    $where[] = ['order_number', 'like', '%' . $keyword . '%'];
                    break;
                default:
                    break;
            }
        }
        if (!empty($account)) $where[] = ['account|phone', 'like', "%" . $account . "%"];
        if (!empty($status)) $where[] = ['status', '=', $status];
        if (!empty($repair)) $where[] = ['repair_class_id', '=', $repair];

        $other = [
            'where' => $where,
            'column' => '*',
            'substation' => '1',
        ];

        $result = parent::page($this->model, $other);

        return $result;
    }

    public function read($id)
    {
        $model = $this->model->where('id', '=', $id)->find();

        if (is_null($model)) parent::redirect_exception('/admin/repair/index', '工单不存在');

        $file = new FilesLocationModel();

        $self = $model->getData();

        $self['images'] = array_values($file->where('master', '=', 'repair_images_' . $id)->column('*'));
        $self['files'] =  array_values($file->where('master', '=', 'repair_files_' . $id)->column('*'));

        return $self;
    }

    public function status(Request $request)
    {
        Db::startTrans();

        $id = $request->get('id');

        //订单获取
        $order = $this->model->where('id', '=', $id)->find();

        //获取成功
        if (is_null($order)) parent::ajax_exception(0, '工单不存在');

        //未锁定
        if ($order->status == '40') parent::ajax_exception(0, '工单已锁定');

        //新状态获取
        $status = input('value');

        //合法的状态码
        $array = [20, 30];

        //状态码合法
        if (!in_array($status, $array)) parent::ajax_exception(0, '状态错误');

        //获取管理员
        $master = parent::master();

        //修改订单状态
        $order->status = $status;
        $order->updated_at = date('Y-m-d H:i:s');
        $order->save();

        Db::commit();
    }

    public function validator_note(Request $request)
    {
        $rule = [
            'id|工单id' => 'require',
            'content|回复内容' => 'require|length:1,255',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    public function note(Request $request)
    {
        Db::startTrans();

        $repair = new RepairModel();
        $repair = $repair->where('id', '=', $request->post('id'))->find();
        if (is_null($repair)) parent::ajax_exception(000, '工单不存在');

        $model = new RepairNoteModel();
        $model->uid = $repair->uid;
        $model->repair_id = $repair->id;
        $model->content = $request->post('content');
        $model->type = 0;
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

        $images = $request->post('imageId');
        $files = $request->post('attachId');
        $file = new FilesLocationModel();
        $file->whereIn('id', $images)->update(['master' => 'repair_note_images_' . $model->id]);
        $file->whereIn('id', $files)->update(['master' => 'repair_note_files_' . $model->id]);

        Db::commit();
    }

    public function get_note($id)
    {
        $model = new RepairNoteModel();
        $model = parent::page($model,['where' => [['repair_id','=',$id]]]);

        $file = new FilesLocationModel();
        foreach ($model['message'] as &$v){

            $v['files'] = array_values($file->where('master','=','repair_note_files_' . $v['id'])->column('*'));
            $v['images'] = array_values($file->where('master','=','repair_note_images_' . $v['id'])->column('*'));
        }

        return $model;
    }
}