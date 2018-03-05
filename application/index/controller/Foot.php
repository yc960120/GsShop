<?php

namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;
use app\index\model\Foot as FootModel;

class Foot extends Base
{
    //用户的足迹
    public function myfoot()
    {
    	$user_id = session('user_id');
    	$foot = Db::table('gs_foot')
    			->alias('f')
                ->join('gs_images i','f.goods_id = i.goods_id')
    			->join('gs_goods g','f.goods_id = g.goods_id')
    			->where("f.user_id=$user_id")
    			->select();
       /* var_dump(count($foot));
        $sql = "select * from gs_foot f left join gs_images i on f.goods_id=i.goods_id left join gs_goods g on f.goods_id=g.goods_id where f.user_id=$user_id";
        $f = new FootModel;
        $foot1 = $f->query($sql); 
        var_dump(count($foot1));
        */
    	$this->assign('foot',$foot);
        return $this->fetch();
    }

    public function delfoot($foot_id)
    {
    	$del = Db::name('foot')->where("foot_id=$foot_id")->delete();
    	$this->redirect('/index/foot/myfoot');
    }

    
   
}
