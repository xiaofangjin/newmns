<?php


namespace app\index\controller;


use app\common\controller\IndexBase;
use think\Db;

/**
 * Class News
 * @package app\index\controller新闻
 */
class News extends IndexBase
{

    /**
     * 资讯
     */
    public function zx(){
        $news = Db::name('news')->where('type',2)->order('create_time desc')->select();
        $this->assign('news',$news);
        return $this->fetch();
    }

    /**
     * 资讯详情
     */
    public function detail(){
        $id = input('param.id/d');
        $detail = Db::name('news')->field('id,title,content,create_time,adminid')->where('id',$id)->find();
        $this->assign('detail',$detail);
        return $this->fetch();
    }


    /**
     * 系统公告
     */
    public function xtxx(){
        $news = Db::name('news')->where('type',1)->order('create_time desc')->select();
        $this->assign('news',$news);
        return $this->fetch();
    }

    /**
     * 公告详情
     */
    public function ggxq(){
        $id = input('param.id/d');
        $detail = Db::name('news')->field('id,title,content,create_time,adminid')->where('id',$id)->find();
        $this->assign('detail',$detail);
        return $this->fetch();
    }
}