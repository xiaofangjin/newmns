<?php

namespace app\ffadmin\controller;

use app\common\controller\AdminBase;
use think\Db;
class Config extends AdminBase
{
    /**
     * @return 清空数据
     */
    public function clearlist(){
        return $this->fetch();
    }

    /**
     * 清空会员
     * @return [type] [description]
     */
    public function clearuser()
    {
        $res = Db::name('user')->where('id','neq',1)->delete();
        if ($res) {
            $this->addLog('会员表','清空','','');
            return json(['con'=>1]);
        }else{
            return json(['con'=>0]);

        }
    }

    /**
     * 清除所有业务
     * @return [type] [description]
     */
    public function clearyewu()
    {
        $res = Db::execute("TRUNCATE TABLE dyl_money1_log;");
        $res3 = Db::name('money3_log')->query("TRUNCATE TABLE dyl_money3_log;");
        $res4 = Db::name('money4_log')->query("TRUNCATE TABLE dyl_money4_log;");
        $res5 = Db::name('admin_money')->query("TRUNCATE TABLE dyl_admin_money;");
        //添加日志
        $this->addLog('矿池资产表','清空','','');
        $this->addLog('可售余额表','清空','','');
        $this->addLog('矿池钱包表','清空','','');
        $this->addLog('后台充值记录表','清空','','');
        return json(['con'=>1]);
    }
    /**
     * @return 配置列表
     */
    public function configlist()
    {
        $arr = Db::name('config')->where('id',1)->find();
        $this->assign('arr',$arr);
        return $this->fetch();
    }

    /**
     * @throws 修改配置
     * @throws
     */
    public function addConfig()
    {

        $data = input('param.');
        $arr = [
            'money1'=>$data['money1'],
            'shouyi1'=>$data['shouyi1'],
            'money2'=>$data['money2'],
            'shouyi2'=>$data['shouyi2'],
            'money3'=>$data['money3'],
            'shouyi3'=>$data['shouyi3'],
            'money4'=>$data['money4'],
            'shouyi4'=>$data['shouyi4'],
            'money5'=>$data['money5'],
            'shouyi5'=>$data['shouyi5'],
            'dai21'=>$data['dai21'],
            'dai41'=>$data['dai41'],
            'dai42'=>$data['dai42'],
            'dai51'=>$data['dai51'],
            'dai52'=>$data['dai52'],
            'dai53'=>$data['dai53'],
            'yj_dai11'=>$data['yj_dai11'],
            'yj_dai21'=>$data['yj_dai21'],
            'yj_dai22'=>$data['yj_dai22'],
            'yj_dai31'=>$data['yj_dai31'],
            'yj_dai32'=>$data['yj_dai32'],
            'yj_dai41'=>$data['yj_dai41'],
            'yj_dai42'=>$data['yj_dai42'],
            'yj_dai46'=>$data['yj_dai46'],
            'yj_dai51'=>$data['yj_dai51'],
            'yj_dai52'=>$data['yj_dai52'],
            'yj_dai56'=>$data['yj_dai56'],
            'yj_dai511'=>$data['yj_dai511'],
            'team1'=>$data['team1'],
            'tmoney'=>$data['tmoney'],
            'jiang1'=>$data['jiang1'],
            'team2'=>$data['team2'],
            'jiang2'=>$data['jiang2'],
            'team3'=>$data['team3'],
            'jiang3'=>$data['jiang3'],
            'jiang3'=>$data['jiang3'],
            'team4'=>$data['team4'],
            'jiang4'=>$data['jiang4'],
            'jiang5'=>$data['jiang5'],
            'bei'=>$data['bei'],
            'shouxu'=>$data['shouxu'],
            'full'=>$data['full'],
            'mns'=>$data['mns'],
            'mnsbei'=>$data['mnsbei'],
            'dong'=>$data['dong'],
            'ftbei1'=>$data['ftbei1'],
            'ftbei2'=>$data['ftbei2'],
            'tixian'=>$data['tixian'],
            'maxmoney'=>$data['maxmoney'],

        ];
        if (isset($data['ob_home'])) {
            $arr['ob_home'] = 1;
        }else{
            $arr['ob_home'] = 0;
        }
        $res = DB::name('config')->where('id',1)->update($arr);
        if ($res) {
            //添加日志记录
           $this->addLog('配置表','更新','','');
            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }
    }

    /**
     * 日志列表
     */
    public function loglist($tableName='',$ip=''){
        $where = [];
        if (isset($tableName) && !empty($tableName)) {
            $where['t_name'] = ['like', '%' . $tableName . '%'];
        }
        if (isset($ip) && !empty($ip)) {
            $where['ip'] = ['like', '%' . $ip . '%'];
        }
        $result = Db::name('log')->where($where)->order('create_time desc')->paginate(10, false, ['query' => request()->param()]);
//        dump($result);
//        die;
        $this->assign([
            'result'    => $result,
            'tableName'     => $tableName,
            'ip'   => $ip
        ]);

        return $this->fetch();
    }

    /**
     * 上传系统图片
     */
    public function imagelist(){
        $image = Db::name('image')->select();
        $this->assign('image',$image);
        return $this->fetch();
    }

    /**
     * @return 上传图片
     */
    public function getImg(){
        $adminid = session('admin.adminid');
        $id = input('param.id/d');
        $file   = request()->file('image');
        if ($file) {
            $target = ROOT_PATH . 'public/static/upload';
            $info   = $file
                ->validate([
                    'size' => 2048000,
                    'ext'  => 'jpg,png,gif,jpeg',
                ])
                ->move($target);
            if ($info) {
                $path = $info->getSaveName();
            } else {
                $this->error($file->getError());
            }
        }
        $res = Db::name('image')->where('id',$id)->update(['image'=>$path,'adminid'=>$adminid,'create_time'=>date('Y-m-d H:i:s',time()),'type'=>$id]);
        if ($res) {
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }


}
