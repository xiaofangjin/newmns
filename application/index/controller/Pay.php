<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;
use think\Config;
use app\index\model\PayModel;
class Pay extends Controller
{
	
	
	
	//获取收货地址
    public function get_api(){
        $api='https://api.ztpay.org/api/v1';
        $appid='zttj70qal7lk83pyjs';
        $appsecret='MSdiwcQupSSZ9TfSWxWCmkmjWgOAZhDJ';
        $name='USDT';
        $data=array(
            'appid'=>$appid,
            'method'=>"get_address",
            'name'=>$name
        );
        $sign=$this->getSign($data,$appsecret);
        $data['sign']=$sign;
        $res=$this->request_post($api,$data);
        //$data_arr = json_decode($res,true);
        //var_dump($data_arr);die;
        echo $res;
    }
    //通知
    public function success1(){
    	$data = input('param.');	
        $this->setlog($data,date("Y-m-d H:i:s").'.php');
        $re = Db::name('user')->where('dizhi',$data['data']['referenceaddress'])->field('userid,id,usdt')->find();
        if(empty($re)){
            return false;
        }
        //判断是否存在
        $res2 = Db::name('money5_log')->where(['code2'=>$data['sign']])->value('id');
        if($res2){
        	return false;
        }
        $a = Db::name('user')->where('dizhi',$data['data']['referenceaddress'])->setInc('usdt',$data['data']['amount']);
        $b = Db::name('money5_log')->insert([
            'userid'=>$re['userid'],
            'form'=>'充值到账',
            'get_money'=>$data['data']['amount'],
            'money'=>$re['usdt']+$data['data']['amount'],
            'create_time'=>date('Y-m-d H:i:s'),
            'code1'=>$data['time'],
            'code2'=>$data['sign'],
            'status'=>1,
        ]);
        //转账
        $api='https://api.ztpay.org/api/v1';
        $appid='zttj70qal7lk83pyjs';
        $appsecret='MSdiwcQupSSZ9TfSWxWCmkmjWgOAZhDJ';
        $name='USDT';//转出币种
        $from=$data['data']['referenceaddress'];//转出方地址
        $to='1KAm2FquffFrDXAE7jStQ4PXoeqjgrVHXb';//接收方地址
        $feeaddress='1Mu3Xu2GyLkTuuXsAHAKeWg1NK4sbGrkGs';//手续费地址
        $value=$data['data']['amount'];//转出USDT数量
        $datas=array(
            'appid'=>$appid,
            'method'=>"transfer",
            'name'=>$name,
            'from'=>$from,
            'to'=>$to,
            'feeaddress'=>$feeaddress,
            'value'=>$value
        );
        $sign=$this->getSign($datas,$appsecret);
        $datas['sign']=$sign;
        $res=$this->request_post($api,$datas);
        $res_json = urldecode(json_encode($res));
        Db::name('aaaaa')->insert([
        	'memo'=>$res_json,	
        ]);
        //dump($res);exit;
        echo 'success';exit;
    }
	
	// 应用公共文件
            //生成签名
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
