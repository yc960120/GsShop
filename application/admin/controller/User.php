<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller\Base;

class User extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function userDetail($id)
    {
        $data = Db::name('user')->where(array('user_id'=>$id))->find();
        $this->assign([
            'data'=>$data,
        ]);
        return $this->fetch('user_detail');
    }

    public function userList()
    {
        $data = Db::name('user')->select();
        $this->assign([
            'data'=>$data,
        ]);
        return $this->fetch('user_list');
    }

    public function userRank()
    {
        return $this->fetch('user_rank');
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
