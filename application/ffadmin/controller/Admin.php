<?php


namespace app\ffadmin\controller;
header('Content-Type: text/html; charset=UTF-8');

use think\Db;
use think\Controller;
use app\common\controller\AdminBase;
class Admin extends AdminBase
{
    /**
     * @return 角色列表
     */
    public function rolelist1()
    {
        $roles = Db::name('role')->field('id,name,title,pid')->where('pid', 0)->select();
        foreach ($roles as $key => $role) {
            $auths[$key]['name'] = $role['title'];
            $auths[$key]['url'] = $role['name'];
            $auths[$key]['id'] = $role['id'];
            $auths[$key]['pid'] = $role['pid'];
            $childrens = Db::name('role')->field('id,name,title,pid')->where('pid', $role['id'])->select();
            if ($childrens) {
                $auths[$key]['open'] = true;
                foreach ($childrens as $k => $children) {
                    $auths[$key]['children'][$k]['name'] = $children['title'];
                    $auths[$key]['children'][$k]['url'] = $children['name'];
                    $auths[$key]['children'][$k]['id'] = $children['id'];
                    $auths[$key]['children'][$k]['pid'] = $children['pid'];
                }
            }
        }
        dump($auths);
        die;
//        echo json_encode($roles);die;
        $this->assign('roles', json_encode($auths));
        return $this->fetch();
    }

    /**
     * 管理员列表
     */
    public function adminlist($adminid = '', $name = '', $phone = '', $email = '', $comm = ''){
        $where = [];
        //時間
        //账号
        if (isset($adminid) && !empty($adminid)) {
            $where['adminid'] = ['eq', $adminid];
        }
        //用戶名字
        if (isset($name) && !empty($name)) {
            $where['name'] = ['like', '%' . $name . '%'];
        }
        //电话
        if (isset($phone) && !empty($phone)) {
            $where['phone'] = ['like', '%' . $phone . '%'];
        }
        //邮箱
        if (isset($email) && !empty($email)) {
            $where['email'] = ['like', '%' . $email . '%'];
        }
        //备注
        if (isset($comm) && !empty($comm)) {
            $where['comment'] = ['like', '%' . $comm . '%'];
        }
        $admins = Db::name('admin')->where($where)->order('time desc')->paginate(15, false, ['query' => request()->param()]);
        $this->assign([
            'adminid' => $adminid,
            'name'    => $name,
            'email'   => $email,
            'comm'    => $comm,
            'phone'   => $phone,
            'admins'  => $admins,
        ]);
        return $this->fetch();
    }
    /**
     * 管理员添加/编辑
     *
     */
    public function addAdmin()
    {
        $data = input('param.');
        // print_r($data);
        // Array ( [au_name] => 1233 [au_nick_name] => 对对对 [au_pwd] => 11 [au_pwd2] => 11 [au_ar_id] => 1 [au_tel] => 111 [au_email] => 123@qq.com [au_intro] => 111 )
        if ($data['au_pwd'] != $data['au_pwd2']) {
            $this->error('两次输入密码不一致，请重新输入');
        }
        $arr = [
            'adminid' => $data['au_name'],
            'name'    => $data['au_nick_name'],
            'pwd'     => $data['au_pwd'],
            'auth'    => $data['au_ar_id'],
            'phone'   => $data['au_tel'],
            'email'   => $data['au_email'],
            'comment' => $data['au_intro'],
        ];
        if ($data['admin_id'] == '') {
            $result = Db::name('admin')->insertGetId($arr);
            //添加权限关系表
            $relation = [
                'uid'=>$result,
                'group_id'=>$data['au_ar_id']
            ];
//            Db::name('auth_role_access')->insert($relation);
            // 添加日志记录
            $this->addLog('管理员表', '添加', json_encode($arr), '');
        } else {
            $result = Db::name('admin')->where('id', $data['admin_id'])->update($arr);
            // 添加日志记录
            $this->addLog('管理员表', '修改', json_encode($arr), '');
        }

        if ($result) {
            $this->success('操作成功', url('ffadmin/admin/adminlist'));
        } else {
            $this->error('操作失败');
        }
    }

    /**
     * 管理员编辑
     * @return [type] [description]
     */
    public function edit()
    {
        $id    = input('param.id/d');
        $admin = Db::name('admin')->where('id', $id)->find();
        return json(['admin' => $admin]);
    }

    /**
     * 删除管理员
     * @return [type] [description]
     */
    public function delete()
    {
        $id  = input('param.id/d');
        $res = Db::name('admin')->where('id', $id)->delete();
        if ($res) {
            // 添加日志记录
            $this->addLog('管理员表', '删除', '', '');

            return json(['con' => 1]);
        } else {
            return json(['con' => 0]);
        }
    }

    /**
     * @return 修改状态
     *
     */
    public function editStatus()
    {
        $data   = input('param.');
        $id     = $data['id'];
        $status = $data['status'];
        if ($status == 0) {
            Db::name('admin')->where('id', $id)->update(['status' => 1]);
            // 添加日志记录
            $this->addLog('管理员表', '修改', json_encode(['status' => 1]), '');
            return json(['con' => 1, 'message' => '激活成功']);
        } else {
            Db::name('admin')->where('id', $id)->update(['status' => 0]);
            // 添加日志记录
            $this->addLog('管理员表', '修改', json_encode(['status' => 0]), '');
            return json(['con' => 1, 'message' => '冻结成功']);
        }
    }

    /**
     * 角色管理
     */
    public function roleList($auth = '')
    {
        $where = [];
        if (isset($auth) && !empty($auth)) {
            $where['auth'] = ['eq',$auth];
        }
        $roles = Db::name('role')->field('id,name,title,pid')->where('pid', 0)->where('status',1)->select();
        foreach ($roles as $key => $role) {
            $auths[$key]['name'] = $role['title'];
            $auths[$key]['url'] = $role['name'];
            $auths[$key]['id'] = $role['id'];
            $auths[$key]['pid'] = $role['pid'];
            $childrens = Db::name('role')->field('id,name,title,pid')->where('pid', $role['id'])->select();
            if ($childrens) {
                $auths[$key]['open'] = true;
                foreach ($childrens as $k => $children) {
                    $auths[$key]['children'][$k]['name'] = '|--'.$children['title'];
                    $auths[$key]['children'][$k]['url'] = $children['name'];
                    $auths[$key]['children'][$k]['id'] = $children['id'];
                    $auths[$key]['children'][$k]['pid'] = $children['pid'];
                }
            }
        }
//        dump($auths);
//        die;
        $list = model('admin')->field('id,name,adminid,auth,time')->where($where)->where('status',1)->where('id','neq',1)->select();
        $this->assign([
            'auths'=>$auths,
            'auth'=>$auth,
            'list'=>$list,
        ]);
        return $this->fetch();
    }
    /**
     * 添加角色
     */
    public function editAuth()
    {
        $data = input('param.');
        $id = $data['ar_id'];
        $role = $data['quanxian'];
        $cc = [];
        $aa = Db::name('role')->where('pid',0)->column('id');

        for($i=0;$i<count($role);$i++){
            for($j=0;$j<count($aa);$j++){
                $bb = Db::name('role')->where('id',$role[$i])->value('pid');

                $bb=strval($bb);
                if($bb == $aa[$j]){
                    $cc[] = $aa[$j];
                }
            }
        }
        $cc = array_unique($cc);
        $roles = array_merge($role, $cc);
        asort($roles);
        $roles = implode(',',$roles);
        $result = Db::name('admin')->where('id', $id)->update(['role'=>$roles]);
        // 添加日志记录
        $this->addLog('管理员表', '修改', json_encode(['role'=>$roles]), '');
        if ($result) {
            $this->success('操作成功', url('ffadmin/admin/rolelist'));
        } else {
            $this->error('操作失败');

        }
    }

    /**
     * 编辑角色
     */
    public function editRole()
    {
        $id    = input('param.id/d');
        $data  = Db::name('admin')->field('id,adminid,role')->where('id', $id)->find();
        $roles = explode(',', $data['role']);
        return json(['data' => $data, 'roles' => $roles]);
    }


    /**
     * 修改密码
     */
    public function changelist(){
        return $this->fetch();
    }

    public function doChange(){
        $data = input('param.');
        $old = $data['au_pwd_o'];
        $res = Db::name('admin')->where(['adminid'=>session('admin.adminid'),'pwd'=>$old])->find();
        if (!$res){
            $this->error('原始密码不正确，请重新输入');
        }
        if ($data['au_pwd']==''){
            $this->error('请输入新密码');
        }
        if ($data['au_pwd2']==''){
            $this->error('请再次输入新密码');
        }
        if($data['au_pwd'] != $data['au_pwd']){
            $this->error('两次密码输入不一致，请重新输入');

        }
        $result = Db::name('admin')->where('adminid',session('admin.adminid'))->update(['pwd'=>$data['au_pwd']]);
        if ($result){
            $this->success('修改成功');
        }else{
            $this->success('修改失败');
        }
    }

    /**
     * 重置密码
     */
    public function resetlist(){
        return $this->fetch();
    }

    public function doReset(){
        $data = input('param.');
        $userid = $data['au_name'];
        $user = Db::name('user')->where('userid',$userid)->find();
        if (!$user){
            $this->error('用户不存在');
        }
        if ($data['au_pwd']!=$data['au_pwd2']){
            $this->error('两次密码输入不一致，请重新输入');
        }
        $res = Db::name('user')->where('userid',$userid)->update(['pwd'=>$data['au_pwd']]);
        if ($res){
            $this->success('重置成功');
        }else{
            $this->error('重置失败');
        }
    }



}

