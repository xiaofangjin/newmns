<?php


namespace app\ffadmin\controller;
use app\common\controller\AdminBase;
use think\Db;
class Index extends AdminBase
{
    public function index(){
        $adminid=session('admin.adminid');
        //本次登录ip
        $ip = request()->ip();
        //上次登录ip
        $data = Db::name('log')->where(['type'=>'登录','admin'=>$adminid,])->order('create_time desc')->limit(2)->select();
        $prevIp = isset($data[1]['ip']) ? $data[1]['ip']  :0;

        //累计登录次数
        $times = Db::name('log')->where(['type'=>'登录','admin'=>$adminid,])->count();
        //注册情况
        //今天注册人数
        $today = date('Y-m-d',time());
        $todayNum = DB::name('user')->where('time','like',$today. '%')->count();
        //今日注册未激活
        $statusNum = DB::name('user')->where('time','like',$today. '%')->where('status',0)->count();

        //新用户
        $newUser = DB::name('user')->where('time','like',$today. '%')->where('is_approve',1)->count();

        //昨天注册人数
        $yesterday  = date("Y-m-d",strtotime("-1 day"));
        $yesterdayNum = DB::name('user')->where('time','like',$yesterday. '%')->count();

        //本月注册
        $mouth = date('Y-m',time());
        $mouthNum = DB::name('user')->where('time','like',$mouth. '%')->count();
        //历史注册
        $totalNum = DB::name('user')->count();

        //累计注册未激活
        $statusTotalNum = DB::name('user')->where('status',0)->count();

        //修改日志
        $logs = Db::name('log')->order('create_time desc')->limit(6)->select();
        $this->assign([
            'logs'=>$logs,
            'todayNum'=>$todayNum,
            'yesterdayNum'=>$yesterdayNum,
            'statusNum'=>$statusNum,
            'totalNum'=>$totalNum,
            'statusTotalNum'=>$statusTotalNum,
            'mouthNum'=>$mouthNum,
            'ip'=>$ip,
            'prevIp'=>$prevIp,
            'times'=>$times,
            'newUser'=>$newUser,
        ]);
        return $this->fetch();
    }
    public function test(){
        return $this->fetch();
    }

}