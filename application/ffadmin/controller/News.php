<?php

namespace app\ffadmin\controller;

use app\common\controller\AdminBase;
use think\Db;

/**
 *新闻管理器
 */
class News extends AdminBase
{
    /**
     * 添加新闻
     * @return [type] [description]
     */
    public function addnotice($id = '')
    {
        $news = DB::name('news')->where('id', $id)->find();
        // print_r($news);
        $this->assign('news', $news);
        return $this->fetch();
    }

    public function doAdd()
    {

        $data = input('param.');
        $arr = [
            'title'       => $data['nn_title'],
            'content'     => $data['p_content'],
            'type'     => $data['type'],
            'create_time' => date('Y-m-d H:i:s',time()),
            'adminid'=>session('admin.adminid'),
        ];
        if ($data['nn_id'] == '') {
            $res = Db::name('news')->insert($arr);
            //插入日志表
            $this->addLog('新闻管理表', '新增', json_encode($arr), '');
        } else {
            $res = Db::name('news')->where('id', $data['nn_id'])->update($arr);
            //添加日志记录
            $this->addLog('新闻管理表', '新增', json_encode($arr), '');

        }

        if ($res) {
            $this->success('操作成功', url('ffadmin/news/noticelist'));
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 新闻管理
     * @param  string $title   [description]
     * @param  string $comment [description]
     * @return [type]          [description]
     */
    public function noticelist($title = '', $comment = '')
    {

        $where = [];
        if (isset($title) && !empty($title)) {
            $where['title'] = ['like', '%' . $title . '%'];
        }
        if (isset($comment) && !empty($comment)) {
            $where['comment'] = ['like', '%' . $comment . '%'];
        }
        $news = DB::name('news')->where($where)->order('create_time desc')->paginate(10, false, ['query' => request()->param()]);
        // print_r($news);
        // die();
        $this->assign([
            'news'    => $news,
            'title'   => $title,
            'comment' => $comment,
        ]);
        return $this->fetch();

    }

    /**
     * 删除新闻
     * @return [type] [description]
     */
    public function delete()
    {
        $id = input('param.id/d');
        // print_r($id);
        $result = Db::name('news')->where('id', $id)->delete();
        if ($result) {
            //日志记录
            $this->addLog('新闻管理表', '删除', '', '');
            return json(['con' => 1]);
        } else {
            return json(['con' => 0]);

        }
    }

    /**
     * @留言列表
     */
    public function msglist( $status = '', $userid = '', $name = '')
    {
        $where = [];
        if (isset($status) && !empty($status)) {
            $where['status'] = ['eq', $status];
        }
        if (isset($userid) && !empty($userid)) {
            $where['userid'] = ['eq', $userid];
        }
        if (isset($name) && !empty($name)) {
            $where['name'] = ['like', '%' . $name . '%'];
        }
        $comment = Model('message')->where($where)->order('create_time desc')->paginate(10, false, ['query' => request()->param()]);
        // print_r($comment);
        // die();
        $this->assign([
            'name'    => $name,
            'userid'  => $userid,
            'status'  => $status,
            'comment' => $comment,
        ]);
        return $this->fetch();
    }

    /**
     * 回复
     * @return [type] [description]
     */
    public function huifu()
    {
        $id   = input('param.id/d');
        $data = Db::name('message')->where('id', $id)->find();
        if ($data) {
            return json(['data' => $data]);
        } else {
            return json(['data' => false]);
        }
    }

    public function dohuifu()
    {
        $data = input('param.');
        $id   = $data['id'];
        unset($data['id']);
        $data['huserid']=session('admin.adminid');
        $res = Db::name('message')->where('id', $id)->update(['result'=>$data['result'],'status'=>1,'huserid'=>session('admin.adminid')]);
        $userid = Db::name('message')->where('id', $id)->value('userid');
        if ($res) {
            $this->addLog('留言表','修改',json_encode($data),$userid);
            return json(['con' => 1]);
        } else {
            return json(['con' => 0]);
        }
    }


    /**
     * 删除留言
     * @return [type] [description]
     */
    public function deleteComment()
    {
        $id  = input('param.id/d');
        $userid = Db::name('message')->where('id', $id)->value('userid');
        $res = DB::name('message')->where('id', $id)->delete();
        if ($res) {
            $this->addLog('留言表','删除','',$userid);
            return json(['con' => 1]);
        } else {
            return json(['con' => 0]);
        }
    }
}
