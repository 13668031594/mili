<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2019/4/25
 * Time: 下午4:20
 */

namespace classes\index;


use app\files\model\FilesLocationModel;
use app\repair\model\RepairClassModel;
use app\repair\model\RepairDefaultModel;
use app\repair\model\RepairModel;
use app\repair\model\RepairNoteModel;
use classes\files\FilesLocationClass;
use think\Db;
use think\Request;

class RepairClass extends \classes\IndexClass
{
    /**
     * 首页
     *
     * @return RepairClassModel|array
     */
    public function index()
    {
        $classes = new RepairClassModel();
        $classes = $classes->where('show', '=', 'on')->order('sort', 'desc')->column('*', 'id');

        $default = new RepairDefaultModel();
        foreach ($classes as $k => &$v) {

            $v['child'] = $default->where('repair_class_id', '=', $k)->where('show', '=', 'on')->order('sort', 'desc')->column('id,title');
        }

        return $classes;
    }

    /**
     * 推荐方案
     *
     * @param $id
     * @return RepairDefaultModel|mixed
     */
    public function repair_default($id)
    {
        $default = new RepairDefaultModel();
        $default = $default->find($id)->getData();

        return $default;
    }

    /**
     * 添加工单
     *
     * @param Request $request
     */
    public function validator_save(Request $request)
    {
        $rule = [
            'phone|联系电话' => 'require|max:11',
            'content|问题描述' => 'require|min:1|max:20000',
            'repair_class|工单类型' => 'require',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    /**
     * 保存工单
     *
     * @param Request $request
     */
    public function save(Request $request)
    {
//        dump($request->file());
//        exit;
        $member = parent::member();
        $class = new RepairClassModel();
        $class = $class->where('id', '=', $request->post('repair_class'))->find();
        if (is_null($class)) parent::ajax_exception(000, '工单类型不存在');

        $model = new RepairModel();
        $model->uid = $member['uid'];
        $model->repair_class_id = $class->id;
        $model->repair_class_name = $class->name;
        $model->nickname = $member['nickname'];
        $model->phone = $request->post('phone');
        $model->content = $request->post('content');
        $model->substation = SUBSTATION;
        $model->created_at = date('Y-m-d H:i:s');
        $model->updated_at = $model->created_at;
        $model->save();
        $model->order_no = 'R' . (375982 + $model->id);
        $model->save();

        $files = $request->file();
        $file = new FilesLocationClass();
        $file->images($files['images'], 'repair_images_' . $model->id);
        $file->files($files['files'], 'repair_files_' . $model->id);
    }

    public function my()
    {
        $member = parent::member();
        $model = new RepairModel();
//dump($member);
//exit;
        $result = [
            '_10' => $model->where('uid', '=', $member['id'])->where('status', '=', 10)->count(),
            '_20' => $model->where('uid', '=', $member['id'])->where('status', '=', 20)->count(),
            '_30' => $model->where('uid', '=', $member['id'])->where('status', '=', 30)->count(),
            '_40' => $model->where('uid', '=', $member['id'])->where('status', '=', 40)->count(),
        ];

        return $result;
    }

    public function my_table()
    {
        $member = parent::member();

        $where = [
            ['uid', '=', $member['id']]
        ];

        $status = input('status');

        switch ($status) {
            case '20':
                $where[] = ['status', '=', 20];
                break;
            case '30':
                $where[] = ['status', '=', 30];
                break;
            case '40':
                $where[] = ['status', '=', 40];
                break;
            case '50':
                break;
            default:
                $where[] = ['status', '=', 10];
                break;
        }

        $other = [
            'order_name' => 'updated_at',
            'where' => $where,
        ];

        $model = new RepairModel();

        $result = parent::page($model, $other);

        return $result;
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
        if ($repair->status >= 30) {

            $repair->status = 10;
            $repair->save();
        }

        $model = new RepairNoteModel();
        $model->uid = $repair->uid;
        $model->repair_id = $repair->id;
        $model->content = $request->post('content');
        $model->type = 1;
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

        $images = $request->post('imageId');
        $files = $request->post('attachId');
        $file = new FilesLocationModel();
        $file->whereIn('id', $images)->update(['master' => 'repair_note_images_' . $model->id]);
        $file->whereIn('id', $files)->update(['master' => 'repair_note_files_' . $model->id]);

        Db::commit();
    }

    public function success()
    {
        $repair = new RepairModel();
        $repair = $repair->where('id', '=',input('id'))->find();
        if (is_null($repair)) parent::ajax_exception(000, '工单不存在');
        $repair->status = 40;
        $repair->save();
    }
}