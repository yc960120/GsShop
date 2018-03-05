<?php

namespace app\index\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\index\model\User as UserModel;
class User extends Base
{
   
     //用户信息的首页
    public function index()
    {
        if(!session('user_id')){
            $this->error('请先登录','/index/index/login','','2');
        }else{
            $user_id = session('user_id');
            $user = Db::name('user')->where('user_id',$user_id)->find();
            //查询收藏
            $collection = Db::table('gs_collect')
                            ->alias('c')
                            ->join('gs_images i','c.goods_id=i.goods_id')
                            ->join('gs_goods g','c.goods_id=g.goods_id')
                            ->where("user_id=$user_id")
                            ->select();
           
        //查询待付款
            $unpay = Db::name('order')->where("user_id=$user_id&&ord_status=0")->select();
            $unpayCount = count($unpay);
        //查询待发货
            $unpost = Db::name('order')->where("user_id=$user_id&&ord_status=1")->select();
            $unpoCount = count($unpost);
        //查询待收货
            $unreceive = Db::name('order')->where("user_id=$user_id&&ord_status=2")->select();
            $unreCount = count($unreceive);
        //查询待评价
            $uncomment = Db::name('order')->where("user_id=$user_id&&ord_status=3")->select();
            $uncomCount = count($uncomment);
        //查询退货
            $returnCount = Db::name('order')->where("user_id=$user_id&&ord_status=4")->select();
            $returnCount = count($returnCount);
            $this->assign(['user'=>$user,'collection'=>$collection,'unpayCount'=>$unpayCount,'unpoCount'=>$unpoCount,'unreCount'=>$unreCount,'uncomCount'=>$uncomCount,'returnCount'=>$returnCount]);
            return $this->fetch();
        } 
    }

     //修改用户密码
    public function password()
    {
        if(input('post.submit')){
           // dump(input('post.'));
            $user_id = session('user_id');
            $newpwd = md5(input('post.newpwd'));
            $r = Db::name('user')->where('user_id',$user_id)->update(['password'=>$newpwd]);
            if($r){
                echo "<script type='text/javascript'>alert('修改成功');</script>";
            }
        }
        return $this->fetch();
    }

    public function checkpwd()
    {
        if(input('param.')){
            $user_id = session('user_id');
            $password = input('post.password');
            $password = md5($password);
            $result = Db::name('user')->where("user_id",$user_id)->value('password');
            if(strcmp($result,$password)){
                echo json_encode(["state"=>0]);
            }else{
                echo json_encode(["state"=>1]);
            }
        }
    }

    //用户的email
    public function email()
    {
        return $this->fetch();
    }


    //用户的个人资料
    public function userinfo()
    {
        $user_id = session('user_id');
        $result = Db::name('user')->where("user_id=$user_id")->select();

        //头像
        if(input('post.'))
        {
            if(request()->file('image')){
            $file = request()->file('image');
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
            if($info){
            // 成功上传后 获取上传信息
                 $fileName = $info->getSaveName();
                 $imagePath = '/'.'upload/'. str_replace('\\', '/', $fileName);
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
            $data = ['user_picture'=>$imagePath];
            Db::name('user')->where("user_id=$user_id")->update($data);
            $this->redirect('/index/user/userinfo');
        }else{
                $data = input('post.');
                array_pop($data);
                $year = $data['year'];
                $month = $data['month'];
                $day = $data['day'];
                
                $birth = compact('year','month','day');
                $birth = implode('-',$birth);
                $data = [
                    "username"=>input('post.username'),
                    "realname"=>input('post.realname'),
                    "sex"=>input('post.sex'),
                    "birth"=>$birth,
                    "email"=>input('post.email'),
                    "phone"=>input('post.phone'),
                ];
                $data['birth'] = $birth;
                Db::name('user')->where("user_id=$user_id")->update($data);    
        }
    }

        $this->assign(["result"=>$result]);
        return $this->fetch();
    }


    //用户的收货地址
    public function address()
    {
        $user_id = session('user_id');
         $result=Db::name('address')->where("user_id",$user_id)->select();
        $this->assign('result',$result);
        return $this->fetch();
    }

    //用户的绑定的手机
    public function bindphone()
    {
        return $this->fetch();
    }


    //用户的红包
    public function red()
    {
        return $this->fetch();
    }

   
     //用户的收藏
    public function collection()
    {
        $user_id = session('user_id');
        $result = Db::name('collect')->where('user_id',$user_id)->distinct(true)->field('goods_id')->select();
        foreach($result as $value){
             $goods_id = $value['goods_id'];
             $selGoods[] = Db::table('gs_goods')
                        ->alias('g')
                        ->join('gs_images i','g.goods_id=i.goods_id')
                        ->where("g.goods_id=$goods_id")
                        ->select(); 
        }
        if(!empty($selGoods)){
            $this->assign('selGoods',$selGoods);
        }
       
        return $this->fetch();
    }

    //用户的优惠券
    public function discount()
    {
        return $this->fetch();
    }


    //用户的账单
    public function bill()
    {
        return $this->fetch();
    }


    //用户的账单明细
    public function billlist()
    {
        return $this->fetch();
    }

  

    //商城推送的消息
    public function news()
    {
        return $this->fetch();
    }

   
      //设置安全问题
    public function question()
    {
        return $this->fetch();
    }

    //钱款去向
    public function record()
    {
        return $this->fetch();
    }

   
    //账户安全
    public function safety()
    {
        return $this->fetch();
    }

    //设置支付密码
    public function setpay()
    {
        return $this->fetch();
    }
    

}
