<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
class Comment extends Base
{
     //用户的评论
    public function comment()
    {
        return $this->fetch();
    }
    //用户评论列表
    public function commentlist()
    {
        $user_id = session('user_id');
        //所有待评价的订单
        $unComment = Db::table('gs_order')
                    ->alias('o')
                    ->join('gs_images i','o.goods_id=i.goods_id')
                    ->where("o.user_id=$user_id&&o.ord_status=3")
                    ->select();
        $this->assign('unComment',$unComment);
        return $this->fetch();
    }

    //写评论
    public function read($id)
    {
       
    }

     public function write()
    {
        $b = input('post.b');
        $list = explode('-',$b);
        $content = $list[0]; $ord_id = $list[1];$grade = $list[2];
        $ord_num = Db::name('order')->where("ord_id=$ord_id")->value('ord_num');
        $result = Db::name('order')->where("ord_num='$ord_num'")->find();
        $user_id = $result['user_id'];
        $goods_id = $result['goods_id'];
        $time = time();
        $data = ['user_id'=>$user_id,'goods_id'=>$goods_id,'content'=>$content,'com_time'=>$time];
        //插入评论，更改订单状态
        $ins = Db::name('comment')->insert($data);
        $upd = Db::name('order')->where("ord_id=$ord_id")->update(['ord_status'=>5]);
        if($upd){
                echo json_encode(["state"=>1]);
        }else{
            echo json_encode(["state"=>0]);
        }

    }
       
  
}
