<?php


namespace app\ffadmin\controller;
header('Content-Type: text/html; charset=UTF-8');

use app\index\model\User as UserModel;
use think\Db;
use think\Controller;
class Login extends Controller{
    protected $beforeActionList = [
        'checkAdmin' => ['except' => 'login,login11,code'],
    ];

//    // 校验管理员登录状态
    public function checkAdmin()
    {
        if (!session('?admin')) {
            $this->error('请先登录', url('ffadmin/login/login'));
        }
    }


    /**
     * @return mixed管理员登录
     */
    public function login($code = '')
    {
//
            return $this->fetch();
//        }

    }

    public function login11()
    {
        $data = input('param.');
        $adminid = $data['admin'];
        $pwd = $data['pwd'];
        //判断验证码是否正确
//        $code = $data['code'];
        $phone = $data['admin'];
//        $information = Db::name('code')->where('phone',$phone)->where('code',$code)->where('is',1)->where('status',3)->find();
//        if(empty($information)){
//            return json(['message'=>'短信验证码不正确','con'=>0]);
//        }else{
//            $time = date('YmdHis',time());
//            $sqlcode = $information['create_time_str'] + 300;
//            if((int)$time>$sqlcode){
//                return json(['message'=>'验证码超时','con'=>0]);
//            }
//        }
//        Db::name('code')->where('phone',$phone)->where('code',$code)->where('is',1)->where('status',3)->setField('is',0);
        $result = DB::name('admin')->field('id,adminid,role')->where('adminid', $adminid)->where('pwd', $pwd)->find();
        if (isset($result)) {
            session('admin', $result);
            session('role',$result['role']);
            return json(['con' => 1,'message'=>'登录成功']);
        } else {
            return json(['con' => 0,'message'=>'请核实登录账号和密码']);
        }
    }


    /**
     * 添加操作日志
     * @param $t_name
     * @param $type
     * @param $data
     * @param $userid
     */
    public function addLog($t_name, $type, $data, $userid)
    {
        $ip = request()->ip();
        $arr = [
            't_name' => $t_name,
            'type' => $type,
            'userid' => $userid,
            'admin' => session('admin.adminid'),
            'data' => $data,
            'ip' => $ip,
            'create_time' => time(),
        ];
        Db::name('log')->insert($arr);
    }

    /**
     * 退出登录
     */
    public function logout()
    {
        session('adminid', null);
        $this->redirect(url('ffadmin/login/login'));
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
            $re = Db::name('admin')->where('phone',$mobile)->value('id');
            if($data['status'] == 3){
                if(empty($re)){
//                    return json(['msg'=>'用户不存在']);
                }
            }
            $this->yanzheng($code, $mobile);
            $userModel = new UserModel();
            $userModel -> codeModel($data,$code);
            return json(['msg'=>'发送成功']);
        }
    }

    public function yanzheng($code='', $mobile,$type=''){

        $a = 'BLT123';
        $b = 'qq123456..';
        $d = '【BiIdt】您的验证码是'.$code.',请及时查看,此验证码三分钟内有效';
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
}