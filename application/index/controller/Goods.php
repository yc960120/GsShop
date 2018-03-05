<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
use think\Session;
use app\index\model\Goods as GoodsModel;
use think\Model;


class Goods extends Base
{
    //商品的详情页
    public function introduction($goods_id)
    {
        //查看评价
        $selcomment = Db::table('gs_comment')
                    ->alias('c')
                    ->join('gs_user u','c.user_id=u.user_id')
                    ->where("goods_id=$goods_id")
                    ->order('c.com_time','desc')
                    ->select();
        $countComment = count($selcomment);

        //商品详情
        $select = Db::name('goods')->where("goods_id=$goods_id")->select();
        //口味
        $selTasty = Db::name('tasty')->where("goods_id=$goods_id")->select();
        //包装
        $selPac = Db::name('package')->where("goods_id=$goods_id")->select();
        //图片
        $selImg = Db::name('images')->where("goods_id=$goods_id")->select();
        //扩围
        $bigselImg[] = $selImg;
        $this->assign([
                'goods_id'=>$goods_id,
                'select'=>$select,
                'selcomment'=>$selcomment,
                'countComment'=>$countComment,
                'selTasty'=>$selTasty,
                'selPac'=>$selPac,
                'selImg'=>$selImg,
                'bigselImg'=>$bigselImg
            ]
        );
        //足迹
        addFoot($goods_id);
        return $this->fetch();
    }
   
    //商品详情页数据处理
    public function handle()
    {
        $goods_id = input('post.goods_id');
        $goodsInfo = Db::name('goods')->where('goods_id',$goods_id)->select();
        $goods_name = $goodsInfo[0]['goods_name'];
        $num = input('post.number');
        $price = $goodsInfo[0]['price'];
        $total_price = $num * $price;
        $img_path = Db::name('images')->where('goods_id',$goods_id)->value('img_path');
        $tasty = input('post.tasty');
        $package = input('post.package');
        $time = time();

        //添加购物车
        if(input('post.addcart'))
        {
            if(!empty(session('user_id'))){
                 $data = [
                        'user_id'=>session('user_id'),
                        'num'=>$num,
                        'goods_name'=>$goods_name,
                        'goods_id'=>$goods_id,
                        'tasty'=>$tasty,
                        'package'=>$package,
                        'img_path'=>$img_path,
                        'price'=>$price,
                        'cart_time'=>$time,
                        'total_price'=>$total_price
                     ];
                //查询该商品是否已加入购物车，是则更新数量
                $selChongfu  = Db::name('cart')->where("goods_name='$goods_name'&&tasty='$tasty'&&package='$package'")->find();
                if($selChongfu){
                    $num1 = $selChongfu['num'];
                    $num2 = $num1 + $num;
                    $cart_id = $selChongfu['cart_id'];
                    $updateNum = Db::name('cart')->where("cart_id=$cart_id")->update(['num'=>$num2]);
                    if($updateNum){
                        $this->success('添加成功',"/index/goods/introduction/goods_id/$goods_id",'','2');
                    }else{
                        $this->error('失败',"/index/goods/introduction/goods_id/$goods_id",'','2');
                    }
                //没添加则插入
                }else{
                        $result = Db::name('cart')->insert($data);
                        if($result){
                            $this->success('添加成功',"/index/goods/introduction/goods_id/$goods_id",'','2');
                        }else{
                            $this->error('失败',"/index/goods/introduction/goods_id/$goods_id",'','2');
                        }
                }
            }else{
                $request = Request::instance();
                //$ip = ip2long($request->ip());
                $ip = $request->ip();
                if($ip = '0.0.0.0'){
                     $ip = '127.0.0.1';
                }
                $ip = ip2long($ip);
                $data = [
                    'num'=>$num,
                    'goods_name'=>$goods_name,
                    'goods_id'=>$goods_id,
                    'tasty'=>$tasty,
                    'package'=>$package,
                    'img_path'=>$img_path,
                    'price'=>$price,
                    'cart_time'=>$time,
                    'ip'=>$ip
                ];

                $result = Db::name('cart')->insert($data);
                $goodsInfo = ['goods_id'=>$goods_id,'ip'=>$ip];
                //序列化
                $cookieValue = serialize($goodsInfo);           
                cookie('cookieValue',$cookieValue,3600);
                $this->success('登录后可添加更多商品到购物车',"/index/goods/introduction/goods_id/$goods_id",'','2');
            }
        }

        //立即购买
       if(input('post.buy'))
       {
            if(session('user_id')){
                //遍历地址
                $user_id = session('user_id');
                $allAdd = Db::name('address')->where("user_id=$user_id")->select();
                $defaultAddr = Db::name('address')->where("user_id=$user_id&is_default=1")->select();
                $data = [[
                'user_id'=>$user_id,
                'num'=>$num,
                'goods_name'=>$goods_name,
                'goods_id'=>$goods_id,
                'tasty'=>$tasty,
                'package'=>$package,
                'img_path'=>$img_path,
                'price'=>$price,
                'total_price'=>$total_price
                ],
             ];
                session('data',$data);
                $this->redirect('/index/order/pay');
            }else{
                $this->error('请先登录','/index/index/login');
            }
      }
  }

    //搜索商品页面,模糊查询
    public function search()
    {
        //得到首页传过来的分类cate_num
        if(input('get.cate_num')){
            $cate_num = input('get.cate_num');
            $id = input('get.id');
            $keyword = $cate_num;
            //$sql = "select * from gs_goods as g left join gs_images as i on g.goods_id=i.goods_id where g.cate_num='$cate_num'and issale = 1";
            $data = Db::name('goods')->where(['issale'=>1,'cate_num'=>$cate_num])->select();
            foreach ($data as $key=>$val) {
                $res = Db::name('images')->where(array('goods_id'=>$val['goods_id']))->find();
                $data[$key]['img_path'] = $res['img_path'];
            }
            //$m = new GoodsModel;
            //$data = $m->query($sql);

            //获取得到的分类id---》查品牌
            $brand_num = Db::name('goods')->where("cate_num='$cate_num'")->distinct(true)->field('brand_num')->select();
            foreach ($brand_num as $num)
            { 
                foreach ($num as $n){
                    $brandinfo[] = Db::name('brand')->where("bra_num='$n'")->find(); 
                } 
            }
            if(!empty($brandinfo)){
                session('brandinfo',$brandinfo);
            }
            

            //获取得到的分类id---》查分类
            $catename = Db::name('category')->where("cate_num='$cate_num'")->select();
            session('catename',$catename);
            $this->assign(['data'=>$data,'keyword'=>$keyword,'id'=>$id]);
      }


        //得到首页搜索框中的内容
        if(input('post.keyword'))
        {
            $keyword = input('post.keyword');
            $id = input('post.id');
            $sql = "select * from gs_goods as g left join gs_images as i on g.goods_id=i.goods_id where g.goods_name like '%$keyword%' and issale = 1";
            $m = new GoodsModel;
            $data = $m->query($sql);
            
            //获取得到的商品名称
            $sql2 = "select distinct brand_num from gs_goods where goods_name like '%$keyword%' and issale = 1";
            $brand_num = $m->query($sql2);
            if($brand_num)
            {
                 foreach ($brand_num as $num)
                 { 
                    foreach ($num as $n){
                        $brandinfo[] = Db::name('brand')->where("bra_num='$n'")->find(); 
                    } 
                 }
                session('brandinfo',$brandinfo);
            }else{
                $this->error('该类商品不存在','/index/index/index','2');
            }
            
             //获取得到的商品---》查分类
             $sql = "select distinct cate_num from gs_goods where goods_name like '%$keyword%' and issale = 1";
             $cate_num = $m->query($sql);
              foreach ($cate_num as $cate)
            { 
                foreach ($cate as $c){
                    $catename[] = Db::name('category')->where("cate_num='$c'")->find(); 
                } 
            }
           // var_dump($catename);
            session('catename',$catename);
            $this->assign(['data'=>$data,'keyword'=>$keyword,'id'=>$id]);  
        }

        //价格排序
        if(input('param.order') == 'price')
        {
            $keyword = input('param.key');
            $id = input('param.id');
            //判断来源类型
            if($id == 2){
                $sql = "select * from gs_goods as g left join gs_images as i on g.goods_id=i.goods_id where g.goods_name like '%$keyword%' and issale = 1 order by g.price asc";
            }else{
                 $sql = "select * from gs_goods as g left join gs_images as i on g.goods_id=i.goods_id where g.cate_num='$keyword' and issale = 1 order by g.price asc";
            }
            $m = new GoodsModel;
            $data = $m->query($sql);  
            $this->assign(['data'=>$data,'keyword'=>$keyword,'id'=>$id]);    
        }

        //销量排序
        if(input('param.order') == 'sales')
        {
            $keyword = input('param.key');
            $id = input('param.id');
            if($id == 2){
                $sql = "select * from gs_goods as g left join gs_images as i on g.goods_id=i.goods_id where g.goods_name like '%$keyword%' and issale = 1 order by g.sales desc";
            }else{
                 $sql = "select * from gs_goods as g left join gs_images as i on g.goods_id=i.goods_id where g.cate_num='$keyword' and issale = 1 order by g.sales desc";
            }
            $m = new GoodsModel;
            $data = $m->query($sql);  
            $this->assign(['data'=>$data,'keyword'=>$keyword,'id'=>$id]);    
        }

        //品牌筛选
        if(input('param.order') == 'brand')
        {
            $keyword = input('param.key');
            $id = input('param.id');
            //查找品牌
            $bra_id = input('param.bra_id');
            if($id == 1)
            {
                if($bra_id == -1){
                         $sql = "select * from gs_goods as g left join gs_images as i on g.goods_id=i.goods_id where g.cate_num = '$keyword' and issale = 1";
                }else{
                     $bra_num = Db::name('brand')->where("bra_id=$bra_id")->value('bra_num');
                     $sql = "select * from gs_goods as g left join gs_images as i on g.goods_id=i.goods_id where g.cate_num = '$keyword' and g.brand_num = '$bra_num' and issale = 1";
                    }
            }else{
                if($bra_id == -1){
                    $sql = "select * from gs_goods as g left join gs_images as i on g.goods_id=i.goods_id where g.goods_name like '%$keyword%' and issale = 1";
                }else{
                    $bra_num = Db::name('brand')->where("bra_id=$bra_id")->value('bra_num');
                    $sql = "select * from gs_goods as g left join gs_images as i on g.goods_id=i.goods_id where g.brand_num = '$bra_num' and g.goods_name like '%$keyword%' and issale = 1";
                }
            }

            $m = new GoodsModel;
            $data = $m->query($sql);  
            $this->assign(['data'=>$data,'keyword'=>$keyword,'id'=>$id]); 
        }

        return $this->fetch();
    }



}
