<?php


namespace app\index\controller;


use app\common\controller\IndexBase;
use think\Db;
use app\index\model\Deal as DealModel;
class Center extends IndexBase
{
    /**
     * 个人中心
     */
    public function my(){
        $userid = session('user.userid');
        $user = model('user')->where('userid',$userid)->find();
        $this->assign([
            'user'=>$user,
        ]);
        return $this->fetch();
    }



    /**
     * @return 我的推广
     */
    public function partner(){
        $userid = session('user.userid');
        $user = Db::name('user')->field('id,userid,path,path')->where('userid',$userid)->find();
        $team = model('user')->where('pid',$user['id'])->select();
        foreach ($team as $key=>$value){
            $team[$key]['teamzhi'] = Db::name('user')->where('path','like','%'.$value['path'].'-'.$value['id'].'%')->sum('first');
        }
        $this->assign([
            'team'=>$team,
        ]);
        return $this->fetch();
    }


    /**
     * @return 交易明细
     */
    public function jymx(){
        $userid = session('user.userid');
        $list = [];
//        买入记录
        $list[] = Db::name('money_log')->where('userid',$userid)->where('status',1)->select();
//        复投记录
        $list[] = Db::name('money_log')->where('userid',$userid)->where('status',3)->select();
        //提现记录
        $list[] = Db::name('shouyi_log')->where('userid',$userid)->where('status',5)->select();
        $this->assign('list',$list);
        return $this->fetch();
    }


    /**
     * 收益明细
     */
    public function symx(){
        $userid = session('user.userid');
        $list = [];
        //推荐奖
        $list[] = Db::name('shouyi_log')->where('userid',$userid)->where('status',6)->order('create_time')->group('create_time')->select();

        //业绩奖
//        $list[] = Db::name('shouyi_log')->where('userid',$userid)->where('status',2)->order('create_time')->group('create_time')->select();
        $list[] = Db::query("SELECT sum(get_money) as sum,create_time FROM ht_shouyi_log where userid = " .$userid." and status =2 group by date_format(create_time,'%m-%d-%Y') order by create_time desc ");
        //团队奖
//        $list[] = Db::name('shouyi_log')->where('userid',$userid)->where('status',3)->select();
        $list[] = Db::query("SELECT sum(get_money) as sum,create_time FROM ht_shouyi_log where userid = " .$userid." and status =3 group by date_format(create_time,'%m-%d-%Y') order by create_time desc ");
        $this->assign('list',$list);
        return $this->fetch();
        ;

    }


    //修改头像
    public function AJAXupload1(){
        $userid = session('user.userid');
        $file = $this->request->file('file');
        $target = ROOT_PATH . 'public/static/upload/img';
        if($file){
            $info = $file->validate(['ext'=>'jpg,png,gif,jpeg','size'=>'10000000'])->move($target);
            if($info){
                $path = $info->getSaveName();
                Db::name('user')->where('userid',$userid)->update(['tou'=>$path]);
                return json(['data'=>$path,'code'=>'1']);
            }else{
                // 上传失败获取错误信息
                return json(['data'=>$file->getError(),'code'=>'2']);
            }
        }
    }



    /**
     * 设置
     */
    public function sz(){
        $userid = session('user.userid');
        $user = Db::name('user')->field('id,userid,pwdH,tou,name')->where(['userid'=>$userid])->find();
        $this->assign([
            'user'=>$user,
        ]);
        return $this->fetch();
    }

    /**
     * 修改昵称
     */
    public function editnick(){
        $subtitle = '';
        $userid = session('user.userid');
        $name = input('param.value');
        $username = Db::name('user')->where('userid',$userid)->value('name');
        if($name){
            $subtitle=substr($name,0,15);
            $result = Db::name('user')->where('userid',$userid)->update(['name'=>$subtitle]);
        }
        if($subtitle == $username){
            return json(['message'=>'请勿重复','con'=>1]);
        }
        if($result){
            return json(['message'=>'修改成功','con'=>1]);
        }else{
            return json(['message'=>'系统繁忙，请稍后重试']);
        }

    }

    /**
     * 修改支付密码
     */
    public function zfmm(){
        $userid = session('user.userid');
        $this->assign('userid',$userid);
        return $this->fetch();

    }

    /**
     * @return修改二级密码提交数据
     */
    public function editPwdTwo(){
        $data = input('param.');
        $userid = session('user.userid');
        $user = Db::name('user')->field('id,userid,pwdH')->where(['userid'=>$userid,'pwdH'=>$data['old_password']])->find();
        if (!isset($user)){
            return json(['message'=>'原支付密码不正确']);
        }
        //判断验证码是否正确
        $code = $data['code'];
        $information = Db::name('code')->where('phone',$userid)->where('code',$code)->where('is',1)->where('status',6)->find();
        if(empty($information)){
            //return json(['message'=>'短信验证码不正确','con'=>2]);
        }else{
            $time = date('YmdHis',time());
            $sqlcode = $information['create_time_str'] + 300;
            if((int)$time>$sqlcode){
                //return json(['message'=>'验证码超时','con'=>2]);
            }
        }
        if($user['pwdH'] == $data['new_password']){

            Db::name('code')->where('phone',$userid)->where('code',$code)->where('is',1)->where('status',6)->setField('is',0);
            return json(['message'=>'修改成功','con'=>1]);
        }
        $result = Db::name('user')->where(['userid'=>$userid])->update(['pwdH'=>$data['new_password']]);
        if($result){
            Db::name('code')->where('phone',$userid)->where('code',$code)->where('is',1)->where('status',6)->setField('is',0);
            return json(['message'=>'修改成功','con'=>1]);
        }else{
            return json(['message'=>'系统繁忙，请稍后重试']);
        }

    }

    /**
     * 修改登录密码
     */
    public function dlmm(){
        $userid = session('user.userid');
        $this->assign('userid',$userid);
        return $this->fetch();

    }

    /**
     * @return修改登录密码提交数据
     */
    public function editPwd(){
        $data = input('param.');
        $userid = session('user.userid');
        $user = Db::name('user')->field('id,userid,pwd')->where(['userid'=>$userid,'pwd'=>$data['old_password']])->find();
        if (!isset($user)){
            return json(['message'=>'原密码不正确']);
        }
        //判断验证码是否正确
        $code = $data['code'];
        $information = Db::name('code')->where('phone',$userid)->where('code',$code)->where('is',1)->where('status',5)->find();
        if(empty($information)){
            //return json(['message'=>'短信验证码不正确','con'=>2]);
        }else{
            $time = date('YmdHis',time());
            $sqlcode = $information['create_time_str'] + 300;
            if((int)$time>$sqlcode){
                //return json(['message'=>'验证码超时','con'=>2]);
            }
        }
        if($user['pwd'] == $data['new_password']){
            Db::name('code')->where('phone',$userid)->where('code',$code)->where('is',1)->where('status',5)->setField('is',0);
            return json(['message'=>'修改成功','con'=>1]);
        }
        $result = Db::name('user')->where(['userid'=>$userid])->update(['pwd'=>$data['new_password']]);
        if($result){
            Db::name('code')->where('phone',$userid)->where('code',$code)->where('is',1)->where('status',5)->setField('is',0);
            return json(['message'=>'修改成功','con'=>1]);
        }else{
            return json(['message'=>'系统繁忙，请稍后重试']);
        }
    }

    /**
     * @return mixed邀请好友
     */
    public function yqhy(){
        return $this->fetch();
    }


    /**
     * @return 退出登录
     */
    public function logout(){
        session('user',null);
    }


}