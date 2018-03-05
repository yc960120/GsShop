<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Db;
use app\index\model\Order as OrderModel;

class Order extends Base
{
    //订单列表
    public function order()
    {
        $user_id = session('user_id');
        //每页显示3条数据
        $allOrder = Db::table('gs_order')
                ->alias('o')
                ->join('gs_images i','o.goods_id=i.goods_id')
                ->where("user_id",$user_id)
                ->order("ord_time desc")
                ->paginate(3);

    //未付款
        $unpay = Db::table('gs_order')
                ->alias('o')
                ->join('gs_images i','o.goods_id=i.goods_id')
                ->where(['user_id' => $user_id,'ord_status' => 4])
                ->order("ord_time desc")
                ->paginate(3);
   //待发货 
        $unsend = Db::table('gs_order')
                ->alias('o')
                ->join('gs_images i','o.goods_id=i.goods_id')
                ->where(['user_id' => $user_id,'ord_status' => 1])
                ->order("ord_time desc")
                ->paginate(3);
    //待收货 
        $unpost = Db::table('gs_order')
                ->alias('o')
                ->join('gs_images i','o.goods_id=i.goods_id')
                ->where(['user_id' => $user_id,'ord_status' => 2])
                ->order("ord_time desc")
                ->paginate(3);
    //待收货 
        $uncomment = Db::table('gs_order')
                ->alias('o')
                ->join('gs_images i','o.goods_id=i.goods_id')
                ->where(['user_id' => $user_id,'ord_status' => 3])
                ->order("ord_time desc")
                ->paginate(3);
            // var_dump($uncomment->toArray());

        $this->assign([
           "allOrder"=>$allOrder,
           "unpay"=>$unpay,
           "unsend"=>$unsend,
           "unpost"=>$unpost,
           "uncomment"=>$uncomment
        ]);
        return $this->fetch();
    }

   


 /* public function ajaxList(){
      //$page = request()->param('apage');
      $page = input('post.apage');
      if (!empty($page)) {
         $rel = model('Order')->paginate(10,false,[
            'type'     => 'Bootstrap',
            'var_page' => 'page',
            'page' => $page,
            'path'=>'javascript:AjaxPage([PAGE]);',
         ]);
         $page = $rel->render();
      }
      echo json_encode(['list'=>$rel,'page'=>$page]);
   }*/
   //显示分类管理界面
   /* public function listAction(){
        $list = model('Topic')->paginate(10,false,[
           'type'     => 'Bootstrap',
           'var_page' => 'page',
           'path'=>'javascript:AjaxPage([PAGE]);',
           //使用函数助手传入参数
           'query' => request()->param(),
        ]);
        //$res = $mem->getList();
        $this->assign('list',$list);
        return $this->fetch('list');
    }
*/

    //订单详情
    public function orderinfo()
    {
        return $this->fetch();
    }

    //退换货申请
    public function refund()
    {
        return $this->fetch();
    }


     //用户订单退换货管理
    public function change()
    {
        return $this->fetch();
    }

    //确认订单
    public function pay()
    {
        $user_id = session('user_id');
        $allAddr = Db::name('address')->where("user_id=$user_id")->select();
        $countAddr = count($allAddr);
        $default = Db::name('address')->where(["user_id"=>$user_id,"is_default"=>1])->find();
        $data = session('data');
        $countData = count($data);
        $this->assign([
            "data"=>$data,
            "default"=>$default,
            "allAddr"=>$allAddr,
            "countAddr"=>$countAddr,
            "countData"=>$countData,
        ]);
      return  $this->fetch();
    }

    //新增地址
    public function address()
    {
        $user_id = session('user_id');
        $consignee = input('post.consignee');
        $receiverphone = input('post.receiverphone');
        $address = input('post.address');
        $data = [
                'user_id'=>$user_id,
                'consignee'=>$consignee,
                'receiverphone'=>$receiverphone,
                'address'=>$address
        ];
        $result = Db::name('address')->insert($data);
        $this->redirect('/index/order/pay');
    }

    //默认地址
    public function setdefault($add_id)
    {
       $user_id = session('user_id');
       $result = Db::name('address')->where("user_id=$user_id&is_default=1")->update(['is_default'=>0]);
       $res =  Db::name('address')->where("add_id=$add_id")->update(['is_default'=>1]);
       $this->redirect(url('/index/order/pay'));
    }
    //删除地址
    public function deleteAddr($add_id)
    {
        Db::name('address')->where("add_id=$add_id")->delete();
        $this->redirect(url('/index/order/pay'));
    }

    
    //确认订单
    public function firmOrder()
    {
        if(input('post.submit')){
           $tmp = input('post.');
          //得到的一维按数组长度为7切分
           $data = array_chunk($tmp,7,true);
          //弹出最后一个
           $data1 = array_pop($data);
           //固定一个订单号
            $ord_num = build_order_no();
           //循环3次
           foreach ($data as $key => $value) {
               $ord_status = 0;
               $user_id = session('user_id');
               $ord_time = time();
               $tasty = $value['tasty'.$key];
               $package = $value['package'.$key];
               $ord_info = "$tasty.$package";
               $goods_name = $value['goods_name'.$key];
               $goods_id = Db::name('goods')->where("goods_name",$goods_name)->value('goods_id');
             
              $insData = [
                'user_id'=>$user_id,
                'ord_info'=>$ord_info,
                'ord_time'=>$ord_time,
                'ord_num'=>$ord_num,
                'ord_status'=>$ord_status,
                'goods_id'=>$goods_id,
                'add_id'=>$value['add_idx'.$key],
                'goods_name'=>$value['goods_name'.$key],
                'price'=>$value['price'.$key],
                'total_price'=>$value['total_price'.$key],
                'num'=>$value['num'.$key]       
             ];
              $ord = Db::name('order')->insert($insData);
           }
              if($ord){
                //删除提交订单中的购物车的商品信息
                $cartData = session('data');
                if(!empty($cartData[0]['cart_id']))
                {
                      foreach ($cartData as $key => $value) {
                      Db::name('cart')->where("cart_id",$value['cart_id'])->delete();
                    }
                }
                
                 session('data',null);
                 session('data1',null);
                 session('tmp',null); 
               return $this->redirect(url('/index/order/success1/ord'.$ord));
               }
        }    
    }
        
    //订单交易成功，商家待处理   
     public function success1()
    {
        $ord = input('param.ord');
        $address = Db::name('order')->where("ord_id",$ord)->select();

        $this->assign('address',$address);
        return $this->fetch();
    }
       


    //物流跟踪
    public function logistics()
    {
        //调用快递100接口
        $typeCom = input('param.com');//快递公司
        $typeNu = input('param.nu');; //快递单号
        $AppKey='6c028940dff54d92';//请将XXXXXX替换成您在http://kuaidi100.com/app/reg.html申请到的KEY
        $url ='http://api.kuaidi100.com/api?id='.$AppKey.'&com='.$typeCom.'&nu='.$typeNu.'&show=2&muti=1&order=asc';

        //请勿删除变量$powered 的信息，否者本站将不再为你提供快递接口服务。
        $powered = '查询数据由：<a href="http://kuaidi100.com" target="_blank">KuaiDi100.Com （快递100）</a> 网站提供 ';

        //优先使用curl模式发送数据
        if (function_exists('curl_init') == 1){
          $curl = curl_init();
          curl_setopt ($curl, CURLOPT_URL, $url);
          curl_setopt ($curl, CURLOPT_HEADER,0);
          curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
          curl_setopt ($curl, CURLOPT_USERAGENT,$_SERVER['HTTP_USER_AGENT']);
          curl_setopt ($curl, CURLOPT_TIMEOUT,5);
          $get_content = curl_exec($curl);
          curl_close ($curl);
        }else{
          include("snoopy.php");
          $snoopy = new snoopy();
          $snoopy->referer = 'http://www.google.com/';//伪装来源
          $snoopy->fetch($url);
          $get_content = $snoopy->results;
        }
        print_r($get_content . '<br/>' . $powered);
      }


    //确认收货
    public function firmReceive($ord_id)
    {
      $result = Db::name('order')->where("ord_id=$ord_id")->update(['ord_status'=>3]);
        $this->redirect('/index/order/order');
     
     
    }



   //订单支付成功,与thinkphp内置方法重名了
  /*  public function success()
    {
        return $this->fetch();
    }*/

}
