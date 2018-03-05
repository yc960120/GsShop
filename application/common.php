<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/*发送邮件方法
*@param $to：接收者 $title：标题 $content：邮件内容
*@return bool true:发送成功 false:发送失败
*/
use think\Db;
use yzx\lib\Ucpaas;
/*发送邮件*/
function send_Mail($to,$title,$content){

//引入PHPMailer的核心文件 使用require_once包含避免出现PHPMailer类重复定义的警告
	/*	require_once("../PHPMailer/src/PHPMailer.php");
		require_once("../PHPMailer/src/SMTP.php");*/
	//require_once ("../vendor/autoload.php");
//实例化PHPMailer核心类
	$mail =new \PHPMailer\PHPMailer\PHPMailer();

//是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
	$mail->SMTPDebug = 0;

//使用smtp鉴权方式发送邮件
	$mail->isSMTP();

//smtp需要鉴权 这个必须是true
	$mail->SMTPAuth=true;

//链接qq域名邮箱的服务器地址
	$mail->Host = 'smtp.qq.com';

//设置使用ssl加密方式登录鉴权
	$mail->SMTPSecure = 'ssl';

//设置ssl连接smtp服务器的远程服务器端口号，以前的默认是25，但是现在新的好像已经不可用了 可选465或587
	$mail->Port = 465;

//设置smtp的helo消息头 这个可有可无 内容任意
// $mail->Helo = 'Hello smtp.qq.com Server';

//设置发件人的主机域 可有可无 默认为localhost 内容任意，建议使用你的域名
	$mail->Hostname = 'http://halsh.cn';

//设置发送的邮件的编码 可选GB2312 我喜欢utf-8 据说utf8在某些客户端收信下会乱码
	$mail->CharSet = 'UTF-8';

//设置发件人姓名（昵称） 任意内容，显示在收件人邮件的发件人邮箱地址前的发件人姓名
	$mail->FromName = '何遇';

//smtp登录的账号 这里填入字符串格式的qq号即可
	//$mail->Username ='wgq@yexiaocangji.cn';
	$mail->Username ='heyuu@halsh.cn';

//smtp登录的密码 使用生成的授权码（就刚才叫你保存的最新的授权码）
	$mail->Password = 'wksezqxocxsajgci';

//设置发件人邮箱地址 这里填入上述提到的“发件人邮箱”------->设置的域名邮箱
	$mail->From = 'heyuu@halsh.cn';

//邮件正文是否为html编码 注意此处是一个方法 不再是属性 true或false
	$mail->isHTML(true);

//设置收件人邮箱地址 该方法有两个参数 第一个参数为收件人邮箱地址 第二参数为给该地址设置的昵称 不同的邮箱系统会自动进行处理变动 这里第二个参数的意义不大
	$mail->addAddress($to,'lsgo在线通知');

//添加多个收件人 则多次调用方法即可
// $mail->addAddress('xxx@163.com','lsgo在线通知');

//添加该邮件的主题
	$mail->Subject = $title;

//添加邮件正文 上方将isHTML设置成了true，则可以是完整的html字符串 如：使用file_get_contents函数读取本地的html文件
	$mail->Body = $content;

//为该邮件添加附件 该方法也有两个参数 第一个参数为附件存放的目录（相对目录、或绝对目录均可） 第二参数为在邮件附件中该附件的名称
// $mail->addAttachment('./d.jpg','mm.jpg');
//同样该方法可以多次调用 上传多个附件
// $mail->addAttachment('./Jlib-1.1.0.js','Jlib.js');

	$status = $mail->send();

//简单的判断与提示信息
	if($status) {
		return true;
	}else{
		return false;
	}
}


//添加足迹
function addFoot($goods_id)
{
  if(!empty(session('user_id'))){
        $user_id = session('user_id');
        $selGoods = Db::name('foot')->where(['goods_id'=>$goods_id,'user_id'=>$user_id])->select();
        if(!$selGoods){
          $foot_time = time();
          $data = ['goods_id'=>$goods_id,'user_id'=>$user_id,'foot_time'=>$foot_time];
          $insFoot = Db::name('foot')->insert($data);
        }

        $selnum = Db::name('foot')->where("user_id=$user_id")->count('goods_id');
        if($selnum > 6){
          $sel = Db::name('foot')->order('foot_time desc')->limit('6,1')->where("user_id=$user_id")->select();
          $foot_id = $sel[0]['foot_id'];
          $delete = Db::name('foot')->where("foot_id=$foot_id")->delete();
        }
    }
 
}

//短信验证码
 	function sendmsg($user)
    {
        //初始化必填   接入参数
        $options['accountsid']='f7e84b445491203f17c6d672c8dfc5ca';
        $options['token']='c20d4455600edf14e542ad41fbcb63a9';
        //初始化 $options必填
        $ucpass = new Ucpaas($options);
        //开发者账号信息查询默认为json或xml
        header("Content-Type:text/html;charset=utf-8");
        //短信验证码（模板短信）,默认以65个汉字（同65个英文）为一条（可容纳字数受您应用名称占用字符影响），超过长度短信平台将会自动分割为多条发送。分割后的多条短信将按照具体占用条数计费。
        $appId = "e0e24139cf25496d9ecd97548c504075";
        //发送对象的手机号
        $to = $user;
        //短信模版编号
        $templateId = "158527";
        //验证码值替换掉函数中的参数
        $param="5201";
        session('phonecode',$param);
        $ucpass->templateSMS($appId,$to,$templateId,$param);
    }


    //序列化防止中文溢出
  /* function mb_unserialize($serial_str) {
	    $out = preg_replace_callback('#s:(\d+):"(.*?)";#s',function($match){return 's:'.strlen($match[2]).':"'.$match[2].'";';);
		return unserialize($out);
	}*/




	/*function mb_unserialize($serial_str) {
    $out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str);
    return unserialize($out);
}
*/


//GUID生成随机的永不重复的数字
 function create_guid($namespace = '') {   
  static $guid = '';
  $uid = uniqid("", true);
  $data = $namespace;
  $data .= $_SERVER['REQUEST_TIME'];
  $data .= $_SERVER['HTTP_USER_AGENT'];
  $data .= $_SERVER['LOCAL_ADDR'];
  $data .= $_SERVER['LOCAL_PORT'];
  $data .= $_SERVER['REMOTE_ADDR'];
  $data .= $_SERVER['REMOTE_PORT'];
  $hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
  $guid = '{' .  
      substr($hash, 0, 8) . 
      '-' .
      substr($hash, 8, 4) .
      '-' .
      substr($hash, 12, 4) .
      '-' .
      substr($hash, 16, 4) .
      '-' .
      substr($hash, 20, 12) .
      '}';
  return $guid;
 }


 //加上日期生成的订单号
 function build_order_no(){ 
    return date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8); 
} 