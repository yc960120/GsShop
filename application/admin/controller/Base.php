<?php
namespace app\admin\controller;
use think\Controller;
use think\Request;
use think\Auth;

class Base extends Controller{
    public function _initialize()
    {
       /*import('ORG.Util.Auth');//加载类库
       $auth=new Auth();
       if(!$auth->check(MODULE_NAME.'-'.ACTION_NAME,session('uid'))){
            $this->error('你没有权限');
       }*/

      $auth = new Auth();
      $request = Request::instance();
      $model = $request->module();
      $controller = $request->controller();
      $action = $request->action();
      $name = $model. '/' .$controller.'/'.$action;
       //var_dump($name);

       /*$arr = array('Index/index','Goods/goodsList','Goods/goodsAdd','Goods/goodsAllAdd');
       if (session('user_id') !=1) {
       		if (!in_array($name, $arr)) {
       			$this->error('抱歉，你没有权限访问',url('Index/index'));
       		}
       }*/
        if (!$auth->check($name,session('user_id'))) {
           $this->error('抱歉，您没有权限访问','/admin/index/index/');
       }


    }

}