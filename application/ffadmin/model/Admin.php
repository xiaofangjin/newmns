<?php

namespace app\ffadmin\model;

use think\Model;

/**
 *新闻模型
 */
class Admin extends Model
{
    public function getAuthAttr($value)
    {
        $status = [
            1 => '超级管理员',
            2 => '普通管理员',
        ];
        return $status[$value];
    }
}