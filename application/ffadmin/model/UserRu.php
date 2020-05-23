<?php

namespace app\ffadmin\model;

use think\Model;

/**
 *
 */

class UserRu extends Model
{
    /**
     * 买家手机号
     * @return [type] [description]
     */
    public function phone()
    {
        return $this->belongsTo('User', 'userid');
    }

    /**
     * 卖家手机号
     * @return [type] [description]
     */
    public function sellphone()
    {
        return $this->belongsTo('User', 'sell_userid');
    }


    /**
     * 订单状态自动获取
     * @param  [type] $value [description]
     * @return [type]        [description]
     */
    public function getisDakuanAttr($value)
    {
//        1订单已完成 0未匹配， 2已打款，等待卖家审核 3已匹配，等待买家打款
        $status = [
            3 => '已匹配，等待买家打款',
            2 => '已打款，等待卖家审核',
            1 => '已完成',
            0 => '未匹配',

        ];
        return $status[$value];
    }


}