<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller\Base;

class Authrule extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function lst()
    {
        $result = Db::name('auth_rule')->select();
        $count = count($result);
        //当前页码数
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        //每页现实的数据条数
        $num = 10;
        //总页数
        $total = ceil($count / $num);
        if($page <= 1) {
            $page = 1;
        }
        if ($page >= $total) {
            $page = $total;
        }
        $offest = ($page - 1) * $num;
        $data = Db::name('auth_rule')->limit($offest,$num)->select();
        $this->assign('count',$count);
        $this->assign('num',$num);
        $this->assign('page',$page);
        $this->assign('data',$data); 
        return $this->fetch('list');
    }
    //添加权限
    public function add(Request $request)
    {
        if (request()->post()) {
            $data = input('post.');
            $res = Db::name('auth_rule')->insert($data);
            if($res) {
                $this->success('添加规则成功','/admin/Authrule/lst');
            }
        }
        return $this->fetch();
    }


    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        if (request()->post()) {
            $data = input('post.');
            $res = Db::name('auth_rule')->where('id',$id)->update($data);
            if ($res) {
                $this->success('修改成功', 'lst');
            } else {
                $this->error('修改失败');
            }
        }
        $data = Db::name('auth_rule')->where(array('id'=>$id))->find();
        $this->assign([
            'data'=>$data,
            'id'=>$id,
        ]);
        return $this->fetch();
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
