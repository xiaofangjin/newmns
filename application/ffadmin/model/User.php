<?php

namespace app\ffadmin\model;

use app\index\model\Center as CenterModel;
use think\Db;
use think\Model;

/**
 * 用户模型
 */
class User extends Model
{

    public function getUserLevelAttr($value)
    {
        $status = [
            1 => '一级矿工',
            2 => '二级矿工',
            3 => '三级矿工',
            4 => '四级矿工',

        ];
        return $status[$value];
    }

    /**
     * @param $userid
     * @throws /实名认证1600矿池资产，两台体验矿机和一台微型矿机
     */
    public function zengsong($userid){
//        $dealModel = new DealModel;
//        $config = $dealModel->config();//sm_kczc,sm_kj,sm_approve
        Db::startTrans();
        try{
            $config = Db::name('config')->where('id',1)->find();
            $num = $config['sm_kczc'];
            $num1 = $config['sm_kj1'];
            Db::name('user')->where('userid',$userid)->setInc('kuangchi_money',$num);
            $result = Db::name('user')->where('userid',$userid)->field('kuangchi_money,pid,id')->find();
            $money = $result['kuangchi_money'];
            //添加充值记录
            $array = [
                'userid'=>$userid,
                'from'=>'实名认证赠送',
                'get_money'=>$num,
                'money'=>$money,
                'status'=>'2',
                'create_time'=>date('Y-m-d H:i:s',time())
            ];
            Db::name('kuangchi_money_log')->insert($array);
            //送可售额度
            Db::name('user')->where('userid',$userid)->setInc('keshou_money',$num1);
            $endmoney = Db::name('user')->where('userid',$userid)->value('keshou_money');
            $array1 = [
                'userid'=>$userid,
                'from'=>'实名认证赠送',
                'get_money'=>$num1,
                'money'=>$endmoney,
                'status'=>8,
                'create_time'=>date('Y-m-d H:i:s',time())
            ];
            Db::name('keshou_money_log')->insert($array1);
            //        一台微型矿机
            $arr1 = $this->kuangji(1,$userid,$type=2);
            for ($i=1;$i<=$config['sm_kj1']; $i++){
                Db::name('user_level_log')->insert($arr1);
            }
            // 提交事务
            Db::commit();
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
        }

    }


    /**
     * @param 矿机类型
     */
    public function kuangji($id,$userid,$type){
        $kuangji = Db::name('user_level')->where('id',$id)->find();
        $arr = [
            'userid'=>$userid,
            'user_level_id'=>$kuangji['id'],
            'user_level_name'=>$kuangji['name'],
            'buy_money'=>$kuangji['money'],
            'get_money'=>$kuangji['get_money'],
            'day'=>$kuangji['life_time'],
            'type'=>$type,
            'img'=>$kuangji['img'],
            'create_time'=>date('Y-m-d H:i:s',time()),
            'create_time_no'=>time(),
            'shifang_time'=>time(),
        ];
        return $arr;
    }

    /**
     * @param添加矿池资产进出明细
     */
    public function addKczcjl($userid,$num,$from,$money,$status=''){

        $data= [
            'userid'=>$userid,
            'get_money'=>$num,
            'from'=>$from,
            'money'=>$money,
            'status'=>$status,
            'create_time'=>date('Y-m-d H:i:s',time())
        ];
        return  Db::name('kuangchi_money_log')->insert($data);
    }
}