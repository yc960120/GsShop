<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller\Base;

class Authgroup extends Base
{
    //用户组列表
    public function lst()
    {
        $data = Db::name('auth_group')->select();
        foreach ($data as $key=>$val) {
            $data[$key]['child'] = explode(',', $val['rules']);
        }
        foreach ($data as $key=>$val) {
            foreach($val['child'] as $k=>$v) {
                $data[$key]['child'][$k]= Db::name('auth_rule')->where(array('id'=>$v))->find();
            }
        }
        /*foreach($data as $key=>$val) {
            var_dump($val['child']);
        }
        die;*/
        $this->assign('data',$data);
        return $this->fetch();
    }
    //添加用户组
    public function add()
    {
        if(request()->post()) {
            /*$user = input('post.user');
            $rules = input('post.rules');
            var_dump($rules);
            $status = input('post.status');
            $title = input('post.title');
            $data['status'] = $status;
            $data['title'] = $title;*/

            $arr = input('post.');
            $user = $arr['user'];
            $title = $arr['title'];
            $rules = $arr['rules'];
            $data['title'] = $title;
            $data['rules'] = $rules;

            $data['rules'] = implode(',', $rules);          
            $res = Db::name('auth_group')->insert($data);
            $groupId = Db::name('auth_group')->getLastInsID();

            $data1['uid'] = $user;
            $data1['group_id'] = $groupId;
            $res1 = Db::name('auth_group_access')->insert($data1);
            if($res && $res) {
                $this->success('添加成功','lst');
            } else {
                $this->error('添加失败');
            }
        }
        $rule = Db::name('auth_rule')->select();
        $user = Db::name('user')->select();
        $this->assign([
            'rule'=>$rule,
            'user'=>$user,
        ]);
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
            //var_dump(implode(',', $data['rules']));
            $data['rules'] = implode(',', $data['rules']);
            $res = Db::name('auth_group')->where(array('id'=>$id))->update($data);
            
            if ($res) {
                $this->success('修改成功','lst');
            } else {
                $this->error('修改失败');
            }
        }
        $data = Db::name('auth_group')->where(array('id'=>$id))->find();
        $data['child'] = explode(',', $data['rules']);
        
        
        $rule = Db::name('auth_rule')->select();
        $this->assign([
            'data'=>$data,
            'id'=>$id,
            'rule'=>$rule,
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
