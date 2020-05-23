<?php


namespace app\index\controller;
use think\Controller;
use think\Db;
use app\index\model\Deal as DealModel;
class Important extends Controller
{
    public function config(){
        return Db::name('config')->where('id',1)->find();
    }

    /**
     * 静态收益
     */
    public function jing(){
        $jiang = '';
        $config = $this->config();
        $tday = date('Y-m-d');
//        $userAll = Db::name('user')->field('id,userid,path,kg_level,level,money,nowmoney,name')
//            ->where('status',1)->where('kg_level','>=',1)->where('nowmoney','>',0)
//            ->where('day','> time',$tday)->select();
        //找没出局，星级大于1星没有冻结的账号
        $userAll = Db::name('user')->field('id,userid,path,kg_level,level,money,nowmoney,name')
            ->where('status',1)->where('kg_level','>=',1)->where('nowmoney','>',0)->select();
//        dump($userAll);
        foreach ($userAll as $key=>$value){
            $user = Db::name('user')->field('id,userid,path,kg_level,level,money,nowmoney,name')->where('userid',$value['userid'])->find();
//            dump($user);
            $level = $user['kg_level'];
            for($i=1;$i<=5;$i++){
                if($level == $i){
                    if($config['shouyi'.$i] >0){
                        //兑换成mns分红
                        $jiang = ($user['money']/$config['mns']) * $config['shouyi'.$i] * 0.01;
                        if($jiang > 0 ){
                            //如果收益等于0,则代表达到出局要求（3倍）,不再收益
                            if($user['nowmoney'] > 0){
                                $nowmoney = $user['nowmoney'];
                                //如果结算金额减本次收益金额小于零，那本次收益金额就等于结算金额
                                if(($nowmoney-$jiang)<0){
                                     $jiang = $nowmoney;
                                }
                                Db::startTrans();
                                try{
//                                    dump($user);
                                    //加静态收益
                                    Db::name('user')->where('userid',$user['userid'])->setInc('liyi',$jiang);
                                    //加总收益
                                    Db::name('user')->where('userid',$user['userid'])->setInc('total',$jiang);
                                    //减结算的静态收益
                                    Db::name('user')->where('userid',$user['userid'])->setDec('nowmoney',$jiang);

                                    $endmoney = Db::name('user')->field('id,liyi,nowmoney,userid')->where('userid',$user['userid'])->find();
                                    //如果结算收益为0说明达到了出局条件，添加出局时间
                                    if($endmoney['nowmoney'] <=0){
                                        Db::name('user')->where('userid',$user['userid'])->setField('out_time',date('Y-m-d H:i:s',time()));
                                    }
                                    $aa = $this->addsymx($user['userid'],$jiang,$i.'级静态收益',$endmoney['liyi'],1);
                                    $this->addsymx2($user['userid'],'-'.$jiang,$i.'级静态收益',$endmoney['nowmoney'],2);
                                    //业绩奖
                                    $this->yeji($user,$jiang);
                                    //团队奖
                                    $this->team($user,$jiang);
                                    // 提交事务
                                    Db::commit();
                                } catch (\Exception $e) {
                                    // 回滚事务
                                    Db::rollback();
                                }
                            }

                        }
                    }
                }
            }
        }
    }

    /**
     * 业绩奖
     */
    public function yeji($user,$shouyi){
//        $user = Db::name('user')->where('userid','16621')->find();
//        $shouyi = 100;
        $sanshi = explode("-", trim($user['path'], '-')); //字符变数组
        $dao    = array_reverse($sanshi); //数组倒序
        $dai = '';
        foreach ($dao as $key=>$value) {
            $dai = $key + 1;
            $parent = Db::name('user')->field('id,userid,kg_level,path,name')->where('id', $value)->where('nowmoney','>',0)->find();
            if($parent['kg_level'] >=1){
                //一星，二星
                if($dai == 1) {
                    $res = $this->yjmx($value, $shouyi, $user['kg_level'], 1, $parent['userid'],$dai,$user['name']);
                }else{
                    if(($parent['kg_level'] == 2) && $dai == 2){
                        $this->yjmx($value,$shouyi,$user['kg_level'],$dai,$parent['userid'],$dai,$user['name']);
                    } else if ($parent['kg_level'] == 3) { //三星找5代
                        if(($dai>= 2) && ($dai <=5)){
                            $res = $this->yjmx($value,$shouyi,$user['kg_level'],2,$parent['userid'],$dai,$user['name']);
                        }
                        //四星找10代
                    } else if ($parent['kg_level'] == 4 ) {
                        if(($dai >=2) && ($dai<= 5)){
                            $this->yjmx($value,$shouyi,$user['kg_level'],2,$parent['userid'],$dai,$user['name']);
                        }else if(($dai >=6) && ($dai<= 10)){
                            $this->yjmx($value,$shouyi,$user['kg_level'],6,$parent['userid'],$dai,$user['name']);

                        }
                        //五星找20代
                    } else if ($parent['kg_level'] == 5) {
                        if(($dai >=2) && ($dai<= 5)){
                            $this->yjmx($value,$shouyi,$user['kg_level'],2,$parent['userid'],$dai,$user['name']);
                        }else if(($dai >=6) && ($dai<= 10)){
                            $this->yjmx($value,$shouyi,$user['kg_level'],6,$parent['userid'],$dai,$user['name']);
                        }else if(($dai >=11) && ($dai<= 20)){
                            $this->yjmx($value,$shouyi,$user['kg_level'],11,$parent['userid'],$dai,$user['name']);
                        }
                    }
                }
            }

        }
    }

    /**
     * 业绩奖收益明细
     * @param $value 会员id
     * @param $shouyi 分红金额
     * @param $level  会员等级
     * @param $dai 会员是第几代
     * @param $userid 会员账号
     *
     */
    public function yjmx($value,$shouyi,$level,$dai,$userid,$pdai,$show){
          $yejimoney = '';
          $nowmoney =  Db::name('user')->where('id', $value)->value('nowmoney');
        $config = $this->config();
        Db::startTrans();
        try{
             $yejimoney = $config['yj_dai' . $level . $dai] * $shouyi * 0.01;
             if(($nowmoney - $yejimoney < 0)){
                  $yejimoney =   $nowmoney;
             }
            //加收益
            Db::name('user')->where('id', $value)->setInc('yeji', $yejimoney);
            //总收益
            Db::name('user')->where('id', $value)->setInc('total', $yejimoney);
            //减结算收益
            Db::name('user')->where('id', $value)->setDec('nowmoney', $yejimoney);
            $endmoney = Db::name('user')->field('id,yeji,nowmoney,liyi')->where('id', $value)->find();
            //如果结算收益为0说明达到了出局条件，添加出局时间
            if($endmoney['nowmoney'] <=0){
                Db::name('user')->where('id',$value)->setField('out_time',date('Y-m-d H:i:s',time()));
            }
            //添加交易记录
//            1.收益明细
            $this->addsymx($userid,$yejimoney,$show.'&M'.$level.'&L'.$pdai,$endmoney['liyi'],2);
//            2.结算收益明细
            $this->addsymx2($userid,'-'.$yejimoney,'M'.$level.'&L'.$pdai.'&'.$show.'业绩奖',$endmoney['nowmoney'],4);
            // 提交事务
            Db::commit();
            return 1;
        } catch (\Exception $e) {
//             回滚事务
            Db::rollback();
            return 2;
        }

    }

    /**
     * 团队奖
     */
    public function team($user,$jifen){
             $chinese =  '';
        $config = $this->config();
        $sanshi = explode("-", trim($user['path'], '-')); //字符变数组
        $dao    = array_reverse($sanshi); //数组倒序
        $arr = [];
        $arr[] = $user['level'];
        $level = '';
        $pjifen = '';
        foreach ($dao as $key=>$value){
            if($value>0){
                //查找上级 上级星级必须5级以上
                $parent = Db::name('user')->field('id,userid,kg_level,path,level,name')
                    ->where('id', $value)->where('kg_level','>=',5)->where('level','>=',1)->where('nowmoney','>',0)->find();
//                dump($parent);
                if($parent){
                    $level = $parent['level'];
                }else{
                    $level = 0;
                }
                $arr[] = $level;
                $a = max($arr);
                $final = $a > $level ? 2 : 1;
                //下级没有本次大的就执行奖励
                if ($final != 2) {

                    $str = implode(",", $arr);
                    if($level >=1){
                        $num = substr_count($str,$level);
                    }else{
                        $num = 0;
                    }
                        //如果是1级就获得初级
                        if ($level == 1) {
                            //初级节点不计算同级
                             $chinese =  '初级';
                            $this->teamadd($value,$config['jiang1'] * 0.01 * $jifen,$chinese,$config['jiang1'],$user['userid']);
                            //中级
                        }else if($level == 2){
                            $chinese =  '中级';
                            if(in_array(1,$arr)){
                                if($num==1){
                                    $this->teamadd($value,($config['jiang2']-$config['jiang1']) * 0.01 * $jifen,$chinese,($config['jiang2']-$config['jiang1']),$user['name']);
                                }else if($num>=2){
                                    //有同级
                                    $pjifen = round(($config['jiang5'] * 0.01) * ($config['jiang2']-$config['jiang1']) * 0.01 * $jifen,2) ;
                                    $this->teamadd($value,$pjifen,$chinese,($config['jiang2']-$config['jiang1']) * ($config['jiang5'] * 0.01),$user['name']);
                                }
                            }else{
                                if($num==1){
                                    $this->teamadd($value,($config['jiang2']) * 0.01 * $jifen,$chinese,$config['jiang2'],$user['name']);
                                }else if($num>=2){
                                    //有同级
                                    $pjifen = round(($config['jiang5'] * 0.01) * ($config['jiang2']) * 0.01 * $jifen,2) ;
                                    $this->teamadd($value,$pjifen,$chinese,$config['jiang2'] *($config['jiang5'] * 0.01) ,$user['name']);
                                }

                            }
                            //高级
                        }else if($level == 3){
                            $chinese =  '高级';
                            if(in_array(2,$arr)){
                                if($num==1){
                                    $this->teamadd($value,($config['jiang3']-$config['jiang2']) * 0.01 * $jifen,$chinese,$config['jiang3']-$config['jiang2'],$user['name']);
                                }else if($num >=2){
                                    //有同级
                                    $pjifen = round(($config['jiang5'] * 0.01) * ($config['jiang3']-$config['jiang2']) * 0.01 * $jifen,2) ;
                                    $this->teamadd($value,$pjifen,$chinese,($config['jiang3']-$config['jiang2']) * ($config['jiang5'] * 0.01),$user['name']);
                                }
                            }else if(!in_array(2,$arr) && in_array(1,$arr)){
                                if($num==1){
                                    $this->teamadd($value,($config['jiang3']-$config['jiang1']) * 0.01 * $jifen,$chinese,$config['jiang3']-$config['jiang1'],$user['name']);
                                }else if($num >=2){
                                    //有同级
                                    $pjifen = round(($config['jiang5'] * 0.01) * ($config['jiang3']-$config['jiang1']) * 0.01 * $jifen,2) ;
                                    $this->teamadd($value,$pjifen,$chinese,($config['jiang3']-$config['jiang1']) * ($config['jiang5'] * 0.01),$user['name']);
                                }
                            }else if(!in_array(2,$arr) && !in_array(1,$arr)){
                                if($num==1) {
                                    $this->teamadd($value, ($config['jiang3']) * 0.01 * $jifen, $chinese, $config['jiang3'], $user['name']);
                                }else if($num >=2){
                                    $pjifen = round(($config['jiang5'] * 0.01) * ($config['jiang3']) * 0.01 * $jifen,2) ;
                                    $this->teamadd($value, $pjifen, $chinese, $config['jiang3'] * ($config['jiang5'] * 0.01), $user['name']);
                                }
                            }
                            //超级
                        }else if($level == 4){
                            $chinese =  '超级';
                            if(in_array(3,$arr)){
                                if($num==1){
                                    $this->teamadd($value,($config['jiang4']-$config['jiang3']) * 0.01 * $jifen,$chinese,$config['jiang4']-$config['jiang3'],$user['name']);
                                }else if($num >=2){
                                    $pjifen = round(($config['jiang5'] * 0.01) * ($config['jiang4']-$config['jiang3']) * 0.01 * $jifen,2) ;
                                    $this->teamadd($value,$pjifen,$chinese,($config['jiang4']-$config['jiang3']) * ($config['jiang5'] * 0.01),$user['name']);
                                }
                            }else if(!in_array(3,$arr) && in_array(2,$arr)){
                                if($num==1){
                                    $this->teamadd($value,($config['jiang4']-$config['jiang2']) * 0.01 * $jifen,$chinese,$config['jiang4']-$config['jiang2'],$user['name']);

                                }else if($num >=2){
                                    $pjifen = round(($config['jiang5'] * 0.01) * ($config['jiang4']-$config['jiang2']) * 0.01 * $jifen,2) ;
                                    $this->teamadd($value,$pjifen,$chinese,($config['jiang4']-$config['jiang2']) * ($config['jiang5'] * 0.01),$user['name']);
                                }
                            }else if(!in_array(3,$arr) && !in_array(2,$arr) && in_array(1,$arr)){
                                if($num==1){
                                    $this->teamadd($value,($config['jiang4']-$config['jiang1']) * 0.01 * $jifen,$chinese,$config['jiang4']-$config['jiang1'],$user['name']);
                                }else if($num >=2){
                                    $pjifen = round(($config['jiang5'] * 0.01) * ($config['jiang4']-$config['jiang1']) * 0.01 * $jifen,2) ;
                                    $this->teamadd($value,$pjifen,$chinese,($config['jiang4']-$config['jiang1']) * ($config['jiang5'] * 0.01),$user['name']);
                                }
                            }else if(!in_array(3,$arr) && !in_array(2,$arr) && !in_array(1,$arr)){
                                if($num==1){
                                    $this->teamadd($value,($config['jiang4']) * 0.01 * $jifen,$chinese,$config['jiang4'],$user['name']);
                                }else if($num >=2){
                                    $pjifen = round(($config['jiang5'] * 0.01) * $config['jiang4'] * 0.01 * $jifen,2) ;
                                    $this->teamadd($value,$pjifen,$chinese,$config['jiang4'] * ($config['jiang5'] * 0.01),$user['name']);
                                }
                            }
                        }
                }
            }
        }
    }



    /**
     * 团队收益明细
     */
    public function teamadd($k,$num,$level,$bili,$show){
        $teammoney = '';
        $nowmoney =Db::name('user')->where('id', $k)->value('nowmoney');
        if($bili>0){
            Db::startTrans();
            try{
                if(($nowmoney - $num) < 0){
                    $num =  $nowmoney;
                }
                Db::name('user')->where('id', $k)->setInc('jie', $num);
                Db::name('user')->where('id', $k)->setInc('total', $num);
                Db::name('user')->where('id', $k)->setDec('nowmoney', $num);
                $moneys = Db::name('user')->field('id,userid,jie,total,nowmoney')->where('id', $k)->find();
                //如果结算收益为0说明达到了出局条件，添加出局时间
                if($moneys['nowmoney'] <=0){
                    Db::name('user')->where('id',$k)->setField('out_time',date('Y-m-d H:i:s',time()));
                }
                $this->addsymx($moneys['userid'], $num, $show .'&'.$level.'节点'.'('.$bili.'%)', $moneys['total'], 3);
                $this->addsymx2($moneys['userid'], '-'.$num, $level.'节点'.$show.'团队奖('.$bili.'%)', $moneys['nowmoney'], 5);
                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
//                // 回滚事务
                Db::rollback();
            }

        }
    }


    /**
     * 出局48小时后不复投，冻结账号
     */
    public function dong(){
        $dealModel = new DealModel();
        $config = $dealModel->config();
        //找已经出局的账号，没有冻结，符合出局条件
        $users = Db::name('user')->field('id,userid,out_time,tz_time')->where('out_time','neq',0)
            ->where(['status'=>1])->where('nowmoney','<=',0)->select();
        dump($users);
        foreach ($users as $key=>$value){
            $cha = (time() - strtotime($value['tz_time'])) / 60 / 60;
            //超48小时冻结账号
            if($cha > $config['dong']){
                Db::name('user')->where('userid',$value['userid'])->setField('status',0);
            }
        }
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
     * 持续分红
     */
    public function fhxu(){
        $shi = date('H');
        if($shi <1 && $shi >=0){
            $this->jing();
        }
    }
}