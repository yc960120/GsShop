<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Session;
use think\Db;

class Index extends Controller
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        return $this->fetch();
        //return 1234;
    }

    public function login()
    {
        return $this->fetch();
    }
    public function doLogin()
    {
        $username = input('post.username');
        $pwd = md5(input('post.pwd'));
        $password = Db::name('user')->where(array('username'=>$username))->select();
        if (!strcmp($pwd, $password[0]['password'])) {
            $uid = Db::name('user')->where(array('username'=>$username))->value('user_id');
            Session::set('user_id',$uid);

            $this->success('恭喜您，登录成功','index');
        } else{
            $this->error('密码错误','login');
        }
        
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
