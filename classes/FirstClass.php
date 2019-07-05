<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午3:11
 */

namespace classes;

use app\http\exceptions\AjaxException;
use app\http\exceptions\RedirectException;
use app\member\model\MemberGradeModel;
use app\substation\model\SubstationModel;
use think\Db;
use think\Model;
use think\Validate;

class FirstClass
{
    /**
     * 分页返回数据
     *
     * @param \think\Model $model
     * @param array $other
     * @return array
     */
    protected function page($model, $other = [])
    {
        $where = isset($other['where']) ? $other['where'] : [];//筛选
        $whereIn = isset($other['whereIn']) ? $other['whereIn'] : [];//筛选
        $order_name = isset($other['order_name']) ? $other['order_name'] : 'created_at';//排序字段
        $order_type = isset($other['order_type']) ? $other['order_type'] : 'desc';//排序类型
        $column = isset($other['column']) ? $other['column'] : '*';//查询字段
        //页码
        if (isset($other['page'])) {

            $page = $other['page'];
        } else {

            $page = (int)input('page');
            $page = empty($page) ? 1 : $page;
        }

        //单页条数
        if (isset($other['limit'])) {

            $limit = $other['limit'];
        } else {

            $limit = (int)input('limit');
            $limit = empty($limit) ? 20 : $limit;
        }

        $model = self::others($model, $other);

        if (isset($other['substation'])) $model = self::substation($model);

        foreach ($whereIn as $k => $v) {
            $model = $model->whereIn($k, $v);
        }

        //计算数据总数
        $number = $model->where($where)->count();

        //获取所有数据
        $data = $model->where($where)->limit($limit)->page($page)->order($order_name, $order_type)->column($column);

        //处理数据
        $i = 0;
        $result = [];
        foreach ($data as $v) {
            $result[$i] = $v;
            $i++;
        }

        //返回格式
        return [
//            'current_page' => $number == 0 ? 0 : $page,
//            'first_page' => $number == 0 ? 0 : 1,
//            'last_page' => $number == 0 ? 0 : ceil($number / $limit),
            'total' => $number,
            'message' => $result,
        ];
    }

    private function others(Model $model, $other)
    {
        if (isset($other['alias'])) $model = $model->alias($other['alias']);
        if (isset($other['leftJoin'])) $model = $model->leftJoin($other['leftJoin'][0], $other['leftJoin'][1]);

        return $model;
    }

    private function substation(Model $model)
    {
        $test = $model->find();

        if (isset($test['substation'])) {

            $substation = request()->get('the_substation');

            if (($substation == 'all') || is_null($substation)) {

                $sub = new SubstationModel();
                $sub = $sub->where('id', '=', SUBSTATION)
                    ->whereOr('pid', '=', SUBSTATION)
                    ->whereOr('top', '=', SUBSTATION)
                    ->column('id');

                if (SUBSTATION == '0')$sub[] = '0';

            } /*elseif (!is_null($substation)) {

                $sub = $substation;
            } */else {

                $sub = $substation;
                /*if (SUBSTATION != '0') {
                    $sub = new SubstationModel();
                    $sub = $sub->where('id', '=', SUBSTATION)->column('id');
                } else {

                    $sub = [0];
                }*/
            }

            $model = $model->whereIn('substation', $sub);
        }

        return $model;
    }

    /**
     * 重定向报错
     *
     * @param string $url
     * @param string $errors
     * @throws RedirectException
     */
    protected function redirect_exception($url = '', $errors = '')
    {
        Db::rollback();

        $result = [
            'url' => $url,//跳转路由
            'message' => $errors//提示代码
        ];

        throw new RedirectException(json_encode($result));
    }

    /**
     * ajax报错
     *
     * @param $code
     * @param $error
     * @throws AjaxException
     */
    protected function ajax_exception($code, $error)
    {
        Db::rollback();

        $result = [
            'status' => 'fails',
            'code' => $code,
            'message' => $error,
        ];

        throw new AjaxException(json_encode($result));
    }

    /**
     * 手动验证，返回空array即为通过
     *
     * @param array $data
     * @param array $rule
     * @param array $message
     * @param array $file
     * @return array
     */
    protected function validator($data = [], $rule = [], $message = [], $file = [])
    {
        //没有验证条件，直接返回空数组
        if (empty($rule)) return [];

        //初始化验证器
        $validator = new Validate($rule, $message, $file);

        //判断验证是否通过
        if (!$validator->check($data)) {

            //否

            $errors = $validator->getError();

            //返回错误描述
            return $errors;
        }

        //返回空
        return null;
    }
}