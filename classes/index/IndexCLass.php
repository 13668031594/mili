<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 2018/11/26
 * Time: 下午5:52
 */

namespace classes\index;

use app\adv\model\AdvModel;
use app\article\model\ArticleModel;
use app\banner\model\BannerModel;
use app\goods\model\GoodsAmountModel;
use app\goods\model\GoodsClassModel;
use app\goods\model\GoodsModel;
use app\notice\model\NoticeModel;

class IndexCLass extends \classes\IndexClass
{
    //banner图片-首页
    public function banner()
    {
        $model = new BannerModel();

        $banner = $model->where('substation','=',SUBSTATION)->where('show', '=', 'on')->order('sort', 'desc')->column('id,location,title,link');

        $result = [];
        $i = 0;
        foreach ($banner as $v) {

            $result[$i]['location'] = file_exists(substr($v['location'], 1)) ? $v['location'] : config('young.image_not_found');
            $result[$i]['title'] = $v['title'];
            $result[$i]['link'] = $v['link'];

            $i++;
        }

        return $result;
    }

    //广告-首页
    public function adv()
    {
        $model = new AdvModel();

        $banner = $model->where('substation','=',SUBSTATION)->where('show', '=', 'on')->order('sort', 'desc')->column('id,location,title,link');

        $result = [];
        $i = 0;
        foreach ($banner as $v) {

            $result[$i]['location'] = file_exists(substr($v['location'], 1)) ? $v['location'] : config('young.image_not_found');
            $result[$i]['title'] = $v['title'];
            $result[$i]['link'] = $v['link'];

            $i++;
        }

        return $result;
    }

    //公告-首页
    public function notice()
    {
        $model = new NoticeModel();

        return $model->where('substation','=',SUBSTATION)->where('show', '=', 'on')->limit(3)->page(1)->order('sort', 'desc')->column('id,title');
    }

    //文章-首页
    public function article()
    {
        $model = new ArticleModel();

        $article = $model->where('substation','=',SUBSTATION)->where('show', '=', 'on')->limit(6)->page(1)->order('sort', 'desc')->column('id,title,describe,created_at,author');

        $result =  array_chunk($article, 3);

//        dump($result);
//        exit();
        return $result;
    }

    //文章-列表
    public function article_table()
    {
        $model = new ArticleModel();

        $where[] = ['show', '=', 'on'];
        $where[] = ['substation', '=', SUBSTATION];

        $result = [
            'where' => $where,
            'order_name' => 'sort',
            'column' => 'id,title,describe,created_at',
        ];

        return parent::page($model, $result);
    }

    //文章-详情
    public function article_info($id)
    {
        $model = new ArticleModel();

        $article = $model->where('substation','=',SUBSTATION)->where('show', '=', 'on')->where('id', '=', $id)->find();

        if (is_null($article)) parent::redirect_exception('/', '该文章已被删除或隐藏');

        return $article;
    }

    //公告-列表
    public function notice_table()
    {
        $model = new NoticeModel();

        $where[] = ['show', '=', 'on'];
        $where[] = ['substation', '=', SUBSTATION];

        $result = [
            'where' => $where,
            'order_name' => 'sort',
            'column' => 'id,title,created_at',
        ];

        return parent::page($model, $result);
    }

    //公告-详情
    public function notice_info($id)
    {
        $model = new NoticeModel();

        $result = $model->where('substation','=',SUBSTATION)->where('show', '=', 'on')->where('id', '=', $id)->find();

        if (is_null($result)) parent::redirect_exception('/', '该公告已被删除或隐藏');

        return $result;
    }

    //礼品-分类
    public function goods_class()
    {
        $model = new GoodsClassModel();

        return $model->order('sort', 'desc')->column('id,name');
    }

    //礼品-列表
    public function goods_table()
    {
        $where[] = ['status', '=', 'on'];

        $goods_class = input('goodsClass');
        if (!empty($goods_class)) $where[] = ['goods_class_id', '=', $goods_class];
        $name = input('goodsName');
        if (!empty($name)) $where[] = ['name', 'like', '%' . $name . '%'];

        $other = [
            'order_name' => 'sort',
            'where' => $where,
        ];

        $result = parent::page(new GoodsModel(), $other);

        $amount = new GoodsAmountModel();
        foreach ($result['message'] as &$v) {

            if (is_null($v['location']) || !file_exists(substr($v['location'], 1))) $v['location'] = config('young.image_not_found');
            if (SUBSTATION != '0') {

                $a = $amount->where('goods_id', '=', $v['id'])->where('substation', '=', SUBSTATION)->find();
                if (!is_null($a)) $v['amount']= $a->amount;
            }
        }

        return $result;
    }
}
