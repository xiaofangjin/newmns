<?php


namespace app\common\controller;
use think\Controller;
use think\Db;
class IndexBase extends Controller
{
    public function _initialize()
    {

        if (!session('?user')) {
            //校验登录状态
            $this->redirect('index/user/login');
        }
        $userid = session('user.userid');
        $status = Db::name('user')->where('userid',$userid)->value('status');
        if ($status===0 || $status==2) {
            //校验用户是否冻结
            $this->redirect('index/user/login');
        }
        $config = Db::name('config')->where('id',1)->value('ob_home');
        if($config != 1){
            $this->redirect('index/user/login');
        }
    }
}