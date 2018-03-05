<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\index\model\Cart as CartModel;


class Cart extends Base
{
    public function shopcart()
    {
    	if(session('user_id'))
    	{
    		$user_id = session('user_id');
		    $cartInfo = Db::name('cart')->where('user_id',$user_id)->select();
		    $this->assign([
		       		'cartInfo'=>$cartInfo
		       	]);
		     return $this->fetch();
		  }else{
		 	//读取cookie里面的数组
		 	    $cookieValue = cookie('cookieValue');
       		$goodsInfo = unserialize($cookieValue);
       		$goods_id = $goodsInfo['goods_id'];
       		$ip = $goodsInfo['ip'];
       		$sql = "select * from gs_cart where goods_id='$goods_id' and ip='$ip' and user_id=-1";
       		$cartInfo = CartModel::query($sql);
       		$this->assign('cartInfo',$cartInfo);
       		return $this->fetch();
 		  }
    }
      

      //单个删除
     public function delete($cart_id)
     {
       $result = Db::name('cart')->where('cart_id',$cart_id)->delete();   
       return $this->redirect('shopcart');
     }

     //单个移入收藏夹
     public function rmgoods($cart_id)
     {
         if(session('user_id')){
          $user_id = session('user_id');
          $goods = Db::name('cart')->where('cart_id',$cart_id)->select();
          $goods_name = $goods[0]['goods_name'];
          $goods_id = $goods[0]['goods_id'];
          $goods_num = Db::name('goods')->where("goods_id=$goods_id")->value('goods_num');
          //判断是否移入收藏夹
          $isCollect = Db::name('collect')->where("goods_num='$goods_num'")->find();
              if($isCollect){
                  $this->error('该商品已加入过收藏，不可重复添加','/index/cart/shopcart','','1');
              }else{
                  $col_time = time();
                  $data = [
                      'user_id'=>$user_id,
                      'goods_id'=>$goods_id,
                      'goods_num'=>$goods_num,
                      'col_time'=>$col_time,
                      'goods_name'=>$goods_name
                    ];
                   $rm = Db::name('collect')->insert($data);
                   $this->success('收藏成功','/index/cart/shopcart','','1');
              }  
          }else{
            $this->error('请先登录,再移入收藏夹','/index/index/login','','2');
          }
      }
    

     public function All()
    {
      //批量删除
          if(input('post.delete'))
          {
              $result = input('post.');
              foreach ($result as $key => $value)
              {
                  $cart_id = $key;
                  if($cart_id != 'delete'){
                      $del = Db::name('cart')->where('cart_id',$cart_id)->delete();
                  }    
              } 
              $this->redirect('/index/cart/shopcart');
          }

        //批量收藏  
          if(input('post.collect'))
          {
            if(session('user_id')){
                $result = input('post.');
                foreach ($result as $key => $value)
                {
                    $cart_id = $key;
                    if($cart_id != 'collect'){
                        $user_id = session('user_id');
                        $goods = Db::name('cart')->where('cart_id',$cart_id)->select();
                        $goods_name = $goods[0]['goods_name'];
                        $goods_id = $goods[0]['goods_id'];
                        $col_time = time();
                        $data = [
                            'user_id'=>$user_id,
                            'goods_id'=>$goods_id,
                            'col_time'=>$col_time,
                            'goods_name'=>$goods_name
                          ];
                          $rm = Db::name('collect')->insert($data);
                          $this->success('收藏成功','/index/cart/shopcart','','1'); 
                    }    
                }
            }else{
                  $this->error('请先登录,再移入收藏夹','/index/index/login','','2');
            }
        }

        if(input('post.calculate'))
        {
          var_dump(input('post.'));
          $result = input('post.');
          foreach($result as $key => $value)
          {
              if($key != 'calculate')
              {
                  $data[] = Db::name('cart')->where("cart_id",$key)->find();
              }
          }
            session('data',$data);
            $this->redirect('/index/order/pay');
        }
    }


   







    
}
