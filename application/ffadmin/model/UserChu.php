<?php

namespace app\ffadmin\model;

use think\Model;

/**
 *
 */

class UserChu extends Model
{
    /**
     * 卖家家手机号
     * @return [type] [description]
     */
    public function phone()
    {
        return $this->belongsTo('User', 'sell_userid');
    }


}