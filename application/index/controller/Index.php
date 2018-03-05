<?php

namespace app\index\controller;
use think\Session;
use think\Cookie;
use think\Controller;
use think\Request;
use think\Db;
use app\index\model\User as UserModel;
use yzx\lib\Ucpaas;
use think\captcha\Captcha;
use sina\SaeTClientV2;
use sina\SaeTOAuthV2;


class Index extends Base
{
    //商城主页 无限极分类实现菜单栏
    public function index()
    {
         if (session('token')['access_token']) {
            $o = new SaeTClientV2( WB_AKEY , WB_SKEY ,session('token')['access_token']);
            $ms  = $o->home_timeline(); // done
            $uid_get = $o->get_uid();
            $uid = $uid_get['uid'];       
            $user_message = $o->show_user_by_id($uid);//根据ID获取用户等基本信息   
            //头像
            $user_picture = $user_message['profile_image_url'];
            //var_dump($user_message);
            $exists = Db::name('user')->where("weibo_id=$uid")->find();
            $weibo_id = $exists['weibo_id']; 

            if($exists == null){
                $arr = array("username"=>$user_message['screen_name'],"weibo_id"=>$uid,"is_weibo"=>1,"user_picture"=>$user_picture);
                $id = Db::name('user')->insertGetId($arr);
                session('user_id',$id);
            }else{
                $arr1 = array("weibo_id"=>$weibo_id,"username"=>$user_message['screen_name']);
                $new1 = Db::name('user')->where("weibo_id=$weibo_id")->update($arr1);
                session('user_id',$exists['user_id']);
            }
         }
            $user_id = session('user_id');
            $user = Db::name('user')->where('user_id',$user_id)->find();
            session('username',$user['username']);

            $data = Db::field('cate_name,cate_id,cate_pid,cate_num')->table('gs_category')->select();
            $newData = $this->make_tree($data);
            $this->assign(["newData"=>$newData,"user"=>$user]);
            return $this->fetch();
     }
        
    function make_tree($list,$pk='cate_id',$pid='cate_pid',$child='child',$root=0)
    {
        $tree=array();
        $packData=array();
        foreach ($list as  $data) {
            $packData[$data[$pk]] = $data;
        }
        foreach ($packData as $key =>$val){  
            if($val[$pid]==$root){//代表根节点 
                $tree[] = &$packData[$key];
                //var_dump($tree);
           }else{
                //找到其父类
                $packData[$val[$pid]][$child][] = &$packData[$key]; 
            }
        }
         return $tree;
    }


    //微博登陆的回调方法
    public function callback()
    {
        $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
        $keys = array();
        $param = Request::instance()->param();
        $keys['code'] = $param['code'];
        $keys['redirect_uri'] = WB_CALLBACK_URL;
        $token = $o->getAccessToken( 'code', $keys ) ;
        if($token){
            session('token',$token);
            $js = <<<ABC
            <script type="text/javascript">
             alert('授权成功');
            </script>
ABC;
            echo $js;
            return $this->fetch('callback');
        }else{
            return $this->error('授权失败','/index/index/login','','2');
        }
    }


    //微博列表页
    public function weibolist()
    {
        $o = new SaeTClientV2( WB_AKEY , WB_SKEY ,session('token')['access_token']);
        $ms  = $o->home_timeline(); // done
        $uid_get = $o->get_uid();
        $uid = $uid_get['uid'];
        $user_message = $o->show_user_by_id($uid);//根据ID获取用户等基本信息
        $this->assign([
            'ms'=>$ms,
            'user_message'=>$user_message
        ]);
        return $this->fetch();
    }


     //登录页面
    public function login()
    {
       $o = new SaeTOAuthV2( WB_AKEY , WB_SKEY );
       $code_url = $o->getAuthorizeURL( WB_CALLBACK_URL );
       $this->assign('code_url',$code_url);
       return $this->fetch();
    }

    //ajax查询用户名是否存在
    public function selogin()
    {
        $username = input('post.username');
        $data = Db::name('user')->where('username',$username)->find();
        if($data){
            echo json_encode(["state"=>1]);
        } else {
            echo json_encode(["state"=>0]);
        }
    }

    public function doLogin()
    {
        $username = input('post.username');
        $password = input('post.password');
        $selUser = Db::name('user')->where('username',$username)->find();
        $user_id = $selUser['user_id'];
        $isactive = $selUser['isactive'];
        if($isactive == 1){
            if(strcmp(md5($password),$selUser['password'])){
                $this->error('密码错误，请重新输入','login','','2');
            }else{
                session('username',$username);
                session('usertype',1);
                session('user_id',$user_id);
                //判断是否有cookie购物车的信息
                if(!empty(cookie('cookieValue'))){
                    $cookieValue = cookie('cookieValue');
                    $goodsInfo = unserialize($cookieValue);
                    $goods_id = $goodsInfo['goods_id'];
                    $ip = $goodsInfo['ip'];
                 
                    $request = Request::instance();
                    $localip = $request->ip();
                    if($localip = '0.0.0.0'){
                        $localip = '127.0.0.1';
                    }
                    $localip = ip2long($localip);
                    $sql = 'select * from gs_cart where goods_id="$goods_id"&ip="$localip"';
                    $check = UserModel::query($sql);
                   if($check){
                        $data = [
                            'goods_id'=>$goods_id,
                            'ip'=>$localip
                        ];
                        $update = Db::name('cart')->where($data)->update(['user_id'=>$user_id]);
                        //cookie('cookieValue', null);
                        Cookie::delete('cookieValue');
                   } 
                }
                $this->redirect('index');
            }
        }else{
            $this->error('该用户尚未激活，请再激活后登录','/index/index/login','','2');
        }
        
    }

    //注册页面
    public function register()
    {
        return $this->fetch();
    }

    //ajax判断是否用户名是否存在
    public function selname(){
         $username = input('post.username');
         $data = Db::name('user')->where('username',$username)->find();
        if($data){
            echo json_encode(["state"=>1]);
        } else {
            echo json_encode(["state"=>0]);
        }
    }

    //处理提交数据
    public function doregister()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if($ip == '::1')
        {  
            $ip = '127.0.0.1';
        }
        $userinfo['username'] = input('post.username');
        $userinfo['regip'] =  ip2long($ip);
        $userinfo['password'] = md5(input('post.password'));
        $email = input('post.email');
        $userinfo['email'] = $email;
        $userinfo['phone'] = input('post.phone');
        $userinfo['regtime'] = time();
        $adduser = Db::name('user')->insert($userinfo);
        $user_id = Db::name('user')->getLastInsID();

        if($adduser){
                //生成随机的激活码
                $code = substr(md5(uniqid()),mt_rand(0,24),8);
                $mailData['mailcode'] = $code;
                Db::name('user')->where('user_id',$user_id)->update($mailData);
                //发送邮件
                $toemail = $email;
                $title='邮件激活';
                $content = <<<DOC
                欢迎您{$userinfo['username']}请<a href="http://www.bestwonderfulu.cn/index/index/active?code={$code}&user_id={$user_id}">激活</a>
DOC;
                $result = send_mail($toemail,$title,$content);
                if($result){
                      $this->success('注册成功,激活邮件已经发送到您的邮箱，请在激活后登录','login','',2);
                }else{
                     $this->error('激活邮件发送失败','register','','2');
                }
        } else {
           $this->error('注册失败','register','','2');
        }
    }

    //处理激活码
    public function active()
    {
        //用户提交的code和user_id
       $code =  input('get.code');
       $user_id = input('user_id');
       $mail = Db::name('user')->where('user_id',$user_id)->find();
       if (strlen($code) != 8) {
            exit('激活码长度不正确');
        }
       if(strcmp($code, $mail['mailcode'])){
            exit('激活码不合法');
        }else{
            $res = Db::name('user')->where('user_id',$user_id)->update(['isactive'=>'1']);
        }
        if($res){
            $this->success('激活成功');
        }else{
             $this->error('激活失败');
        }
    }

    //退出登录
    function logout()
    {
        session(null);
        $this->redirect('index');
    }

    //忘记密码
    function forget()
    {
       return $this->fetch('forget');
    }
    
    //通过ajax提交手机号，异步判断
    function checkphone()
    {
        $phone = input('post.phone');
        if($phone){
            //发送短信验证码
            sendmsg($phone); 
            echo json_encode(["state"=>1]); 
        } else {
            echo json_encode(["state"=>0]);
        }
        $user_id = Db::name('user')->where('phone',$phone)->value('user_id'); 
        session('user_id',$user_id);   
    }

    function doforget()
    {
        $phoneyzm = input('post.phoneyzm');
        $phonecode = session('phonecode');
        if(strcmp($phoneyzm, $phonecode)){
            $this->error('短信验证码不正确','forget','','2');
        }else{
             //得到短信验证码，进行下一步
            return $this->fetch('setpwd');
        }
    }

    //ajax判断验证码
   function checkyzm()
    {
        $yzm = input('post.yzm');
        $captcha = new Captcha();
      if($captcha->check($yzm)){
            echo json_encode(["state"=>1]);
        } else {
            echo json_encode(["state"=>0]);
        }
   }


    function setpwd()
    {
        return $this->fetch();
    }
    
    function dosetpwd()
    {
        $pwd = input('post.password');
        $repwd = input('post.repwd');
        if(strcmp($pwd,$repwd))
        {
             $this->error('两次密码不一致','setpwd','','2');   
        }else{
             $user_id = session('user_id');
             $password = md5($pwd);
             $upd = Db::name('user')->where('user_id',$user_id)->setField('password',$password);
                if($upd){
                    $this->success('密码修改成功','login','','2');  
                }else{
                   $this->error('数据更新失败，请重试','forget','','2');
                }
        } 
    }

   
}

    

