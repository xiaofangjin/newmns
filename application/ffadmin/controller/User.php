<?php


namespace app\ffadmin\controller;

use app\common\controller\AdminBase;
use think\Db;
use app\ffadmin\model\User as UserModel;

class User extends AdminBase
{
    /**
     * @param用户列表
     */
    public function userlist($userid = '', $date = '', $type = '', $name = '', $phone = '', $puserid = '', $type1 = '')
    {
        $where = [];
        //時間
        if (isset($date) && !empty($date)) {
            $timeArr = explode(' - ', $date);

            $min = strtotime($timeArr[0] . ' 00:00:00');
            $max = strtotime($timeArr[1] . ' 23:59:59');
            $where['create_time'] = ['between', [$min, $max]];
        }
        //用戶名
        if (isset($userid) && !empty($userid)) {
            $where['userid'] = ['eq', $userid];
        }

        //等级
        if (isset($type) && !empty($type)) {
            $where['kg_level'] = ['eq', $type];
        }
        //节点
        if (isset($type1) && !empty($type1)) {
            $where['level'] = ['eq', $type1];
        }

        //姓名
        if (isset($name) && !empty($name)) {
            $where['name'] = ['like', '%' . $name . '%'];
        }
        //手機號
        if (isset($phone) && !empty($phone)) {
            $where['phone'] = ['like', '%' . $phone . '%'];
        }
        //推薦人
        if (isset($puserid) && !empty($puserid)) {
            //根据推荐人账号查推荐人id
            $fid = Db::name('user')->where('userid', $puserid)->value('id');
            // print_r($fid);
            //父id= 推荐人id
            $where['pid'] = ['eq', $fid];

        }

        $result = Db::name('user')->where($where)->order('create_time desc')->paginate(6, false, ['query' => request()->param()]);
//        dump($result);
        // $result   = Db::name('user')->order('time desc')->paginate(10, false, ['query' => request()->param()]);
        $users = $result->all();

        foreach ($users as $key => $value) {
            $users[$key]['recom'] = Db::name('user')->where('id', $value['pid'])->value('userid');
            $users[$key]['totalnum'] = Db::name('user_ru')->where('userid', $value['userid'])->where(function ($query) {
                $query->where('is_dakuan', 2)->where('is',1)->whereor('is_dakuan',1);
            })->sum('num');
        }
        $pagefoot=$result->render();
//        dump($pagefoot);
//        die;
        $this->assign([
            'users' => $users,
            'date' => $date,
            'userid' => $userid,
            'type' => $type,
            'type1' => $type1,
            'name' => $name,
            'phone' => $phone,
            'puserid' => $puserid,
            'result' => $result,
        ]);
        return $this->fetch();
    }

    /**
     * 激活/冻结用户
     * @return [type] [description]
     */
    public function jihuo()
    {
        $id = input('param.id/d');
        $data = Db::name('user')->where('id', $id)->find();
        $res = $data['status'];
        if ($res == 1) {
            $result = Db::name('user')->where('id', $id)->update(['status' => 0]);
            $this->addLog('会员表', '修改', json_encode(['status' => 0]), $data['userid']);

        }
        if ($res == 0) {
            $result = Db::name('user')->where('id', $id)->update(['status' => 1]);
            $this->addLog('会员表', '修改', json_encode(['status' => 1]), $data['userid']);

        }
      if ($res == 2) {
            $result = Db::name('user')->where('id', $id)->update(['status' => 1]);
            $this->addLog('会员表', '修改', json_encode(['status' => 1]), $data['userid']);

        }
        if ($result) {
            return json(['con' => 1]);
        } else {
            return json(['con' => 0]);

        }
    }

    /**
     *
     * 编辑修改用户资料
     * @param string $value [description]
     */
    public function edit()
    {
        $id = input('param.id');
        $user = Db::name('user')->where('id', $id)->find();
        $parentUser = Db::name('user')->where('id', $user['pid'])->value('userid');
        return json(['user' => $user, 'parentUser' => $parentUser]);
    }

    public function doEdit()
    {
        $data = input('param.');
        $userid = $data['userid1'];
        $realname = $data['realname1'];
        $pwd = $data['pwd'];
        $pwdH = $data['pwdH'];
        $phone = $data['iphone'];
        $id = $data['user_id'];
        $result = Db::name('user')->where('id', $id)->update(['name' => $realname, 'phone' => $phone,
            'kg_level' => $data['user_type'],'level' => $data['jie'],'pwd'=>$pwd,'pwdH'=>$pwdH]);
        if ($result) {
            //添加日志记录
            $this->addLog('会员表', '修改', json_encode(['name' => $realname, 'phone' => $phone,
                'kg_level' => $data['user_type'],'pwd'=>$pwd,'pwdH'=>$pwdH,'level'=>$data['jie']]), $userid);
            return json(['con' => 1, 'message' => '修改成功']);
        } else {
            return json(['con' => 0, 'message' => '修改失败']);

        }
    }

    /**
     * 添加新会员
     * @return [type] [description]
     */
    public function registerlist()
    {
        return $this->fetch();
    }

/**
     * 添加新会员提交表单
     * @return [type] [description]
     */
    public function doRegister()
    {
        $userModel = new UserModel;

        $data = input('param.');
        $userid = $data['u_name'];
        $tuijian = $data['u_re_uid'];
        if (!empty($userid) && !empty($tuijian)) {
            $user = Db::name('user')->where('userid', $userid)->find();
            if ($user) {
                $this->error('用户名已存在');
            }
            $parent = Db::name('user')->where('userid', $tuijian)->find();
            if (!$parent) {
                $this->error('推荐人不存在');
            }
            $fid = $parent['id'];
            $path = $parent['path'] . '-' . $parent['id'];
            $depth = $parent['depth'] + 1;
            $arr = [
                'userid' => $data['u_name'],
                'pid' => $fid,
                'path' => $path,
                'depth' => $depth,
                'name' => $data['u_nick_name'],
                'phone' => $data['u_tel'],
                'pwd' => $data['u_pwd1'],
                'pwdH' => $data['u_pwd2'],
                'create_time' => time(),
            ];
            $result = Db::name('user')->insert($arr);
            if ($result) {
                //添加日志记录
                $this->addLog('会员表', '添加', json_encode($arr), $userid);
                $this->success('添加成功', url('ffadmin/user/userlist'));
            } else {
                $this->error('添加失败');
            }
        }
    }

    /**
     * 团队管理
     * @param string $value [description]
     */

    //public function treelist()
    //{
       // $this->assign('name', input('?param.name') ? input('param.name') : '');
       // return $this->fetch();
   // }
  
      public function treelist()
    {
        $count = '';
        if(request()->isAjax()){
            $id = input('post.id');
            $arr = Db::name('user')->field('id,pid,userid,time,path')->where('pid',$id)->select();
            foreach ($arr as $key=>$value){
                $arr[$key]['count'] = Db::name('user')->where('path','like','%'.$value['path'].'-'.$value['id'].'%')->count();
            }
            $num = count($arr);
            return json(['num'=>$num,'data'=>$arr]);

        }else{
            $loginName = '188';
            $arr = Db::name('user')->field('id,pid,userid,time,path')->where('userid',$loginName)->find();
            $count = Db::name('user')->where('path','like','%'.$arr['path'].'-'.$arr['id'].'%')->count();
            $this->assign('arr',$arr);
            $this->assign('count',$count);
            return $this->fetch();
        }
    }
    public function treelist1(){

        $loginName = input('post.data');
        $arr = Db::name('user')->field('id,pid,userid,time,path')->where('userid',$loginName)->find();
        $count = Db::name('user')->where('path','like','%'.$arr['path'].'-'.$arr['id'].'%')->count();
        $arr['count'] = $count;
        return json(['msg'=>$arr]);

    }
//
//
//    public function getTree()
//    {
//        //检测变量是否设置
//        $id = input('?param.id') ? input('param.id') : 0;
//        $where = [];
//        if (input('?param.name') && $id == 0) {
//            $where['name'] = ['like', '%' . input('param.name') . '%'];
//        }
//        $res = Db::name('user')->field('id,userid,pid,name')->where($where)->where('pid', $id)->select();
//        $data = [];
//        foreach ($res as $re) {
//            $user = [];
//            $user['id'] = $re['id'];
//            $user['name'] = $re['name'];
//            $user['title'] = $re['userid'];
//            $children = Db::name('user')->where('pid', $re['id'])->find();
//            $user['isParent'] = $children ? true : false;
//            $data[] = $user;
//        }
//        return json($data);
//    }

    /**
     * 会员审核
     * @return [type] [description]
     */
    public function passlist($userid = '', $phone = '')
    {
        $where = [];
        //名字
        if (isset($userid) && !empty($userid)) {
            $where['userid'] = ['eq', $userid];
        }
        if (isset($phone) && !empty($phone)) {
            $where['phone'] = ['like', '%' . $phone . '%'];
        }
        $users = model('user')->where($where)->where('shenhe', 0)->where('is_approve',2)->order('create_time desc')->paginate(15, false, ['query' => request()->param()]);
        $this->assign([
            'users' => $users,
            'userid' => $userid,
            'phone' => $phone,

        ]);
        return $this->fetch();
    }

    public function shenhe()
    {
        $id = input('param.id/d');
        $userid = Db::name('user')->where('id', $id)->value('userid');
        $res = Db::name('user')->where('id', $id)->update(['shenhe' => 1,'is_approve'=>1]);
        if ($res) {
            $userModel = new UserModel();
            $userModel->zengsong($userid);
            $this->addLog('会员表', '修改', json_encode(['shenhe' => 1]), $userid);
            return json(['con' => 1]);
        } else {
            return json(['con' => 0]);

        }
    }
  
    /**
     * @return \批量审核
     */
    public function piliang(){
        $userModel = new UserModel;
        $id = input('param.id');
        $arr = explode(',',$id);
        Db::startTrans();
        try{
            for($i = 0; $i< count($arr);$i++){
                Db::name('user')->where('id','eq', $arr[$i])->update(['shenhe' => 1,'is_approve'=>1]);
                $userid = Db::name('user')->where('id','eq', $arr[$i])->value('userid');
                $userModel->zengsong($userid);
            }
            // 提交事务
            Db::commit();
            $res = 1;
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            $res = 0;
        }

        if($res){
            return json(['con' => 1,'message'=>'批量审核通过']);
        }else {
            return json(['con' => 0,'message'=>'批量审核失败']);

        }
    }
  
      /**
     * @return 否决
     */
    public function deny()
    {
        $id = input('param.id/d');
        $userid = Db::name('user')->where('id', $id)->value('userid');
        $res = Db::name('user')->where('id', $id)->delete();
        if ($res) {
            session('user',null);
            $this->addLog('会员表', '删除', '', $userid);
            return json(['con' => 1]);
        } else {
            return json(['con' => 0]);

        }
    }

    public function openIndex(){
        $id = input('param.id');
        $userid = Db::name('user')->field('id,userid,pid')->where('id',$id)->find();
        if($userid){
            session('user',$userid);
            return true;
        }else{
            return false;
        }

    }
  
      /**
     * 永久冻结
     */
    public function yongjiu(){
        $id = input('param.id');
        $result = Db::name('user')->where('id',$id)->setField('status',2);
        if($result){
            return json(['con' => 1,'message'=>'永久冻结成功']);
        }else{
            return json(['con' => 1,'message'=>'永久冻结失败']);
        }
    }

}