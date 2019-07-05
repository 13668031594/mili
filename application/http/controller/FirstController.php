<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/21
 * Time: 下午3:32
 */

namespace app\http\controller;


use app\substation\model\SubstationModel;
use classes\AdminClass;

class FirstController
{
    protected $url;//文件夹与路由路径变量

    /**
     * ajax成功
     *
     * @param string $url
     * @param string $success
     * @param array $other
     * @return \think\response\Json
     */
    protected function success($url = '', $success = '操作成功', $other = [])
    {
        $result = [
            'status' => 'success',
            'url' => '/' . $this->url . $url,
            'message' => $success,
        ];

        return json(array_merge($result, $other));
    }

    /**
     * 跳转页面
     *
     * @param string $view
     * @param array $data
     * @return \think\response\View
     */
    protected function view($view = '', $data = [])
    {
        //css与js所在文件夹路径
        $data['src'] = '/static/' . $this->url . '/';

        //初始化errors
        if (!isset($data['errors'])) {

            $data['errors'] = session('errors');
            session('errors', null);
        }

        //初始化success
        if (!isset($data['success'])) {

            $data['success'] = session('success');
            session('success', null);
        }

        $data['sub'] = self::substation();
        $data['the_substation'] = input('the_substation');

        //渲染视图
        return view($view, $data);
    }

    private function substation()
    {
        $class = new AdminClass();
        $ids = $class->my_substation();

        $substation = new SubstationModel();
        $substation = $substation->whereIn('id',$ids)->column('id,name');
        if (SUBSTATION == 0)$substation[0] = '主站';

        $substations = new SubstationModel();
        $substations = $substations->column('id,name');
        $substations[0] = '主站';
        ksort($substations);
        ksort($substation);

        return [
            'array' => $substations,
            'json' => json_encode($substation),
            'array_con' => $substation,
            'is' => $class->is_substation(),
        ];
    }

    /**
     * 列表数据
     *
     * @param array $result
     * @param array $other
     * @return \think\response\Json
     */
    protected function tables($result = [], $other = [])
    {
        if (!empty($other)) $result = array_merge($result, $other);

        $result['status'] = 'success';

        return json($result);
    }
}