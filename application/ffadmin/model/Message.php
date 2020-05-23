<?php

namespace app\ffadmin\model;

use think\Model;

/**
 *新闻模型
 */
class Message extends Model
{

    public function getStatusAttr($value)
    {
        $status = [
            1 => '已回复',
            2 => '待回复',
        ];
        return $status[$value];
    }

}
