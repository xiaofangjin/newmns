<?php
namespace app\index\controller;

use app\common\controller\IndexBase;
use think\Controller;
use think\Db;
use app\index\model\Deal as DealModel;
use app\index\model\Index as IndexModel;
class Index extends IndexBase
{
    /**
     * @return mixed首页
     */
    public function index()
    {
        $userid = session('user.userid');
        $chuju = '';
        $tixian = '';
        $dealModel = new DealModel();
        $config = $dealModel->config();
        $user = Db::name('user')->where('userid',$userid)->find();
        //当日静态收益
        $jing = Db::name('shouyi_log')->where('userid',$userid)->whereTime('create_time', 'today')->where('status',1)->sum('get_money');
        //当日动态收益
        $dong = Db::name('shouyi_log')->where('userid',$userid)->whereTime('create_time', 'today')->where('status','in','2,3,6')->sum('get_money');
        //总业绩
        $total = Db::name('user')->where('path','like','%'.$user['path'].'-'.$user['id'].'%')->sum('money');
        //出局金额
        $chuju = ($config['bei'] * $user['money']);
        //进度条，提现金额/出局金额
        if($chuju > 0){
            //满3000减1000，需要减掉提现的金额
            $count = Db::name('money_log')->where(['userid'=>$userid,'status'=>12])->count('id');
            $tixian = $user['tixian'] - $count * 3000;
            $jin =round(($tixian / $chuju),2) * 100;
        }else{
            $jin = 0;
        }
        $chuju = ($config['bei'] * $user['money']);
        $this->assign([
            'user'=>$user,
            'config'=>$config,
            'jing'=>$jing,
            'dong'=>$dong,
            'bei'=>$config['mnsbei'],
            'jin'=>$jin,
            'chuju'=>$chuju,
            'total'=>$total,
        ]);
        return $this->fetch();
    }

    /**
     * 充值
     */
    public function cz(){
        return $this->fetch();
    }

    /**
     * 提现
     */
    public function tx(){
        $dealModel = new DealModel();
        $config = $dealModel->config();
        $userid = session('user.userid');
        $user = Db::name('user')->field('id,userid,total,kg_level')->where('userid',$userid)->where('status',1)->find();
        $money  = round($user['total'] * $config['mns'],2);
        $this->assign([
            'xu'=>$config['shouxu'],
            'full'=>$config['full'],
            'money'=>$money,
        ]);
        return $this->fetch();
    }

    /**
     * 提现保存数据
     */
    public function tixian(){
        $dealModel = new DealModel();
        $config = $dealModel->config();
        $userid = session('user.userid');
        $data = input('param.');
        $pwd = $data['password'];
        $num = abs($data['num']);
        $user = Db::name('user')->field('id,userid,pwdH,total,kg_level,nowmoney,is_3000,is_xian,tixian,money')->where('userid',$userid)->where('status',1)->find();
        //限制每天提现次数
        if($user['is_xian'] != 1){
            $tixian = Db::name('tixian_log')->where('userid',$userid)->whereTime('create_time','today')->count('id');
            if($tixian >=$config['tixian']){
                return json(['message'=>'每天只能提现'.$config['tixian'].'次','con'=>2]);
            }
        }
        //提现需要把mns转为usdt
        //1.现有usdt
        $money  = round($user['total'] * $config['mns'],2);
        //2.提现要扣的mns
        $txmoney = round($num / $config['mns'],2);
        //手续费,转为mns
        $shouxu = round($config['shouxu'] / $config['mns'],2);
        //要扣的总mns
        $totalxu = $txmoney + $shouxu;
        if($num < $config['full']){
            return json(['message'=>'请输入大于'.$config['full'].'的金额','con'=>2]);

        }
        if($user['pwdH'] != $pwd){
            return json(['message'=>'支付密码不正确','con'=>2]);
        }
        if(!$user){
            return json(['message'=>'请核实用户状态','con'=>2]);
        }
        if(($num + $config['shouxu']) > $money){
            return json(['message'=>'余额不足','con'=>2]);
        }

        //出局金额
        if($user['is_xian'] != 1){
            $chuju = $config['bei'] * $user['money'];
            if(($user['tixian'] + $num+$config['shouxu']) >= $chuju){
                return json(['message'=>'超出出局金额，请核实','con'=>2]);
            }
        }

        $arr = [
            'userid'=>$userid,
            'address'=>$data['address'],
            'money'=>$num+$config['shouxu'],
            'price'=>$config['mns'],
            'create_time'=>date('Y-m-d H:i:s',time()),
        ];
        //提现减金额
        Db::name('user')->where('userid',$userid)->setDec('total',$totalxu);
		Db::name('user')->where('userid',$userid)->setInc('tixian',$num+$config['shouxu']);
        $endmoney1 = Db::name('user')->where('userid',$userid)->value('total');
        $dealModel->addsymx($userid,'-'.$totalxu,'提现'.$num.'USDT扣除',$endmoney1,5);
        $result = Db::name('tixian_log')->insert($arr);
        if($result){
            return json(['message'=>'提币成功','con'=>1]);
        }else{
            return json(['message'=>'系统繁忙，请稍后','con'=>2]);
        }
    }

    /**
     * 复投
     */
    public function ft(){
        $bei = '';
        $money = '';
        $userid = session('user.userid');
        $dealModel = new DealModel();
        $config = $dealModel->config();
        $user = Db::name('user')->field('id,userid,pwdH,total,kg_level')->where('userid',$userid)->where('status',1)->find();
        $money = $user['total'] * $config['mns'];
        if($user['kg_level'] <5){
            $bei = $config['ftbei1'];
        }else if($user['kg_level'] == 5){
            $bei = $config['ftbei2'];
        }
        $this->assign([
            'bei'=>$bei,
            'money'=>$money,
            ]);
        return $this->fetch();
    }

    /**
     * 复投存数据
     */
    public function futou(){
        $bei = '';
        $dealModel = new DealModel();
        $config = $dealModel->config();
        $userid = session('user.userid');
        $data = input('param.');
        $pwd = $data['password'];
        $num = abs($data['num']);
        $user = Db::name('user')->field('id,userid,pwdH,total,kg_level,nowmoney,money')->where('userid',$userid)->where('status',1)->find();
        if($user['kg_level'] <5){
            $bei = $config['ftbei1'];
        }else if($user['kg_level'] == 5){
            $bei = $config['ftbei2'];
        }
        if(($num % $bei) !=0 || $num<=0){
            return json(['message'=>'请输入'.$bei.'的倍数','con'=>2]);

        }

        if(($user['money'] + $num) >$config['maxmoney']){
            return json(['message'=>'超出最大金额'.$config['maxmoney'],'con'=>2]);
        }
        if($user['pwdH'] != $pwd){
            return json(['message'=>'支付密码不正确','con'=>2]);
        }
        if(!$user){
            return json(['message'=>'请核实用户状态','con'=>2]);
        }
        if($num > $user['total']){
            return json(['message'=>'余额不足','con'=>2]);
        }
        //加投资金额
        Db::startTrans();
        try{
            //1.减收益金额
            Db::name('user')->where('userid',$userid)->setDec('total',$num);
            //2.加本金
            Db::name('user')->where('userid',$userid)->setInc('money',$num);
            //设置投资时间
            Db::name('user')->where('userid',$userid)->setField('tz_time',date('Y-m-d H:i:s',time()));
            //3.加分红钱包
//            Db::name('user')->where('userid',$userid)->setInc('hong',$num);
            //记录出局金额
            //1.如果小于0就重置为0
            if($user['nowmoney'] < 0){
                Db::name('user')->where('userid',$userid)->setField('nowmoney',0);
            }
            //2.投资按出局倍数加结算收益，判定是否出局
            Db::name('user')->where('userid',$userid)->setInc('nowmoney',$config['bei'] * $num / $config['mns']);
            $endmoney = Db::name('user')->field('id,money,usdt,nowmoney')->where('userid',$userid)->find();
            //添加收益明细
            $dealModel -> addsymx($userid,'-'.$num,'复投'.$num.'mns扣除',$endmoney['usdt'],4);
            $dealModel -> addqbmx($userid,$num,'复投',$endmoney['money'],3);
            // 提交事务
            Db::commit();
            //按购币等数量升级
            $dealModel->shengji($endmoney['money'],$user);
            return json(['message'=>'复投成功','con'=>1]);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return json(['message'=>'系统繁忙，请稍后重试','con'=>2]);

        }
    }

}
