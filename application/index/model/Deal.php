<?php


namespace app\index\model;


use think\Model;
use think\Db;
class Deal extends Model
{
    public function config(){
        return Db::name('config')->where('id',1)->find();
    }

    /**
     * 按投资金额升级
     */
    public function shengji($num,$user){
       $config = $this->config();
        for($i=1;$i<=5;$i++){
            if($user['kg_level'] < $i){
                if($num >= $config['money' . $i]){
                    Db::name('user')->where('userid',$user['userid'])->setField('kg_level',$i);
                }
            }

        }
    }


    /**
     * 推荐奖
     */
    public function tuijian($num,$user){
        $tuimoney = '';
        $sanshi = explode("-", trim($user['path'], '-')); //字符变数组
        $dao    = array_reverse($sanshi); //数组倒序
        $dao2   = array_slice($dao, 0, 3);
//        dump($dao2);
        $config = $this->config();
        $num = ($num / $config['mns']);
        foreach ($dao2 as $key=>$value){
            //第几代
            $dai = $key + 1;
            if($value > 0){
                //查上级的等级
                $parent = Db::name('user')->field('id,userid,kg_level,path,nowmoney')->where('id',$value)->where('nowmoney','>',0)->find();
//                dump($parent);
                //收益
                if($parent['kg_level'] > 1 && $parent['kg_level'] < 4){
                    if($dai == 1){
                        $tuimoney = $num * $config['dai21'] * 0.01;
                        if(($parent['nowmoney'] - $tuimoney) < 0){
                            $tuimoney = $parent['nowmoney'];
                        }
//                        dump($parent);
                        //1.加收益
                        Db::name('user')->where('id',$value)->setInc('tui',$tuimoney);
                        //2.减出局钱包
                        Db::name('user')->where('id',$value)->setDec('nowmoney',$tuimoney);
                        //3.加总收益
                        Db::name('user')->where('id',$value)->setInc('total',$tuimoney);
                        $this->jl($value,$tuimoney,$parent['kg_level'],$dai,$config['bei'],$user['name']);
                    }
                }else if($parent['kg_level'] == 4){
                    $tuimoney1 = $num * $config['dai4' . $dai]  * 0.01;
                    if(($parent['nowmoney'] - $tuimoney1) < 0){
                        $tuimoney1 = $parent['nowmoney'];
                    }
                    if($dai < 3){
                        Db::name('user')->where('id',$value)->setInc('tui',$tuimoney1);
                        Db::name('user')->where('id',$value)->setInc('total',$tuimoney1);
                        Db::name('user')->where('id',$value)->setDec('nowmoney',$tuimoney1);
                        $this->jl($value,$tuimoney1,$parent['kg_level'],$dai,$config['bei'],$user['name']);
                    }
                }else if($parent['kg_level'] == 5){
                    $tuimoney2 = $num * $config['dai5' . $dai]  * 0.01;
                    if(($parent['nowmoney'] - $tuimoney2) < 0){
                        $tuimoney2 = $parent['nowmoney'];
                    }
                    Db::name('user')->where('id',$value)->setInc('tui',$tuimoney2);
                    Db::name('user')->where('id',$value)->setInc('total',$tuimoney2);
                    Db::name('user')->where('id',$value)->setDec('nowmoney',$tuimoney2);
                    $this->jl($value,$tuimoney2,$parent['kg_level'],$dai,$config['bei'],$user['name']);
                }
            }
        }
    }

    /**
     * 推荐奖加交易记录
     */
    public function jl($value,$num,$xing,$dai,$bei,$show){
        $endmoney = Db::name('user')->field('id,userid,money,nowmoney')->where('id',$value)->find();
        if($endmoney['nowmoney']<= 0){
            Db::name('user')->where('id',$value)->setField('out_time',date('Y-m-d H:i:s',time()));
        }
        //加本金交易明细
        $this->addsymx($endmoney['userid'],$num,$show.'&M'.$xing . '&L'.$dai,$endmoney['money'],6);
        //出局钱包明细
        $this->addsymx2($endmoney['userid'],'-'.$num,'M'.$xing . '&L'.$dai .'&'.$show.'推荐奖',$endmoney['money'],3);
    }


    /**
     * @param添加本金进出明细
     */
    public function addqbmx($userid,$num,$from,$money,$status=''){
        $data= [
            'userid'=>$userid,
            'get_money'=>$num,
            'from'=>$from,
            'money'=>$money,
            'status'=>$status,
            'create_time'=>date('Y-m-d H:i:s',time())
        ];
        return Db::name('money_log')->insert($data);
    }

    /**
     * @param添加静态收益进出明细
     */
    public function addsymx($userid,$num,$from,$money,$status=''){
        $data= [
            'userid'=>$userid,
            'get_money'=>$num,
            'from'=>$from,
            'money'=>$money,
            'status'=>$status,
            'create_time'=>date('Y-m-d H:i:s',time())
        ];
        return Db::name('shouyi_log')->insert($data);
    }

    /**
     * @param添加结算收益进出明细
     */
    public function addsymx2($userid,$num,$from,$money,$status=''){
        $data= [
            'userid'=>$userid,
            'get_money'=>$num,
            'from'=>$from,
            'money'=>$money,
            'status'=>$status,
            'create_time'=>date('Y-m-d H:i:s',time())
        ];
        return Db::name('shouyi2_log')->insert($data);
    }

    /**
     * @param添加usdt进出明细
     */
    public function addusdtmx($userid,$num,$from,$money,$status=''){
        $data= [
            'userid'=>$userid,
            'get_money'=>$num,
            'from'=>$from,
            'money'=>$money,
            'status'=>$status,
            'create_time'=>date('Y-m-d H:i:s',time())
        ];
        return Db::name('usdt_log')->insert($data);
    }
}