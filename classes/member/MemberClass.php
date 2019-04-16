<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/24
 * Time: 下午1:39
 */

namespace classes\member;

use app\member\model\MemberGradeModel;
use app\member\model\MemberModel;
use app\member\model\MemberRecordModel;
use classes\AdminClass;
use classes\ListInterface;
use think\Db;
use think\Request;

class MemberClass extends AdminClass implements ListInterface
{
    public $member;
    public $record;

    public function __construct()
    {
        $this->member = new MemberModel();
        $this->record = new MemberRecordModel();
    }

    public function index()
    {
        $where = [];

        $account = input('account');
        $status = input('status');
        $grade = input('grade');

        if (!empty($account)) $where[] = ['account|phone', 'like', "%" . $account . "%"];
        if (!empty($status)) $where[] = ['status', '=', $status];
        if (!empty($grade)) $where[] = ['grade_id', '=', $grade];

        $column = 'id,status,grade_name,account,phone,nickname,remind,commis,created_at,recharge';

        return parent::page($this->member, ['where' => $where, 'column' => $column]);
    }

    public function create()
    {
        $model = new MemberGradeModel();

        $grade = $model->order('sort', 'desc')->column('id,name');

        return $grade;
    }

    public function save(Request $request)
    {
        $grade = new MemberGradeModel();
        $grade = $grade->where('id', '=', $request->post('grade'))->find();
        if (is_null($grade)) parent::ajax_exception(000, '等级不存在');

        $model = self::referee_add($this->member, $request);
        $model->phone = $request->post('phone');
        $model->account = $request->post('account');
        $model->nickname = $request->post('nickname');
        $model->password = md5($request->post('password'));
        $model->pay_pass = md5($request->post('pay_pass'));
        $model->created_type = 1;
        $model->grade_id = $grade['id'];
        $model->grade_name = $grade['name'];
        $model->status = $request->post('status');
        $model->bank_no = $request->post('bank_no');
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();
    }

    public function read($id)
    {
        $member = $this->member->where('id', '=', $id)->find();

        if (is_null($member)) parent::redirect_exception('/admin/member/index', '会员不存在');

        return $member->getData();
    }

    public function edit($id)
    {
        return self::read($id);
    }

    public function update($id, Request $request)
    {
        $model = $this->member->where('id', '=', $id)->find();

        if (is_null($model)) parent::redirect_exception('/admin/member/index', '会员不存在');

        $grade = new MemberGradeModel();
        $grade = $grade->where('id', '=', $request->post('grade'))->find();
        if (is_null($grade)) parent::ajax_exception(000, '等级不存在');

        $model->nickname = $request->post('nickname');
        if ($request->post('password') != 'w!c@n#m$b%y^') $model->password = md5($request->post('password'));
        if ($request->post('pay_pass') != 'w!c@n#m$b%y^') $model->pay_pass = md5($request->post('pay_pass'));
        $model->grade_id = $grade['id'];
        $model->grade_name = $grade['name'];
        $model->status = $request->post('status');
        $model->bank_no = $request->post('bank_no');
        $model->updated_at = date('Y-m-d H:i:s');
        $model->save();
    }

    public function delete($id)
    {
        $this->member->whereIn('id', $id)->delete();
        $this->record->whereIn('member_id', $id)->delete();
    }

    public function validator_save(Request $request)
    {
        $rule = [
            'referee|推荐号' => 'min:5|max:20',
            'phone|联系电话' => 'require|length:11|unique:member,phone',
            'account|账号' => 'require|min:6|max:20|regex:^\d{6,20}$|unique:member,account',
            'nickname|昵称' => 'require|min:2|max:48',
            'password|密码' => 'require|min:6|max:20',
            'pay_pass|支付密码' => 'require|min:6|max:20',
            'status|状态' => 'require|in:' . implode(',', array_keys(config('member.status'))),
            'grade|身份' => 'require',
            'bank_no|收款账号' => 'max:30'
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(502, $result);
    }

    public function validator_update($id, Request $request)
    {
        $rule = [
            'nickname|昵称' => 'require|min:2|max:48',
            'password|密码' => 'require|min:6|max:20',
            'pay_pass|支付密码' => 'require|min:6|max:20',
            'status|状态' => 'require|in:' . implode(',', array_keys(config('member.status'))),
            'grade|身份' => 'require',
            'bank_no|收款账号' => 'max:30'
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(502, $result);
    }

    public function validator_delete($id)
    {
        $test = new MemberModel();
        $test = $test->whereIn('id', $id)->where('status', '<>', 2)->find();
        if (!is_null($test)) parent::ajax_exception(000, '只能删除停用的会员');
    }

    /**
     * 添加上级信息
     *
     * @param MemberModel $member
     * @param Request $request
     * @return MemberModel
     */
    public function referee_add(MemberModel $member, Request $request)
    {
        $referee_account = $request->post('referee');
        if (empty($referee_account)) return $member;

        $test = new MemberModel();
        $referee = $test->where('id', '=', $request->post('referee'))->find();
        if (is_null($referee)) parent::ajax_exception(503, '上级不存在');

        $referee = $referee->getData();

        $families = empty($referee['families']) ? $referee['id'] : ($referee['families'] . ',' . $referee['id']);

        $member->families = $families;//上级缓存
        $member->referee_id = $referee['id'];//上级id
        $member->referee_account = $referee['account'];//上级账号
        $member->referee_phone = $referee['phone'];//上级手机号
        $member->referee_nickname = $referee['nickname'];//上级昵称
        $member->level = $referee['level'] + 1;//自身层级

        return $member;
    }

    public function validator_wallet(Request $request)
    {
        $rule = [
            'type' => 'require',
            'number' => 'require|integer|between:-1000000,1000000',
        ];

        $file = [
            'type' => '充值类型',
            'number' => '充值数量'
        ];

        $result = parent::validator($request->post(), $rule, [], $file);
        if (!is_null($result)) parent::ajax_exception(505, $result);
    }

    public function wallet(Request $request)
    {
        Db::startTrans();

        //寻找会员模型
        $member = $this->member->where('id', '=', $request->post('id'))->find();

        //判断
        if (is_null($member)) parent::ajax_exception(506, '会员不存在');

        //获取变化数值
        $number = $request->post('number');

        //初始化会员记录
        $record = new MemberRecordModel();
        $record->member_id = $member->id;
        $record->account = $member->account;
        $record->nickname = $member->nickname;
        if ($number < 0) {

            $record->content = '管理员扣除了您的『';
        } else {

            $record->content = '管理员为您充值『';
        }

        //按类型充值
        switch ($request->post('type')) {
            case '0':

                $member->remind += $number;

                $record->remind = $number;
                $record->content .= '余额';

                $member->remind_all += $number;

                break;
            case '1':

                $member->commis += $number;
                $member->commis_all += $number;

                $record->commis = $number;
                $record->content .= '佣金';

                break;
            default:
                parent::ajax_exception(507, '充值类型错误');
                break;
        }

        $number = ($number < 0) ? (0 - $number) : $number;

        $record->commis_now = $member->commis;
        $record->commis_all = $member->commis_all;
        $record->remind_now = $member->remind;
        $record->remind_all = $member->remind_all;
        $record->type = 10;
        $record->content .= '』：' . $number;
        $record->created_at = date('Y-m-d H:i:s');

        $record->save();
        $member->save();

        Db::commit();

    }

    public function record(Request $request)
    {
        $where = [];

        $where['member_id'] = ['=', $request->get('id')];

        switch ($request->get('type')) {
            case '1':
                $where['commis'] = ['<>', 0];
                break;
            case '2':
                $where['remind'] = ['<>', 0];
                break;
            default:
                break;
        }

        $startTime = $request->get('startTime');
        $endTime = $request->get('endTime');

        if (!empty($startTime)) {
            $where[] = ['created_at', '>=', $startTime];
        }
        if (!empty($endTime)) {
            $where[] = ['created_at', '<', $endTime];
        }

        return parent::page($this->record, ['where' => $where]);
    }

    public function record_array()
    {
        return config('member.record');
    }

    public function record_delete($id)
    {
        $this->record->whereIn('id', $id)->delete();
    }

    public function team($member_id)
    {
        //结果数组
        $result = [
            'number' => 0,
            'team' => json_encode([]),
        ];

        //初始化模型
        $model = new MemberModel();

        //获取下级信息
        $team = $model->where('families', 'like', '%' . $member_id . '%')
            ->where('referee_id', '=', $member_id)
            ->column('id,referee_id,nickname');

        //没有下级
        if (count($team) <= 0) return $result;

        $result['number'] = count($team);//下级总数

        //下级结果数组
        $fathers = [];

        foreach ($team as $v) {

            $fathers[$v['referee_id']][] = $v;
        }

        $result['team'] = str_replace('"', "'", json_encode(self::get_tree($member_id, $fathers)));

        return $result;
    }

    public function get_tree($father_id, $team)
    {
        if (!isset($team[$father_id])) return [];

        $result = [];

        foreach ($team[$father_id] as $k => $v) {

            $result[$k]['name'] = $v['nickname'];
//            if (isset($team[$v['id']])) $result[$k]['children'] = self::get_tree($v['id'], $team);
        }

        return $result;
    }
}