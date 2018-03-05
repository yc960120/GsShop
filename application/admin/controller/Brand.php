<?php

namespace app\admin\controller;

use app\admin\model\Brand as BrandModel;
use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller\Base;

class Brand extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function brandList()
    {
        //获取品牌数量
        $count = Db::name('brand')->where(array('is_del'=>0))->count();
        //当前页码数
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        //每页显示数据条数
        $num = 5;
        //总页数
        $total = ceil($count / $num);
        if ($page <= 1) {
            $page = 1;
        }
        if ($page >= $total) {
            $page = $total;
        }
        $offest = ($page - 1) * $num;
        $data = Db::name('brand')->where('is_del',0)->limit($offest,$num)->select();
        //$data = Db::name('brand')->select();
        $this->assign('data',$data);
        $this->assign('count',$count);
        $this->assign('num',$num);
        $this->assign('page',$page);
        return $this->fetch('brand_list');
    }
    public function brandAdd()
    {
        $data = Db::name('category')->where('cate_pid','neq',0)->select();
        //$data = Db::query("select gs_category.cate_id,gs_category.cate_name,gs_brand.bra_name from gs_category left join gs_brand on gs_category.cate_id = gs_brand.cate_id");
        $this->assign('data',$data);
        return $this->fetch('brand_add');
    }
    public function doBrandAdd()
    {
        $brandname = input('post.brandname');
        $brandnum = input('post.brandnum');
        $data['bra_name'] = $brandname;
        $data['bra_num'] = $brandnum;
        $data1 = Db::name('brand')->insert($data);
        if ($data1) {
            $this->success('添加成功','brandList');
        }
              
    }
    public function checkName()
    {
        $brandname = input('post.brandname');
        $data = Db::name('brand')->where('bra_name',$brandname)->select();

        if ($data) {
            echo json_encode(["state"=>1]);
        } else {
            echo json_encode(["state"=>0]);
        }        
    }

    //删除
    public function del()
    {
        $bra_id = input('get.bra_id');
        Db::name('brand')->where(array('bra_id'=>$bra_id))->update(['is_del'=>1]);
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
