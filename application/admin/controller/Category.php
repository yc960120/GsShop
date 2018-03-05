<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller\Base;

class Category extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function categoryList()
    {
        $data = Db::field('cate_name,cate_id,cate_pid,cate_num')->name('category')->select();
        $newData = $this->make_tree($data);
        $this->assign('newData',$newData);
        return $this->fetch('category_list');
    }
    public function categoryAdd()
    {
        $data = Db::field('cate_name,cate_id,cate_pid,cate_num')->name('category')->select();
        $newData = $this->make_tree($data);
        // var_dump($newData);
        // die;
        $this->assign('newData',$newData);
        return $this->fetch('category_add');
    }
    //无限级分类
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


    public function doCategoryAdd()
    {
        $data['cate_name'] =input('post.catename');
        $data['cate_num'] = input('post.catenum');
        $data['cate_pid'] = input('post.catepid');
        $data1 = Db::name('category')->insert($data);
        if ($data1) {
            $this->success('添加成功','categoryList');
        }

    }
    public function checkCategory()
    {
        $catename = input('post.catename');
        $data = Db::name('category')->where(array('category_name'=>$catename))->select();
        if($data) {
            echo json_encode(["state"=>1]);
        } else {
            echo json_encode(["state"=>0]);
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
