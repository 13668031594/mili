<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/26
 * Time: 下午4:21
 */

namespace app\index\controller;

use classes\index\IndexCLass;

class IndexController extends \app\http\controller\IndexController
{
    private $class;

    public function __construct()
    {
        parent::__construct();

        $this->class = new IndexCLass();
    }

    //首页
    public function getIndex()
    {
        $result = [
            'banner' => $this->class->banner(),//banner
            'adv' => $this->class->adv(),//广告
            'notice' => $this->class->notice(),//公告
            'article' => $this->class->article(),//文章
        ];

        return parent::view('index', $result);
    }

    //文章列表
    public function getArticle()
    {
        return parent::view('article');
    }

    //文章数据
    public function getArticleTable()
    {
        $result = $this->class->article_table();

        return parent::tables($result);
    }

    //文章详情
    public function getArticleInfo($id)
    {
        $self = $this->class->article_info($id);

        return parent::view('article-info', ['self' => $self]);
    }

    //公告列表
    public function getNotice()
    {
        return parent::view('notice');
    }

    //公告数据
    public function getNoticeTable()
    {
        $result = $this->class->notice_table();

        return parent::tables($result);
    }

    //公告详情
    public function getNoticeInfo($id)
    {
        $self = $this->class->notice_info($id);

        return parent::view('notice-info', ['self' => $self]);
    }

    //礼品页面
    public function getGoods()
    {
        $class = $this->class->goods_class();

        $result = [
            'class' => $class,
            'goodsName' => input('goodsName'),
            'goodsClass' => input('goodsClass')
        ];

        return parent::view('goods', $result);
    }

    //礼品数据
    public function getGoodsTable()
    {
        $result = $this->class->goods_table();

        return parent::tables($result);
    }
}