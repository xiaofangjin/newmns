<?php


namespace app\index\model;


use think\Db;
use think\Model;
use app\index\model\Deal as DealModel;
class Index extends Model
{
    /**
     * 领取水滴往上级返
     */
    public function fan($user){
        $dealModel = new DealModel();
        $config = $dealModel->config();
        $sanshi = explode("-", trim($user['path'], '-')); //字符变数组
        $dao    = array_reverse($sanshi); //数组倒序
        $dao2   = array_slice($dao, 0, 3);
        foreach ($dao2 as $k=>$v){
            $a = $k+1;
            if($v>0){
                //返上级水滴
                if($config['fenhong'.$a] > 0){
                    $result = Db::name('user')->where('id',$v)->setInc('money',$config['fenhong'.$a]);
                    $result = Db::name('user')->where('id',$v)->setInc('nowmoney',$config['fenhong'.$a]);
                     $aa = Db::name('user')->where('id',$v)->setInc('liyi',$config['fenhong'.$a]);
                    if($result && $aa){
                        $parent = Db::name('user')->field('id,userid,money,liyi')->where('id',$v)->find();
                        $dealModel->addqbmx($parent['userid'],$config['fenhong'.$a],$a.'代利益',$parent['money'],7);
                        $this->addlymx($parent['userid'],$config['fenhong'.$a],$a.'代利益',$parent['liyi'],1);
                    }
                }

            }
        }
    }

    /**
     * 直推种树返，团队种树返
     */
    public function jiang($user){
        //直推
        $dealModel = new DealModel();
        $config = $dealModel->config();
        if($user['is_tui'] !=1){
            $parant = Db::name('user')->where(['id'=>$user['pid'],'deal'=>1])->where('shu','>=',1)->setInc('money',$config['zhitui']);
            $endmoney = Db::name('user')->field('id,userid,money')->where(['id'=>$user['pid']])->find();
            if($parant){
                $bb = $this->addqbmx($endmoney['userid'],$config['zhitui'],'直推人'.$user['userid'].'种树奖励',$endmoney['money'],12);
                Db::name('user')->where(['id'=>$user['id']])->setField('is_tui',1);
            }
        }
        //团队
        if($user['is_team'] !=1){
            $sanshi = explode("-", trim($user['path'], '-')); //字符变数组
            $dao    = array_reverse($sanshi); //数组倒序
            foreach ($dao as $k=>$v){
                if($v>0){
                    $team = Db::name('user')->where(['id'=>$v,'deal'=>1])->where('shu','>=',1)->setInc('money',$config['team']);
                    $endmoney1 = Db::name('user')->field('id,userid,money')->where(['id'=>$v])->find();
                    if($team){
                        $aa = $this->addqbmx($endmoney1['userid'],$config['team'],'团队人'.$user['userid'].'种树奖励',$endmoney1['money'],13);
                        Db::name('user')->where(['id'=>$user['id']])->setField('is_team',1);
                     
                    }
                }
            }
        }


    }

    /**
     * @param添加钱包进出明细
     */
    public function addlymx($userid,$num,$from,$money,$status=''){
        $data= [
            'userid'=>$userid,
            'get_money'=>$num,
            'from'=>$from,
            'money'=>$money,
            'status'=>$status,
            'create_time'=>date('Y-m-d H:i:s',time())
        ];
        return Db::name('liyi_log')->insert($data);
    }

    /**
     * @param添加钱包进出明细
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
}