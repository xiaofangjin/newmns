<?php


namespace app\ffadmin\controller;

use app\common\controller\AdminBase;
use think\Controller;
use think\Db;

class Money1 extends AdminBase
{
    /**
     * 矿池资产财务明细
     * @param string $value [description]
     */
    public function banklistkczc($userid = '',$date = '')
    {
        $where = [];
        //用戶名
        if (isset($userid) && !empty($userid)) {
            $where['userid'] = ['like', '%' . $userid . '%'];
        }
        $money4 = Db::name('shouyi_log')->where($where)->order('create_time desc')->paginate(15, false, ['query' => request()->param()]);
        $this->assign([
            'money4' => $money4,
            'userid' => $userid,
            'date'   => $date,
        ]);
        return $this->fetch();
    }

    /**
     * 矿池钱包财务明细
     * @param string $value [description]
     */
    public function banklistkcqb($userid = '', $date = '')
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
        $money3 = Db::name('money_log')->where($where)->order('create_time desc')->paginate(15, false, ['query' => request()->param()]);
        $this->assign([
            'money3' => $money3,
            'userid' => $userid,
            'date'   => $date,
        ]);
        return $this->fetch();
    }

    /**
     * 可售余额财务明细
     * @param string $value [description]
     */
    public function banklistksye($userid = '', $date = '')
    {
        $where = [];
        //時間
        if (isset($date) && !empty($date)) {
            $time                 = $date;
            $timeArr              = explode(' - ', $time);
            $min                  = $timeArr[0] .' 00:00:00';
            $max                  = $timeArr[1] .' 23:59:59';
            $where['create_time'] = ['between time', [$min, $max]];
        }
        //用戶名
        if (isset($userid) && !empty($userid)) {
            $where['userid'] = ['like', '%' . $userid . '%'];
        }
        $money1 = Db::name('keshouyu_money_log')->where($where)->order('create_time desc')->paginate(15, false, ['query' => request()->param()]);
        $this->assign([
            'money1' => $money1,
            'userid' => $userid,
            'date'   => $date,
        ]);
        return $this->fetch();
    }

    /**
     * 可售额度明细
     * @param string $value [description]
     */
    public function banklistksed($userid = '', $date = '')
    {
        $where = [];
        // //時間
        if (isset($date) && !empty($date)) {
            $time                 = $date;
            $timeArr              = explode(' - ', $time);
            $min                  = $timeArr[0] .' 00:00:00';
            $max                  = $timeArr[1] .' 23:59:59';
            $where['create_time'] = ['between time', [$min, $max]];
        }
        //用戶名
        if (isset($userid) && !empty($userid)) {
            $where['userid'] = ['like', '%' . $userid . '%'];
        }
        $money4 = Db::name('keshou_money_log')->where($where)->order('create_time desc')->paginate(15, false, ['query' => request()->param()]);
        $this->assign([
            'money4' => $money4,
            'userid' => $userid,
            'date'   => $date,
        ]);
        return $this->fetch();
    }
}