<?php


namespace app\index\model;


use think\Db;
use think\Model;
use app\index\model\Deal as DealModel;
class User extends Model
{
    /**
     * 验证码
     * */
    public function codeModel($data,$code){
        $dataArr = array(
            'code'=>$code,
            'status'=>$data['status'],
            'create_time'=>date('Y-m-d H:i:s',time()),
            'create_time_str'=>date('YmdHis',time()),
            'phone'=>$data['phone'],
        );
        return Db::name('code')->insert($dataArr);
    }

    public function getLevelAttr($value){
        $data = [
            1=>'初级',
            2=>'中级',
            3=>'高级',
            4=>'超级',
            0=>'',
        ];
        return $data[$value];
    }

    public function getKgLevelAttr($value){
        $data = [
            1=>'M1',
            2=>'M2',
            3=>'M3',
            4=>'M4',
            5=>'M5',
            0=>'M0',
        ];
        return $data[$value];
    }


    /**
     * 节点升级
     */
    public function jiedian($userid){
        $result = '';
        $dealModel = new DealModel;
        $config = $dealModel->config();
        $parent = Db::name('user')->field('id,pid,userid,path,kg_level,level,yeji,money')->where('userid',$userid)->find();
        if($parent['kg_level'] == 5){
            $team = Db::name('user')->where('path','like','%'.$parent['path'] .'-'.$parent['id'].'%')->column('level');
            $str = implode(",", $team);
            //升初级
            if($parent['level'] == 0){
                //找直推
                $count = Db::name('user')->where('pid',$parent['id'])->where('kg_level',5)->count('id');
                //团队业绩
                $total1 = Db::name('user')->where('path','like','%'.$parent['path'].'-'.$parent['id'].'%')->sum('money');
                if(($count>=$config['team1']) && ($total1 >= $config['tmoney'])){
                    //升级为初级节点
                    $result = Db::name('user')->where('userid',$userid)->setField('level',1);
                }
                //其他级别
            }else if($parent['level'] >= 1){
                for ($i=2;$i<=4;$i++){
                    if($parent['level'] < $i){
                        $count = substr_count($str, $i-1);
                        if($count >= $config['team'.$i]){
                            $result = Db::name('user')->where('userid',$userid)->setField('level',$i);
                        }
                    }
                }
            }
        }
        return $result;
    }

    function getSign($data,$appsecret) {
        $signPars = "";
        ksort($data);
        foreach($data as $k => $v) {
            if("sign" != $k && $v != ''  && $v!="0") {
                $signPars .= $k . "=" . $v . "&";
            }
        }
        $signPars .= "key=" . $appsecret;
        return strtoupper(md5($signPars));
    }

    function dump($data){
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }

    /**
     * 使用curl发送
     * @param string $url
     * @param string $param
     * @return bool|mixed
     */
    function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);

        return $data;
    }

    /**
     * 写日志
     * @param $data
     * @param $file
     */
    function setlog($data='',$file='')
    {
        $time=time();
        $info="===ZT区块链支付日志记录 ".date('Y-m-d H:i:s',$time)."=== \n ".var_export($data,true)." \n";
        if(!is_dir("log")){
            mkdir("log",0755);
        }
        $file=$file?:date('YmdHis',$time).".txt";
        file_put_contents("log/$file",$info,FILE_APPEND);
    }
}