<?php

namespace app\repair\model;

use think\Model;

class RepairModel extends Model
{
    public function status()
    {
        return [
            10 => '待处理',
            20 => '处理中',
            30 => '待确认',
            40 => '已完结',
        ];
    }
}
