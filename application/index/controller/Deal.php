<?php


namespace app\index\controller;


use app\common\controller\IndexBase;
use think\Db;
use app\index\model\Deal as DealModel;
use app\index\model\User as UserModel;
class Deal extends IndexBase
{

    /**
     * 交易市场
     */
    public function jy(){
        $dealModel = new DealModel();
        $config = $dealModel->config();
        $userid = session('user.userid');
        $user = Db::name('user')->where('userid',$userid)->find();
        $this->assign([
            'user'=>$user,
            'config'=>$config,
        ]);
        return $this->fetch();
    }


    /**
     * 买入
     */
    public function buy(){
        $bei = '';
        $dealModel = new DealModel();
        $config = $dealModel->config();
        $userid = session('user.userid');
        $data = input('param.');
        $num = abs($data['num']);
        $user = Db::name('user')->where('userid',$userid)->find();
        //当前用户的usdt
        $dealnum = $user['usdt'];
        if($num > $dealnum){
            return json(['message'=>'您的余额不足','con'=>2]);
        }
        if($user['status'] != 1){
            return json(['message'=>'请核实会员状态','con'=>2]);
        }
        //判断用户等级，5星以上需1000倍数投资，5星以下50倍数复投
        if($user['kg_level'] <5){
            $bei = $config['ftbei1'];
        }else if($user['kg_level'] == 5){
            $bei = $config['ftbei2'];
        }
        if(($num % $bei) !=0 || $num<=0){
            return json(['message'=>'请输入'.$bei.'的倍数','con'=>2]);
        }
        if(($num + $user['money'])  >$config['maxmoney']){
            return json(['message'=>'超出最大金额'.$config['money5'],'con'=>2]);
        }
        //按差买入
        if($user['kg_level'] < 5){
            $buymoney = $user['money'] + $num;
            $cha = $config['money5'] - $user['money'];
            if($buymoney > $config['money5']){
                return json(['message'=>'您只需买入'.$cha.'即可升5星','con'=>2]);
            }
        }
        Db::startTrans();
        try{
            //usdt
            Db::name('user')->where('userid',$userid)->setDec('usdt',$num);
            //本金
            Db::name('user')->where('userid',$userid)->setInc('money',$num);
            //设置投资时间
            Db::name('user')->where('userid',$userid)->setField('tz_time',date('Y-m-d H:i:s',time()));
            //记录用利益
            //1.如果小于0就重置为0
            if($user['nowmoney'] < 0){
                Db::name('user')->where('userid',$userid)->setField('nowmoney',0);
            }
            //2.投资按出局倍数加结算收益，判定是否出局
            Db::name('user')->where('userid',$userid)->setInc('nowmoney',$config['bei'] * $num/ $config['mns']);
            $endmoney = Db::name('user')->field('id,money,usdt,nowmoney')->where('userid',$userid)->find();
            $dealModel -> addusdtmx($userid,$num,'买入'.$num.'mns扣除',$endmoney['usdt'],1);
            $dealModel -> addqbmx($userid,$num,'买入',$endmoney['money'],1);
            $dealModel -> addsymx2($userid,$config['bei'] * $num,'投资本金'.$num,$endmoney['nowmoney'],1);
            // 提交事务
            Db::commit();
            //按购币等数量升级
            $dealModel->shengji($endmoney['money'],$user);
            //第一次投资显示金额
            if($user['is_first'] != 1){
                //首次投资有推荐奖
                $dealModel->tuijian($endmoney['money'],$user);
                Db::name('user')->where('userid',$userid)->setField('first',$num);
                Db::name('user')->where('userid',$userid)->setField('is_first',1);
            }
            return json(['message'=>'买入成功','con'=>1]);
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return json(['message'=>'系统繁忙，请稍后重试','con'=>2]);

        }
    }

    public function test(){
        $userModel = new UserModel();
        $userModel ->jiedian(165);
    }


}