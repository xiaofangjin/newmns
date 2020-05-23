<?php
namespace app\common\controller;

use think\Controller;
use think\Db;
use think\Request;
use think\Session;
use think\Loader;
use LBMenu\BuildMenuHtml;
/**
 *
 */
class AdminBase extends Controller
{

    public function _initialize()
    {
        if (!session('?admin')) {
            //校验登录状态
            $this->redirect('ffadmin/login/login');
        }
        $role = Session::get('role');
        $roleArr = $this->roleArrModel($role,3);
        $back_data_array='';
        foreach ($roleArr as $v){
            $arr = array(
                $v['id'],
                $v['pid'],
                "url"=>url($v["name"],$v['param']),
                "title"=>$v['title'],
                'css'=>$v['css'],
                'condition'=>$v['condition'],
                'img'=>$v['img'],
                'nype'=>$v['nype'],
                'ac'=>$v['ac']

            );
            $back_data_array[''.$v['pid']][] = $arr;
        }

        $htmlMenu = new BuildMenuHtml($back_data_array);
        $html = $htmlMenu->buildMenu();

        $this->assign("menuhtml",$html);
    }

    /**
     * 添加日志记录
     * @param $t_name
     * @param $type
     * @param $data
     * @param $userid
     */
    public function addLog($t_name, $type, $data, $userid)
    {
        $ip  = request()->ip();
        $arr = [
            't_name'      => $t_name,
            'type'        => $type,
            'userid'      => $userid,
            'admin'       => session('admin.adminid'),
            'data'        => $data,
            'ip'          => $ip,
            'create_time' => time(),
        ];
        Db::name('log')->insert($arr);
    }
    /**
     * 查询  返回数组
     */
    public function roleArrModel($id,$num){
        if($num==1){
            return db('role')->where('id','in',$id)->where('name','#')->select();
        }
        if($num==2){
            return db('role')->where('id','in',$id)->where('name','not in','#')->select();
        }
        if($num==3){
            return db('role')->where('id','in',$id)->where('status',1)->order('sort asc')->select();
        }
    }
}