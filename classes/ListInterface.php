<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/10/17
 * Time: 下午5:48
 */

namespace classes;

use think\Request;

interface ListInterface
{
    //初始化模型
    public function __construct();

    //列表页面
    public function index();

    //添加页面
    public function create();

    //保存数据
    public function save(Request $request);

    //详情页面
    public function read($id);

    //编辑页面
    public function edit($id);

    //更新数据
    public function update($id,Request $request);

    //删除数据
    public function delete($id);

    //保存数据验证
    public function validator_save(Request $request);

    //编辑数据验证
    public function validator_update($id,Request $request);

    //删除数据验证
    public function validator_delete($id);
}