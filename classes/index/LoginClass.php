<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/26
 * Time: 下午3:27
 */

namespace classes\index;

use app\index\model\SmsModel;
use app\member\model\MemberGradeModel;
use app\member\model\MemberModel;
use classes\IndexClass;
use classes\member\MemberClass;
use think\Request;

class LoginClass extends IndexClass
{
    public $model;

    public function __construct()
    {
        $this->model = new MemberModel();
    }

    /**
     * 登录字段验证
     */
    public function validator_login(Request $request)
    {
        //验证条件
        $rule = [
            'account|用户名' => 'require|max:20|min:6',
            'password|密码' => 'require|max:20|min:6',
        ];

        //验证
        $result = parent::validator($request->post(), $rule);

        //有错误报告则报错
        if (!is_null($result)) parent::back($result);
    }

    /**
     * 登录方法，登录成功返回member模型
     *
     * @param Request $request
     * @return MemberModel|array|null|\PDOStatement|string|\think\Model
     */
    public function login(Request $request)
    {
        //初始化模型
        $member = new MemberModel();

        //尝试获取管理员信息
        $member = $member->where('account|phone', '=', $request->post('account'))
            ->where('password', '=', md5($request->post('password')))
            ->where('substation', '=', SUBSTATION)
            ->find();

        //获取失败，账密错误
        if (is_null($member)) parent::back('账号或密码错误' . SUBSTATION);

//        if ($member['substation'] != SUBSTATION) parent::back('您在本站还未注册账号');

        //返回管理员信息
        return $member;
    }

    /**
     * 修改登录信息
     *
     * @param \think\Model $member
     */
    public function refresh_member(\think\Model $member)
    {
        $session = [
            'id' => $member->id,//管理员id
            'time' => time() + config('young.index_login_time'),//登录持续时间
            'login_ass' => md5(time() . rand(100, 999))//登录密钥
        ];

        session('member', $session);//保存登录信息

        $member->login_times += 1;
        $member->login_time = date('Y-m-d H:i:s');
        $member->login_ip = $_SERVER["REMOTE_ADDR"];
        $member->login_ass = $session['login_ass'];
        $member->save();
    }

    /**
     * 注销
     */
    public function logout()
    {
        session('member', null);
    }

    /**
     * 注册验证
     *
     * @param Request $request
     */
    public function validator_reg(Request $request)
    {
        $rule = [
            'referee|推荐号' => 'min:5|max:20',
            'account|用户名' => 'require|min:6|max:20|regex:^\d{6,20}$',
            'phone|手机号码' => 'require|length:11',
            'pass|密码' => 'require|min:6|max:20',
//            'again|确认密码' => 'require|min:6|max:20',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        $test = new MemberModel();
        $test = $test->where('substation', '=', SUBSTATION)->where(function ($query) use ($request) {
            $query->where('phone', '=', $request->post('phone'))
                ->whereOr('account', '=', $request->post('account'));
        })->find();
        if (!is_null($test)) parent::ajax_exception(000, '你在本站注册过会员了');

        self::validator_phone($request);//短信验证

//        if ($request->post('pass') != $request->post('again')) parent::ajax_exception(000, '确认密码有误');
    }

    /**
     * 注册
     *
     * @param Request $request
     */
    public function reg(Request $request)
    {
        $grade = new MemberGradeModel();
        $grade = $grade->where('change', '=', 'fail')->find();
        if (is_null($grade)) parent::ajax_exception(000, '注册失败，请联系管理员');

        $class = new MemberClass();

        $model = new MemberModel();
        $model = $class->referee_add($model, $request);
        $model->phone = $request->post('phone');
        $model->account = $request->post('account');
        $model->nickname = substr($model->phone, 0, 3) . '****' . substr($model->phone, 7);
        $model->password = md5($request->post('pass'));
        $model->pay_pass = md5($request->post('pass'));
        $model->created_type = 1;
        $model->grade_id = $grade['id'];
        $model->grade_name = $grade['name'];
        $model->created_at = date('Y-m-d H:i:s');
        $model->substation = SUBSTATION;
        $model->save();
    }

    /**
     * 找回密码验证
     *
     * @param Request $request
     */
    public function validator_reset(Request $request)
    {
        $rule = [
            'code|手机验证码' => 'min:5|max:20',
            'phone|手机号码' => 'require|length:11',
            'pass|密码' => 'require|min:6|max:20',
            'again|确认密码' => 'require|min:6|max:20',
        ];

        $result = parent::validator($request->post(), $rule);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        $test = new MemberModel();
        $test = $test->where('substation', '=', SUBSTATION)->where('phone', '=', $request->post('phone'))->find();
        if (is_null($test)) parent::ajax_exception(000, '你在本站尚未注册过会员');

        self::validator_phone($request);//短信验证

        if ($request->post('pass') != $request->post('again')) parent::ajax_exception(000, '确认密码有误');
    }

    /**
     * 重置密码
     *
     * @param Request $request
     */
    public function reset(Request $request)
    {
        $model = new MemberModel();
        $model = $model->where('phone', '=', $request->post('phone'))->where('substation', '=', SUBSTATION)->find();
        if (is_null($model)) parent::ajax_exception(000, '会员不存在');
        $model->password = md5($request->post('pass'));
        $model->save();
    }


    /**
     * 发送验证码前验证
     *
     * @param $phone
     * @param $time
     */
    public function validator_sms_reg($phone, $time)
    {
        $term = [
            'phone' => 'require|length:11',//联系电话，必填
        ];

        $errors = [
            'phone.require' => '请输入联系电话',
            'phone.length' => '请输入11位的联系电话',
            'phone.unique' => '该电话号码已经注册过账号，请更换联系电话或填写账号信息',
        ];

        //参数判断
        $result = parent::validator(['phone' => $phone], $term, $errors);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        $test = new MemberModel();
        $test = $test->where('substation', '=', SUBSTATION)->where('phone', '=', $phone)->find();
        if (!is_null($test)) parent::ajax_exception(000, '你在本站已经注册过会员了');

        //验证上次发送验证码时间
        self::validator_sms_time($phone, $time);
    }

    /**
     * 发送验证码前验证
     *
     * @param $phone
     * @param $time
     */
    public function validator_sms_reset($phone, $time)
    {
        $term = [
            'phone' => 'require|length:11',//联系电话，必填
        ];

        $errors = [
            'phone.require' => '请输入联系电话',
            'phone.length' => '请输入11位的联系电话',
            'phone.unique' => '账号不存在',
        ];

        //参数判断
        $result = parent::validator(['phone' => $phone], $term, $errors);
        if (!is_null($result)) parent::ajax_exception(000, $result);

        $test = new MemberModel();
        $test = $test->where('substation', '=', SUBSTATION)->where('phone', '=', $phone)->find();
        if (is_null($test)) parent::ajax_exception(000, '你在本站尚未注册过会员');

        //验证上次发送验证码时间
        self::validator_sms_time($phone, $time);

        //验证会员存在
        $test = new \app\member\model\MemberModel();
        $test = $test->where('phone', '=', $phone)->find();
        if (is_null($test)) parent::ajax_exception(000, '会员不存在');
    }

    /**
     * 验证上次发送验证码时间
     *
     * @param $phone
     * @param $time
     */
    public function validator_sms_time($phone, $time)
    {
        //获取该电话号码最新的验证码
        $test = new SmsModel();
        $test_code = $test->where('phone', '=', $phone)->order('created_at', 'desc')->find();

        //没有找到数据
        if (!is_null($test_code)) {

            //比较是否超时
            if ($time < ($test_code->end - 240)) {

                $end = $test_code->end - $time - 240;

                parent::ajax_exception('001', $end);
            }
        }
    }

    /**
     * 删除所有超时验证码
     *
     * @param $time
     */
    public function delete_sms($time)
    {
        $model = new SmsModel();
        $model->where('end', '<', $time)->delete();
    }

    /**
     * 发送短信
     *
     * @param $phone
     * @param $time
     * @param string $templateCode
     * @return int
     */
    public function send_sms($phone, $time, $templateCode = '『账号注册』')
    {
        //初始化短信类
        $class = new \classes\vendor\SmsClass();

        //生成验证码
        $code = rand(10000, 99999);

        //发送短信
        $result = $class->sendSms($phone, $code, $templateCode);

        //判断回执
        if (!isset($result->Message)) parent::ajax_exception(000, '请刷新重试(message)');

        //判断是否成功
        if ($result->Message != 'OK') {

            //根据状态吗报错
            switch ($result->Code) {

                case 'isv.BUSINESS_LIMIT_CONTROL':
                    $error = '每小时只能发送5条短信';
                    break;
                case 'isv.MOBILE_NUMBER_ILLEGAL':
                    $error = '非法手机号';
                    break;
                case 'isv.MOBILE_COUNT_OVER_LIMIT':
                    //账户不存在
                    $error = '手机号码数量超过限制';
                    break;
                default:
                    $error = '请刷新重试（' . $result->Code . '）';
                    break;
            }

            parent::ajax_exception(000, $error);
        }

        //生成结束时间
        $end = $time + 300;

        //添加到数据库
        $model = new SmsModel();
        $model->phone = $phone;
        $model->end = $end;
        $model->code = $code;
        $model->created_at = date('Y-m-d H:i:s');
        $model->save();

        //清楚过期短信
        self::delete_sms($time);

        return $time + 60;
    }

    /**
     * 验证短信
     *
     * @param Request $request
     */
    public function validator_phone(Request $request)
    {
        $phone = $request->post('phone');
//        $phone = '13668031594';
        $code = $request->post('code');

        //获取该电话号码最新的验证码
        $test = new SmsModel();
        $test_code = $test->where('phone', '=', $phone)->order('created_at', 'desc')->find();

        //没有找到数据
        if (is_null($test_code)) parent::ajax_exception(000, '验证码已经失效');

        //当前时间戳
        $now_time = time();

        //比较是否超时
        if ($now_time > $test_code->end) parent::ajax_exception(000, '验证码已经失效,请重新获取');

        //比较验证码是否正确
        if ($code != $test_code->code) parent::ajax_exception(000, '验证码输入错误');
    }
}