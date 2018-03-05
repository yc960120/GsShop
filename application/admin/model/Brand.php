<?php

namespace app\admin\model;

use think\Model;
use traits\model\SoftDelete;

class Brand extends Model
{
    //设置软删除
    use SoftDelete;
    protected $deleteTime = 'delete_time';
    
}
