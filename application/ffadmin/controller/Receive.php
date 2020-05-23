<?php

namespace app\ffadmin\controller;

use app\common\controller\AdminBase;
use think\Db;
use app\index\model\Deal as DealModel;
/**
 * 会员交易控制器
 */
class Receive extends AdminBase
{
    /**
     * @订单交易状态列表
     * @
     */
    public function receivelist($userid = '', $selluserid = '', $type = '')
    {
        $where = [];
        if (isset($userid) && !empty($userid)) {
            $where['userid'] = ['eq', $userid];
        }
        if (isset($selluserid) && !empty($selluserid)) {
            $where['sell_userid'] = ['eq', $selluserid];
        }
        if (isset($type) && $type !='') {
            $where['is_dakuan'] = ['eq', $type];
        }
        $deal = model('user_ru')->where($where)->order('create_time desc')->paginate(5, false, ['query' => request()->param()]);
        foreach ($deal as $key=>$v){
            $deal[$key]['uphone'] = Db::name('user')->where('userid',$v['userid'])->value('phone');
            $deal[$key]['sphone'] = Db::name('user')->where('userid',$v['sell_userid'])->value('phone');
        }

        $this->assign([
            'deal'       => $deal,
            'userid'     => $userid,
            'selluserid' => $selluserid,
            'type'       => $type,
        ]);
        return $this->fetch();

    }

    /**
     * @冻结/激活订单
     */
    public function editStatus(){
        $id = input('param.id/d');
        $status = Db::name('user_ru')->where('id',$id)->value('status');
        if ($status==1){
            $result = Db::name('user_ru')->where('id',$id)->update(['status'=>0]);
            if ($result){
                $this->addLog('订单表', '修改', json_encode(['status'=>0]), '');
                return json(['message'=>'冻结成功','con'=>1]);
            }else{
                return json(['message'=>'冻结失败','con'=>0]);
            }
        }else{
            $result = Db::name('user_ru')->where('id',$id)->update(['status'=>1]);
            if ($result){
                $this->addLog('订单表', '修改', json_encode(['status'=>1]), '');
                return json(['message'=>'激活成功','con'=>1]);
            }else{
                return json(['message'=>'激活失败','con'=>0]);
            }
        }
    }

    /**
     * @未匹配订单列表
     */
    public function pipeilist($userid = '', $selluserid = '')
    {
        $dealModel = new DealModel();
        $config = $dealModel->config();
        $where = [];
        if (isset($userid) && !empty($userid)) {
            $where['userid'] = ['eq', $userid];
        }

        $deal = model('user_ru')->where($where)->where('is_pipei',0)->order('create_time desc')->paginate(5, false, ['query' => request()->param()]);

        // print_r($news);
        // die();
        $this->assign([
            'deal'       => $deal,
            'userid'     => $userid,
            'config'     => $config,
        ]);
        return $this->fetch();

    }

    /**
     * 匹配订单
     */
    public function pipei(){
        $data = input('param.');
        //买单
        $buy = Db::name('user_ru')->where('id',$data['id'])->find();
        //卖单
        $sell = Db::name('user_chu')->where('id',$data['sellid'])->find();
        if(isset($sell)){
            if($sell['is_pipei'] ==1){
                return json(['message'=>'该订单已匹配','con'=>2]);
            }
            if($sell['is_pipei'] !=1 && $buy['is_pipei'] !=1){
                Db::startTrans();
                try{
                    Db::name('user_ru')->where(['is_pipei'=>0,'id'=>$data['id']])->update(['is_pipei'=>1,'is_dakuan'=>3,'sell_userid'=>$sell['sell_userid'],'pipei_time'=>date('Y-m-d H:i:s',time())]);
                    Db::name('user_chu')->where(['is_pipei'=>0,'id'=>$data['sellid']])->update(['is_pipei'=>1,'pipei_time'=>date('Y-m-d H:i:s',time())]);
                    // 提交事务
                    Db::commit();
//                $this->addLog('订单表', '修改', json_encode(['is_pipei'=>1]), '');
//                return 1;
                    return json(['message'=>'匹配成功','con'=>1]);
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
//                return 2;
                    return json(['message'=>'匹配失败','con'=>0]);
                }

            }
        }else{
            return json(['message'=>'请核实卖单id','con'=>0]);
        }

    }

    /**
     * @未匹配卖单订单列表
     */
    public function spipeilist($selluserid = '')
    {

        $where = [];
        if (isset($selluserid) && !empty($selluserid)) {
            $where['sell_userid'] = ['eq', $selluserid];
        }

        $deal = Db::name('user_chu')->where($where)->where('is_pipei',0)->order('create_time asc')->paginate(5, false, ['query' => request()->param()]);

        // print_r($news);
        // die();
        $this->assign([
            'deal'       => $deal,
            'selluserid' => $selluserid,
        ]);
        return $this->fetch();

    }

    /**
     * @冻结/激活卖单
     */
    public function seditStatus(){
        $id = input('param.id/d');
        $status = Db::name('user_chu')->where('id',$id)->value('status');
        if ($status==1){
            $result = Db::name('user_chu')->where('id',$id)->update(['status'=>0]);
            if ($result){
                $this->addLog('订单表', '修改', json_encode(['status'=>0]), '');
                return json(['message'=>'冻结成功','con'=>1]);
            }else{
                return json(['message'=>'冻结失败','con'=>0]);
            }
        }else{
            $result = Db::name('user_chu')->where('id',$id)->update(['status'=>1]);
            if ($result){
                $this->addLog('订单表', '修改', json_encode(['status'=>1]), '');
                return json(['message'=>'激活成功','con'=>1]);
            }else{
                return json(['message'=>'激活失败','con'=>0]);
            }
        }
    }

    /**
     * 删除订单
     */
    public function  deleteOrder()
    {
        $id = input('param.id/d');
        $result = Db::name('user_ru')->where('id', $id)->delete();
        if ($result) {
            $this->addLog('订单表', '删除','', '');
            return json(['con' => 1]);
        } else {
            return json(['con' => 0]);
        }
    }


  
      /**
     * @param 投诉订单
     */
    public function tousulist($userid='',$sell_userid=''){
        $where = [];
        if (isset($userid) && !empty($userid)) {
            $where['userid'] = ['eq', $userid];
        }
        if (isset($sell_userid) && !empty($sell_userid)) {
            $where['sell_userid'] = ['eq', $sell_userid];
        }
        $tousu = Db::name('user_ru')->where($where)->order('create_time desc')->where('is_tousu',1)->paginate(5, false, ['query' => request()->param()]);;
        $this->assign([
            'tousu'=>$tousu,
            'userid'=>$userid,
            'sell_userid'=>$sell_userid,
        ]);
        return $this->fetch();
    }

    /**
     * 投诉后封买家账号
     */
    public function dongBuy(){
        $config = Db::name('config')->where('id',1)->find();
        $id = input('param.id');
        $info = Db::name('user_ru')->field('id,userid,sell_userid,num,today_money,is_dakuan,is_outtime')->where(['id'=>$id,'is_tousu'=>1,'is_outtime'=>0,'chuli'=>0])->find();
//        dump($id);
        if($info){
            Db::startTrans();
            try{
                //冻结买家账号
                Db::name('user')->where('userid',$info['userid'])->setField('status',0);
                $money = Db::name('user')->field('id,money')->where('userid',$info['sell_userid'])->find();
                //返卖家的余额
                $sell_bili = $config['xtfei'];
                $dealNum = $info['num'] + $sell_bili;
                $money = $money['money'] + $dealNum;
                Db::name('user')->where('userid',$info['sell_userid'])->update(['money'=>$money]);
              	$endmoney = Db::name('user')->field('money')->where('userid',$info['sell_userid'])->find();
                 $this->addqbmx($info['sell_userid'],$dealNum,'卖家投诉订单后返还',$endmoney['money'],10);
                Db::name('user_ru')->where('id',$id)->setField('chuli',1);
                // 提交事务
                Db::commit();
                //更新订单状态
                return json(['con'=>1]);
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return json(['con'=>2]);
            }

        }
    }

    /**
     * 投诉后封卖家账号
     */
    public function dongSell(){
        $id = input('param.id');
        $config = Db::name('config')->where('id',1)->find();
        $info = Db::name('user_ru')->field('id,userid,sell_userid,num,today_money,is_dakuan,is_outtime,level')->where(['id'=>$id,'is_tousu'=>1,'is_outtime'=>0,'chuli'=>0])->find();
        if($info){
            Db::startTrans();
            try{
                //冻结卖家账号
                Db::name('user')->where('userid',$info['sell_userid'])->setField('status',0);
                $money = Db::name('user')->field('id,money')->where('userid',$info['userid'])->find();
                //返买家的余额
                $dealNum = $info['num'];
                $money = $money['money'] + $dealNum;
                Db::name('user')->where('userid',$info['userid'])->update(['money'=>$money]);
                $endmoney = Db::name('user')->field('id,money,deal')->where('userid',$info['userid'])->find();
                $this->addqbmx($info['userid'],$dealNum,'投诉订单后返买家',$endmoney['money'],11);
                Db::name('user_ru')->where('id',$id)->setField('chuli',1);
                if($endmoney['deal'] !=1){
                    Db::name('user')->where('userid',$info['userid'])->setField('deal',1);
                }
                $dealModel = new DealModel;
                 //提交事务
                Db::commit();
                return json(['con'=>1]);
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return json(['con'=>2]);
            }
        }
    }

  
      /**
     * @param添加矿池钱包进出明细
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
        return  Db::name('money_log')->insert($data);
    }

}