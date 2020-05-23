<?php

namespace app\ffadmin\controller;

use app\common\controller\AdminBase;
use think\Controller;
use think\Db;
use app\index\model\Deal as DealModel;
/**
 * 财务控制器
 */
class Money extends AdminBase
{
    /**
     * 账户充值
     * @param string $value [description]
     */
    public function rechargelist()
    {
        return $this->fetch();
    }

    /**
     * 账户充值提交表单
     * @return [type] [description]
     */
    public function doRecharge()
    {
        $shuAll = [];
        $dealModel = new DealModel();
        $config = $dealModel->config();
        $data = input('param.');
        // Array ( [u_name] => 111 [money] => 10 [money_type] => b [ub_msg] => 也一样 )
        $userid = $data['u_name'];
        $money  = $data['money'];
        $user   = Db::name('user')->where('userid', $data['u_name'])->find();
        if (empty($user)) {
            $this->error('账号不存在');
        }
        if ($data['money_type'] == 1) {
            //水滴
            $result = Db::name('user')->where('userid', $userid)->setInc('usdt', $money);
            $this->addLog('用户表', '修改', '{"money":"' . $money . '"}', $userid);

            //添加充值记录
            //1.查询原有余额
            $moneynow = Db::name('user')->where('userid', $userid)->value('usdt');

            $arr = [
                'userid'      => $userid,
                'from'        => '充值到usdt',
                'get_money'   => $money,
                'money'       => $moneynow,
                'wallet'      => 'usdt',
                'create_time' => date('Y-m-d H:i:s',time()),
                'comment'     => $data['ub_msg'],
            ];
            $result = Db::name('admin_money')->insert($arr);
            $this->addLog('后台充值表', '新增', json_encode($arr), '');

        }
        if ($data['money_type'] == 2) {
            //树
            $result = Db::name('user')->where('userid', $userid)->setInc('total', $money);
            $this->addLog('用户表', '修改', '{"shu":"' . $money . '"}', $userid);
            //添加充值记录
            //1.查询原有余额
            $moneynow = Db::name('user')->where('userid', $userid)->value('total');
                $arr = [
                    'userid'      => $userid,
                    'from'        => '充值到提现钱包',
                    'get_money'   => $money,
                    'money'       => $moneynow,
                    'wallet'      => '提现钱包',
                    'create_time' => time(),
                    'comment'     => $data['ub_msg'],
                ];
                $result = Db::name('admin_money')->insert($arr);
        }
        if ($result) {
            $this->success('充值成功', url('ffadmin/money/banklist'));
        } else {
            $this->error('充值失败');
        }
    }

    /**
     * 后台充值财务明细
     * @param  string $userid 搜索输入的用户名
     * @param  string $date   搜索输入的日期
     * @return [type]         [description]
     */
    public function banklist($userid = '', $date = '')
    {
        $where = [];
        // 時間
        if (isset($date) && !empty($date)) {
            $time    = $date;
            $timeArr = explode(' - ', $time);
            // dump($timeArr); //2019-04-03 16:22:02
            $min = strtotime($timeArr[0] . ' 00:00:00');
            $max = strtotime($timeArr[1] . ' 23:59:59');
            // print_r($min);
            // dump($max);
            // die;
            $where['create_time'] = ['between', [$min, $max]];
        }
        //用戶名
        if (isset($userid) && !empty($userid)) {
            $where['userid'] = ['eq', $userid];
        }
        $money = Db::name('admin_money')->where($where)->order('create_time desc')->paginate(15, false, ['query' => request()->param()]);
        $this->assign([
            'money'  => $money,
            'userid' => $userid,
            'date'   => $date,
        ]);
        return $this->fetch();
    }

    /**
     * @return int
     * @throws \think\Exception
     * @throws \think\exception\PDOException提现列表
     */
    /**
     * @param 提现明细
     */
    public function txlist($userid = '', $date = '')
    {
        $where = [];
        //時間
        if (isset($date) && !empty($date)) {
            $time                 = $date;
            $timeArr              = explode(' - ', $time);
            $min                  = $timeArr[0] .' 00:00:00';
            $max                  = $timeArr[1] .' 23:59:59';
            $where['create_time'] = ['between', [$min, $max]];
        }
        //用戶名
        if (isset($userid) && !empty($userid)) {
            $where['userid'] = ['eq',$userid];
        }
        $money3 = Db::name('tixian_log')->where($where)->order('create_time desc')->paginate(15, false, ['query' => request()->param()]);
        $this->assign([
            'money3' => $money3,
            'userid' => $userid,
            'date'   => $date,
        ]);
        return $this->fetch();
    }

    /**
     * @return 改提现状态
     */
    public function editStatus(){
        $dealModel = new DealModel();
        $data = input('param.');
        $id = $data['id'];
        $type = $data['type'];
        $result = Db::name('tixian_log')->where('id',$id)->find();
        if($result['type'] <=0){
            if($type == 1){
                Db::startTrans();
                try{
                     Db::name('tixian_log')->where('id',$id)->setField('type',1);
                     Db::name('tixian_log')->where('id',$id)->setField('status',1);
                    $userid = Db::name('tixian_log')->where('id',$id)->value('userid');
                    $user = Db::name('user')->field('id,userid,kg_level,is_xian,total,kg_level,nowmoney,is_3000')->where('userid',$userid)->find();
                    //如果是5星提现金额每满3000减1000本金
                    if($user['kg_level'] == 5){
                        if($user['is_xian'] !=1){
                            $sum = Db::name('tixian_log')->where('userid',$userid)->where('status',1)->sum('money');
                            $shang = intval($sum/3000);
                            if($shang > $user['is_3000']){
                                //减本金
                                Db::name('user')->where('userid',$userid)->setDec('money',1000);
                                //改状态
                                Db::name('user')->where('userid',$userid)->setField('is_3000',$shang);
                                $endmoney = Db::name('user')->where('userid',$userid)->value('money');
                                //如果减掉之后本金小于0，冻结账号
                                if($endmoney <=0){
                                    Db::name('user')->where('userid',$userid)->setField('status',0);
                                }
                                $dealModel->addqbmx($userid,-1000,'提现满3000减1000',$endmoney,12);
                            }
                        }
                    }
                    // 提交事务
                    Db::commit();
                    return json(['message'=>'确认成功','con'=>1]);
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    return json(['message'=>'确认失败','con'=>2]);
                }
            }else{
                $res = Db::name('tixian_log')->where('id',$id)->setField('type',2);
                //返金额
                $userorder = Db::name('tixian_log')->where('id',$id)->find();
                $userid = $userorder['userid'];
                //转为mns
                $money = round($userorder['money']/$userorder['price'],2);
                Db::name('user')->where('userid',$userid)->setInc('total',$money);
                $endmoney = Db::name('user')->where('userid',$userid)->value('total');
                $this->addsymx($userid,$money,'后台驳回提现返还',$endmoney,7);
                if($res){
                    return json(['message'=>'驳回成功','con'=>1]);
                }else{
                    return json(['message'=>'驳回失败','con'=>2]);
                }
            }

        }
    }

    /**
     * @param钱包明细
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
        return  Db::name('shouyi_log')->insert($data);
    }

}