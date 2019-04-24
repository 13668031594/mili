<?php
//use think\Route;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\facade\Route;

/**
 * 后台路由组
 */

Route::group('',function (){

    Route::get('admin/login', 'master/Login/getLogin');//后台登录页面
    Route::post('admin/login', 'master/Login/postLogin');//后台登录方法
    Route::get('admin/', 'master/Login/getIndex')->middleware('master_login');//后台首页
    Route::group('admin', function () {

        Route::get('/', 'master/Login/getIndex');//后台首页
        Route::get('', 'master/Login/getIndex');//后台首页

        //注销登录
        Route::get('logout', 'master/Login/getLogout');

        //管理员
        Route::controller('master', 'master/Master');

        //系统设置
        Route::controller('system', 'system/System');

        //站点栏目
        Route::controller('site', 'system/Site');

        //快递列表
        Route::controller('express', 'express/Express');

        //banner
        Route::controller('banner', 'banner/Banner');

        //广告
        Route::controller('adv', 'adv/Adv');

        //公告
        Route::controller('notice', 'notice/Notice');

        //文章
        Route::controller('article', 'article/Article');

        //会员等级
        Route::controller('member_grade', 'member/MemberGrade');

        //会员
        Route::controller('member', 'member/Member');

        //会员店铺
        Route::controller('store', 'member/MemberStore');

        //商品分类
        Route::controller('goods_class', 'goods/GoodsClass');

        //商品
        Route::controller('goods', 'goods/Goods');

        //充值
        Route::controller('recharge', 'recharge/Recharge');

        //提现
        Route::controller('withdraw', 'withdraw/Withdraw');

        //订单
        Route::controller('order', 'order/Order');

        //发货
        Route::controller('send', 'order/Send');

        //头像列表
        Route::controller('avatar', 'avatar/Avatar');

        //导航管理
        Route::controller('nav', 'nav/Nav');

        //快捷搜索
        Route::controller('link', 'nav/Link');

        Route::controller('bill', 'bill/Bill');

        //分站列表
        Route::controller('substation', 'substation/Substation');

    })->middleware(['master_login']);//验证管理员登录中间件
    /**
     * 后台路由组结束
     */

    /**
     * 前台路由组
     */
//未登录才能访问的权限组，若已登录，重定向到首页页面
    Route::group([], function () {

        Route::get('login', 'index/Login/getLogin');//前台登录页面
        Route::post('login', 'index/Login/postLogin');//前台登录方法
        Route::get('reg', 'index/Login/getReg');//前台注册页面
        Route::post('reg', 'index/Login/postReg');//前台注册方法
        Route::get('regSms/:phone', 'index/Login/getRegSms');//注册验证码发送
        Route::get('reset', 'index/Login/getReset');//前台找回密码页面
        Route::post('reset', 'index/Login/postReset');//前台找回密码方法
        Route::get('resetSms/:phone', 'index/Login/getResetSms');//找回密码验证码发送
    })->middleware('member_logout');//验证会员未登录中间件

//登录后才能访问的权限组，若未登录，重定向到登录页面
    Route::group([], function () {

        Route::get('logout', 'index/Login/getLogout');//前台注销方法

        //订单模块
        Route::get('order', 'index/Order/getOrder');//礼品数据
        Route::get('order-info', 'index/Order/getOrderInfo');//礼品详情
        Route::post('order', 'index/Order/postOrder');//下单
        Route::get('goods-had', 'index/Order/getGoodsHad');//订单页面
        Route::get('goods-had-table', 'index/Order/getGoodsHadTable');//订单列表数据
        Route::get('goods-send-table', 'index/Order/getGoodsSendTable');//订单发货列表数据
        Route::get('order-back', 'index/Order/getOrderBack');//订单撤销
        Route::get('order-list', 'index/Order/getOrderList');//发货清单页面
        Route::get('order-table', 'index/Order/getOrderTable');//发货清单
        Route::get('order-download', 'index/Order/getOrderDownload');//订单下载
        Route::get('order-note', 'index/Order/getOrderNote');//订单备注修改

        //个人中心
        Route::get('user', 'index/User/getUser');//个人中心
        Route::get('data', 'index/User/getData');//完善资料
        Route::post('data', 'index/User/postData');//完善资料
        Route::get('change-password', 'index/User/getPassword');//修改密码
        Route::post('change-password', 'index/User/postPassword');//修改密码
        Route::get('pay-password', 'index/User/getPay');//修改交易密码
        Route::post('pay-password', 'index/User/postPay');//修改交易密码
        Route::get('store', 'index/User/getStore');//店铺
        Route::get('store-table', 'index/User/getStoreTable');//店铺数据
        Route::post('store', 'index/User/postStore');//修改店铺e
        Route::get('store-delete', 'index/User/getStoreDelete');//删除店铺
        Route::get('store-show', 'index/User/getStoreShow');//变更店铺状态
        Route::get('upgrade', 'index/User/getUpgrade');//升级成会员
        Route::get('default', 'index/User/getDefault');//完善初始资料
        Route::post('default', 'index/User/postDefault');//完善初始资料
        Route::post('upgrade', 'index/User/postUpgrade');//购买升级会员

        //找回支付密码
        Route::get('reset-pay', 'index/User/getReset');//前台找回支付密码页面
        Route::post('reset-pay', 'index/User/postReset');//前台找回支付密码方法
        Route::get('paySms', 'index/User/getResetSms');//找回密码支付验证码发送

        //在线充值
        Route::get('recharge', 'index/Recharge/getRecharge');//在线充值
        Route::post('recharge', 'index/Recharge/postRecharge');//充值
        Route::get('recharge-note', 'index/Recharge/getNote');//充值记录页面
        Route::get('recharge-note-table', 'index/Recharge/getNoteTable');//充值记录数据
        Route::get('recharge-back', 'index/Recharge/getBack');//撤销充值记录
        Route::get('expense-note', 'index/Recharge/getExpense');//余额记录页面
        Route::get('expense-note-table', 'index/Recharge/getExpenseTable');//余额记录数据

        //代理中心
        Route::get('generalize', 'index/Agent/getGeneralize');//我的推广
        Route::get('son', 'index/Agent/getSon');//我的下级
        Route::get('deduct-note', 'index/Agent/getDeduct');//提现记录页面
        Route::get('deduct-note-table', 'index/Agent/getDeductTable');//提现记录数据
        Route::get('withdraw', 'index/Agent/getWithdraw');//提现页面
        Route::post('withdraw', 'index/Agent/postWithdraw');//提现页面
        Route::get('withdraw-back', 'index/Agent/getBack');//提现页面
        Route::get('commis', 'index/Agent/getCommis');//佣金记录页面
        Route::get('commis-table', 'index/Agent/getCommisTable');//佣金记录数据

    })->middleware('member_login');//验证会员登录中间件

//游客页面，无需登录即可访问
    Route::group([], function () {

        Route::get('', 'index/Index/getIndex');//前台首页
        Route::get('/', 'index/Index/getIndex');//前台首页
        Route::get('/index', 'index/Index/getIndex');//前台首页
        Route::get('/index/', 'index/Index/getIndex');//前台首页
        Route::get('article', 'index/Index/getArticle');//文章页面
        Route::get('article-table', 'index/Index/getArticleTable');//文章数据
        Route::get('article-info/:id', 'index/Index/getArticleInfo');//文章详情
        Route::get('notice', 'index/Index/getNotice');//公告页面
        Route::get('notice-table', 'index/Index/getNoticeTable');//公告数据
        Route::get('notice-info/:id', 'index/Index/getNoticeInfo');//公告详情
        Route::get('goods', 'index/Index/getGoods');//礼品页面
        Route::get('goods-table', 'index/Index/getGoodsTable');//礼品数据
    });
    /**
     * 前台路由组结束
     */

    Route::get('/test', 'test/Test/index');
    Route::post('/test', 'test/Test/file');
})->middleware('substation');