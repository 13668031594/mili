<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午3:09
 */

namespace classes\master;

use app\master\model\MasterModel;
use app\substation\model\SubstationModel;
use classes\AdminClass;
use think\Request;

class LoginClass extends AdminClass
{
    /**
     * 登录字段验证
     */
    public function validator_login(Request $request)
    {
        $master = self::test_master();

        if (!is_null($master)) parent::ajax_exception(000, '请勿重复登录');

        //验证条件
        $rule = [
            'account|账号' => 'require|max:20|min:6',
            'password|密码' => 'require|max:20|min:6',
            'code|验证码' => 'require|captcha',
        ];

        //验证
        $result = parent::validator($request->post(), $rule);

        //有错误报告则报错
        if (!is_null($result)) parent::ajax_exception(000, $result);
    }

    /**
     * 登录方法，登录成功返回master模型
     *
     * @param Request $request
     * @return MasterModel|array|null|\PDOStatement|string|\think\Model
     */
    public function login(Request $request)
    {
        //初始化模型
        $master = new MasterModel();

        //尝试获取管理员信息
        $masters = $master->where('account', '=', $request->post('account'))
            ->where('password', '=', md5($request->post('password')))
            ->find();

        //获取失败，账密错误
        if (is_null($masters)) parent::ajax_exception(000, '账号或密码错误');

        //安全路径
        $safe_path = include 'safe_path.php';

        //获取访问域名
        $localhost = $_SERVER['SERVER_NAME'];

        if (!in_array($localhost, $safe_path)) {

            //该域名不是主站域名，寻找站点id
            $substation_model = new SubstationModel();
            $substation_model = $substation_model->where('localhost', '=', $localhost)->where('status', '=', 'on')->find();

            //没找到该分站或者该分站与管理员分站不符
            if (is_null($substation_model) || (($substation_model['id'] != $masters['substation']) &&
                    ($substation_model['pid'] != $masters['substation']) &&
                    ($substation_model['top'] != $masters['substation']))) parent::ajax_exception(000, '你无权登录此站点');
        } elseif (($masters['substation'] != 0)) {

            //若该域名为主站域名
            //且管理员并非主站管理员
            parent::ajax_exception(000, '你无权登录此站点');
        }

        //返回管理员信息
        return $masters;
    }

    /**
     * 修改登录信息
     *
     * @param \think\Model $master
     */
    public function refresh_master(\think\Model $master)
    {
        $session = [
            'id' => $master->id,//管理员id
            'time' => time() + config('young.admin_login_time'),//登录持续时间
            'login_ass' => md5(time() . rand(100, 999))//登录密钥
        ];

        session('master', $session);//保存登录信息
//dd('master_' . $_SERVER['SERVER_NAME']);
        $master->login_times += 1;
        $master->login_time = date('Y-m-d H:i:s');
        $master->login_ip = $_SERVER["REMOTE_ADDR"];
        $master->login_ass = $session['login_ass'];
        $master->save();
    }

    /**
     * 注销
     */
    public function logout()
    {
        session('master', null);
    }

    private function test_master()
    {
        //尝试获取session中的master信息
        $master = session('master');

        //验证session中的信息格式与过期时间
        if (is_null($master) || !is_array($master) || !isset($master['id']) || !isset($master['login_ass']) || !isset($master['time']) || ($master['time'] < time())) return null;

        //初始化管理员模型
        $masters = new MasterModel();

        //尝试获取管理员资料
        $masters = $masters->where('id', '=', $master['id'])->find();

        //没有获取到管理员资料，跳转至登录页面
        if (is_null($masters)) return null;

        //登录密钥验证
        if ($master['login_ass'] != $masters->login_ass) return null;

        //更新操作时间
        $master['time'] = time() + config('young.admin_login_time');

        session('master', $master);

        return $masters;
    }
}