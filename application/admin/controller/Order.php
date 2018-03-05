<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Db;
use app\admin\controller\Base;

class Order extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function orderDetail()
    {
        $ord_id = input('get.ord_id');
        $result = Db::query("select * from gs_order left join gs_address on gs_order.add_id = gs_address.add_id where ord_id=$ord_id");
        $order = Db::query("select * from gs_order left join gs_images on gs_order.goods_id = gs_images.goods_id where ord_id=$ord_id");
        $this->assign('result',$result);
        $this->assign('order',$order);
        return $this->fetch('order_detail');
    }

    public function orderList()
    {
        $result = Db::query("select * from gs_order left join gs_address on gs_order.add_id = gs_address.add_id where gs_order.is_del=0");
        $count = count($result);
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $num = 5;
        $total = ceil($count / $num);
        if ($page <= 1) {
            $page = 1;
        }
        if ($page >= $total) {
            $page = $total;
        }
        $offest = ($page - 1) * $num;
        $data = DB::query("select * from gs_order left join gs_address on gs_order.add_id = gs_address.add_id where gs_order.is_del=0 limit $offest,$num");
        $this->assign('data',$data);
        $this->assign('page', $page);
        $this->assign('count',$count);
        $this->assign('num',$num);
        return $this->fetch('order_list');
    }

    public function del($ord_id) 
    {
        $data['is_del'] = 1;
        $res = Db::name('order')->where(array('ord_id'=>$ord_id))->update($data);
        if ($res) {
            $this->success('删除成功，你可以在回收站恢复','orderList');
        } else {
            $this->error('删除失败');
        }
    }

    //回收站
    public function orderRecycle()
    {
        $result = Db::query("select * from gs_order left join gs_address on gs_order.add_id = gs_address.add_id where gs_order.is_del=1");
        $count = count($result);
        if ($count) {
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            $num = 5;
            $total = ceil($count / $num);
            if ($page <= 1) {
                $page = 1;
            }
            if ($page >= $total) {
                $page = $total;
            }
            $offest = ($page - 1) * $num;
            $data = DB::query("select * from gs_order left join gs_address on gs_order.add_id = gs_address.add_id where gs_order.is_del=1 limit $offest,$num");
            $this->assign('data',$data);
            $this->assign('page', $page);
            $this->assign('count',$count);
            $this->assign('num',$num);
        } else {
            $count = 0;
            $page = 0;
            $num = 0;
            $this->assign('page', $page);
            $this->assign('count',$count);
            $this->assign('num',$num);            
        }
        
        return $this->fetch('order_recycle');        
    }
    //回收站恢复订单
    public function recovery()
    {
        $ord_id = input('get.ord_id');
        $data['is_del'] = 0;
        $res = Db::name('order')->where('ord_id',$ord_id)->update($data);
        if ($res) {
            $this->success('恢复成功，您可以在订单列表查看','orderList');
        } else {
            $this->error('恢复失败');
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
