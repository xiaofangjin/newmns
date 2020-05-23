<?php


namespace app\index\controller;
use think\Controller;
use think\Db;
use app\index\model\User as UserModel;
use geetest\GeetestLib;
use think\Session;
class User extends Controller
{
    /**
     * @return 用户注册
     */
    public function sign(){
        $id = input('param.id/d');
        if(isset($id)){
            $pUserid = Db::name('user')->where('id',$id)->value('userid');
        }
        $data = isset($pUserid) ? $pUserid:0;
        $this->assign('data',$data);
        return $this->fetch();
    }


    public function doSign(){
        $data = input('param.');
		$subtitle = '';
        //判断验证码是否正确
        $code = $data['code'];
        $phone = $data['phone'];
        $information = Db::name('code')->where('phone',$phone)->where('code',$code)->where('is',1)->where('status',2)->find();
        if(empty($information)){
            //return json(['message'=>'短信验证码不正确','con'=>2]);
        }else{
            $time = date('YmdHis',time());
            $sqlcode = $information['create_time_str'] + 300;
            if((int)$time>$sqlcode){
                //return json(['message'=>'验证码超时','con'=>2]);
            }
        }
        Db::name('code')->where('phone',$phone)->where('code',$code)->where('is',1)->where('status',2)->setField('is',0);
        $fUser = Db::name('user')->field('id,userid,pid,path,depth')->where('userid',$data['fuesrid'])->find();
        if(!preg_match("/^(0|86|17951)?(13[0-9]|15[012356789]|17[678]|18[0-9]|14[57]|19[0-9]|16[0-9])[0-9]{8}$/", $data['phone'])){
            //return json(['message'=>'请输入有效的手机号']);
        }
        $user = Db::name('user')->field('id')->where(['userid'=>$data['phone']])->find();
        if($user){
            return json(['message'=>'该用户已存在，请重新注册','con'=>2]);
        }
        if (!isset($fUser)){
            return json(['message'=>'邀请码不存在','con'=>2]);
        }
		$subtitle=substr($data['name'],0,15);
        $arr = [
            'userid'=>$data['phone'],
            'pid'=>$fUser['id'],
            'path'=>$fUser['path'] . '-'. $fUser['id'],
            'depth'=>$fUser['depth'] + 1,
            'pwd'=>$data['pwd'],
            'pwdH'=>$data['pwdH'],
            'phone'=>$data['phone'],
            'name'=>$subtitle,
            'create_time'=>time(),
            'time'=>date('Y-m-d H:i:s',time()),
            'dizhi'=>$this->get_api(),
        ];
        $result = Db::name('user')->insertGetId($arr);
        if ($result){
            return json(['message'=>'注册成功','con'=>1]);
        }else{
            return json(['message'=>'注册失败','con'=>2]);
        }
    }

    //获取收货地址
    public function get_api(){
        $userModel = new UserModel();
        $api='https://api.ztpay.org/api/v1';
        $appid='zttj70qal7lk83pyjs';
        $appsecret='MSdiwcQupSSZ9TfSWxWCmkmjWgOAZhDJ';
        $name='USDT';
        $data=array(
            'appid'=>$appid,
            'method'=>"get_address",
            'name'=>$name
        );
        $sign=$userModel->getSign($data,$appsecret);
        $data['sign']=$sign;
        $res=$userModel->request_post($api,$data);
        //$data_arr = json_decode($res,true);
        //var_dump($data_arr);die;
        echo $res;
    }

    /***
     * 展示滑块验证码
     */
    public function getCaptcha($t){
        $geetest_id = config('geetest.id');
        $geetest_key = config('geetest.key');
        $GtSdk = new GeetestLib($geetest_id,$geetest_key);
        $data = array(
            "user_id" => $t, # 网站用户id
            "client_type" => "web",
        );

        $status = $GtSdk->pre_process($data, 1);
        Session::set('geetest', ['gtserver' => $status, 'user_id' => $data['user_id']]);
        echo $GtSdk->get_response_str();
    }

    /**
     * 验证
     */
    public function verify($data){
        $geetest_id = config('geetest.id');
        $geetest_key = config('geetest.key');
        $GtSdk = new GeetestLib($geetest_id,$geetest_key);
        if (Session::get('geetest.gtserver') == 1) {   //服务器正常
            $result = $GtSdk->success_validate($_POST['geetest_challenge'], $_POST['geetest_validate'], $_POST['geetest_seccode'], $data);
            if ($result) {
                return true;
            } else{
                return false;
            }
        }else{  //服务器宕机,走failback模式
            if ($GtSdk->fail_validate($_POST['geetest_challenge'],$_POST['geetest_validate'],$_POST['geetest_seccode'])) {
                return true;
            }else{
                return false;
            }
        }
    }

    /**
     * @return 用户登录
     */
    public function login(){
//        dump($_COOKIE);
        return $this->fetch();
    }

    public function doLogin(){
		$userModel = new UserModel();
        $data = input('param.');
        if(!$this->verify($data)){
            return json(['message'=>'验证码不正确','con'=>2]);
        }
        $user = Db::name('user')->field('id,userid,pid,kg_level,status')->where(['userid'=>$data['userid'],'pwd'=>$data['pwd']])->find();
        if(isset($user) && $user['status']!=1){
            return json(['message'=>'账号已冻结','con'=>2]);
        }
        if ($user){
            session('user',$user);
            $arr = Db::name('user')->field('userid,pwd')->where(['userid'=>$data['userid']])->find();
            if($data['remember'] == 1){
//                echo 11;
                setcookie("username",$arr['userid'],time()+3600*24*7);
                setcookie("password",$arr['pwd'],time()+3600*24*7);
            }else if($data['remember'] == 2){
//                echo 22;
                setcookie("username",null,time()+3600*24*7);
                setcookie("password",null,time()+3600*24*7);
//                cookie('user',null);
//                dump($_COOKIE);
            }
			            //节点升级
            $userModel->jiedian($user['userid']);
            return json(['message'=>'登录成功','con'=>1]);
        }else{
            return json(['message'=>'用户名或密码不正确，请重新输入','con'=>2]);
        }
    }

    /**
     * 验证码
     * */
    public function code(){
        if(request()->isAjax()){
            $data = input('post.');
            foreach ($data as $k=>$v){
                $new_str = $this->xss_sql($v);
                $data[$k] = $new_str;
            }
            $code = rand(1000, 9999);
            $mobile = $data['phone'];
            $re = Db::name('user')->where('userid',$mobile)->value('id');
            if($data['status'] == 2){
                if(!empty($re)){
                    return json(['msg'=>'用户已存在']);
                }
            }

            if($data['status'] == 1){
                if(empty($re)){
                    return json(['msg'=>'用户不存在']);
                }
            }

            $this->yanzheng($code, $mobile);
            $userModel = new UserModel();
            $userModel -> codeModel($data,$code);
            return json(['msg'=>'发送成功']);
        }
    }

    public function yanzheng($code='', $mobile,$type=''){

        $a = 'Z';
        $b = 'qq123456';
        $d = '【MNS】您的验证码是'.$code.',请及时查看,此验证码三分钟内有效';
        $username=urlencode(trim($a));
        $password=urlencode(trim($b));
        $mobiles=trim($mobile);
        //$content=urlencode(iconv( "UTF-8", "gb2312//IGNORE" , trim($_REQUEST["contents"])));
        $content=urlencode(mb_convert_encoding(trim($d),"gb2312" , "utf-8"));
        $this->SendSms($username, $password,$mobiles, $content);
    }
    //发送短信
    function SendSms($username, $password,$mobiles, $content)
    {

        $fp=Fopen("http://api.sms1086.com/api/Send.aspx?username=$username&password=$password&mobiles=$mobiles&content=$content","r");
        $ret = fgetss($fp,255);
        /* echo urldecode($ret);
         Fclose($fp); */
    }
    //修改密码
    function ChgPwd($username, $password,$new_password)
    {
        $fp=Fopen("http://api.sms1086.com/api/ChgPwd.aspx?username=$username&password=$password&newpws=$new_password","r");
        $ret = fgetss($fp,255);
        echo urldecode($ret);
        Fclose($fp);
    }
    //查询余额
    function Query($username, $password)
    {
        $fp=Fopen("http://api.sms1086.com/apiQuery.aspx?username=$username&password=$password","r");
        $ret = fgetss($fp,255);
        echo urldecode($ret);
        Fclose($fp);
    }
    public function xss_sql($string){
        $str = trim($string);           //清理空格
        $str = strip_tags($str);        //过滤html标签
        $str = htmlspecialchars($str);  //将字符内容转化为html实体
        //$str = addslashes($str);        //防止SQL注入
        return $str;
    }
    public function lang(){
        switch ($_GET['lang']) {
            case 'cn':
                cookie('think_var', 'zh-cn');
                break;
            case 'en':
                cookie('think_var', 'en-us');
                break;
            case 'cn2':
                cookie('think_var', 'ft-cn2');
                break;
        }
    }

    /**
     * @return 忘记密码页面
     */
    public function wjmm(){
        return $this->fetch();
    }

    public function checkYanZheng(){
        $data = input('param.');
        $phone = $data['phone'];
        $code = $data['code'];
        $info = Db::name('code')->where(['phone'=>$phone,'code'=>$code,'status'=>1,'is'=>1])->find();
        if(empty($info)){
            //return json(['message'=>'短信验证码不正确','con'=>2]);
        }else{
            $time = date('YmdHis',time());
            $sqlcode = $info['create_time_str'] + 300;
            if((int)$time>$sqlcode){
                //return json(['message'=>'验证码超时','con'=>2]);
            }
        }
        $pwd = Db::name('user')->field('pwd,pwdH')->where(['userid'=>$phone])->find();
        if(empty($pwd)){
            return json(['message'=>'用户不存在','con'=>2]);
        }
        if($pwd['pwd'] == $data['pwd'] && $pwd['pwdH'] == $data['pwdH']){
            return json(['message'=>'修改成功','con'=>1]);
        }
        Db::name('user')->where(['userid'=>$phone])->update(['pwd'=>$data['pwd'],'pwdH'=>$data['pwdH']]);
        $result = Db::name('code')->where(['phone'=>$phone,'code'=>$code,'status'=>1,'is'=>1])->setField('is',0);
        return json(['message'=>'修改成功','con'=>1]);
    }

    /**
     * @return 网站关闭页面
     */
    public function cancel(){
        return $this->fetch();
    }

}