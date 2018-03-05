<?php

namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\Image;
use think\Db;
use app\admin\controller\Base;

class Goods extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function goodsList()
    {
        //data = Db::name('goods')->select();
        //$result =Db::query("select * from gs_goods left join gs_images on gs_goods.goods_id = gs_images.goods_id where gs_goods.is_del=0");
         $result = Db::name('goods')->where(array('is_del'=>0))->select();
        //获取商品数量
        $count = count($result);
        if($count) {
            //当前页码数
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            //每页现实的数据条数
            $num = 2;
            //总页数
            $total = ceil($count / $num);
            if($page <= 1) {
                $page = 1;
            }
            if ($page >= $total) {
                $page = $total;
            }
            $offest = ($page - 1) * $num;
            //$data = Db::query("select * from gs_goods left join gs_images on gs_goods.goods_id = gs_images.goods_id where gs_goods.is_del=0 limit $offest,$num");
            $data = Db::name('goods')->where(array('is_del'=>0))->limit($offest,$num)->select();
            foreach ($data as $key => $value) {
                $res = Db::name('images')->where(array('goods_id'=>$value['goods_id']))->find();
                $data[$key]['img_path'] = $res['img_path'];
            }
            //
            $this->assign('count',$count);
            $this->assign('num',$num);
            $this->assign('page',$page);
            $this->assign('data',$data);            
        } else {
            $count = 0;
            $num = 0;
            $page = 0;
            $this->assign('count',$count);
            $this->assign('num',$num);
            $this->assign('page',$page);             
        }
       
        return $this->fetch('product_list');
    }
    //删除商品
    public function destroy()
    {
        $goods_id = input('get.goods_id');
        $data['is_del'] = 1;
        Db::name('goods')->where(array('goods_id'=>$goods_id))->update($data);
        $this->redirect('/admin/goods/goodsList');
    }
    //促销商品操作
    public function discount()
    {
        $goods_id = input('get.goods_id');
        if (input('get.is_discount')) {
            $data['is_discount'] = 0;
            Db::name('goods')->where(array('goods_id'=>$goods_id))->update($data);
            $this->redirect('/admin/goods/goodsList');
        } else{
            $data['is_discount'] = 1;
            Db::name('goods')->where(array('goods_id'=>$goods_id))->update($data);
            $this->redirect('/admin/goods/goodsList');
        }
    }
    //新品商品操作
    public function isNew()
    {
        $goods_id = input('get.goods_id');
        if(input('get.isnew')) {
            $data['isnew'] = 0;
            Db::name('goods')->where(array('goods_id'=>$goods_id))->update($data);
            $this->redirect('/admin/goods/goodsList');
        } else {
            $data['isnew'] = 1;
            Db::name('goods')->where(array('goods_id'=>$goods_id))->update($data);
            $this->redirect('/admin/goods/goodsList');
        }
    }
    //热销商品操作
    public function ishot()
    {
        $goods_id = input('get.goods_id');
        if(input('get.ishot')) {
            $data['ishot'] = 0;
            Db::name('goods')->where(array('goods_id'=>$goods_id))->update($data);
            $this->redirect('/admin/goods/goodsList');
        } else {
            $data['ishot'] = 1;
            Db::name('goods')->where(array('goods_id'=>$goods_id))->update($data);
            $this->redirect('/admin/goods/goodsList');
        }        
    }

    public function goodsAdd()
    {
        $brand = Db::name('brand')->select();
        $category = Db::name('category')->where('cate_pid','neq',0)->select();
        $this->assign('brand',$brand);
        $this->assign('category',$category);
        return $this->fetch('product_add');
    }
    //添加商品
    public function doGoodsAdd()
    {
        

        $data['goods_name'] = input('post.goodsname');
        $data['goods_num'] = input('post.goodsnum');
        $data['brand_num'] = input('post.brandname');
        $data['cate_num'] = input('post.catename');
        $data['goods_info'] = input('post.info');
        $data['price'] = input('post.price');
        $data['stock'] = input('post.stock');
        
        $file = request()->file('image');
        if($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
            if($info) {
                $fileName = $info->getSaveName();
                $imagePath = '/'.'upload/'. str_replace('\\', '/', $fileName);
                $data1['img_path'] = $imagePath;
            } else {
                echo $file->getError();
            }
        }

        //插入商品表
        $result = Db::name('goods')->insert($data);
        $goods_id = Db::name('goods')->getLastInsID();
        //插入图片表
        $data1['goods_id'] = $goods_id;
        $data1['img_type'] = 0;
        date_default_timezone_set('PRC');
        $data1['img_time'] = time();
        $result2 = Db::name('images')->insert($data1);
        if ($result2) {
            $this->success('添加成功','goodsAdd');
        }
    }
    //校验商品是否存在
    public function checkGoods()
    {
        $goodsname = input('post.goodsname');
        $data = Db::name('goods')->where(array('goods_name'=>$goodsname))->select();
        if($data) {
            echo json_encode(["state"=>1]);
        } else {
            echo json_encode(["state"=>0]);
        }
    }

    //商品批量添加页面
    public function goodsAllAdd()
    {
        return $this->fetch('product_all_add');
    }
    //商品批量添加
    public function doAddAll()
    {
        header("Content-Type:text/html;charset=utf-8");
        $file = request()->file('csv');
        if($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'upload');
            if($info) {
                $fileName = $info->getSaveName();
                $filePath = 'upload/'. str_replace('\\', '/', $fileName);
            } else {
                echo $file->getError();
            }
        }
        $handle = fopen($filePath, 'r');
        $result = $this->input_csv($handle); //解析csv

        $len_result = count($result);
        if ($len_result == 0) {
            echo '没有数据';
            exit;
        }
        $data_values = '';
        for ($i = 1; $i < $len_result; $i++) { 
            $goods_name = iconv('gb2312', 'utf-8', $result[$i][0]); //中文转码
            $goods_num = $result[$i][1];
            $cate_num =  $result[$i][2];
            $brand_num = $result[$i][3];
            $stock = $result[$i][4];
            $price = $result[$i][5];
            $goods_info = iconv('gb2312', 'utf-8' , $result[$i][6]);
            $data_values .= "('$goods_name','$goods_num','$cate_num','$brand_num','$stock','$price','$goods_info'),";
            //$data_values .= $data_values;
        }
        $data_values = substr($data_values, 0, -1);
        fclose($handle);
        $data = Db::execute("insert into gs_goods (goods_name,goods_num,cate_num,brand_num,stock,price,goods_info) values $data_values");
        if ($data) {
            $this->success('添加成功','goodsList');
        } else {
            echo '导入失败';
        }
    }
    function input_csv($handle)
    {
        $out = array();
        $n = 0;
        while($data = fgetcsv($handle,10000)) {
            $num = count($data);
            for($i = 0; $i < $num; $i++) {
                $out[$n][$i] = $data[$i];
            }
            $n++;
        }
        return $out;
    }    

    //商品图片批量添加
    public function pictureAdd()
    {
        $data = Db::name('goods')->where(array('is_del'=>0))->select();
        $this->assign('data',$data);
        return $this->fetch('picture_add');
    }       
    public function doPictureAdd()
    {
        $img_type = input('post.type');
        $goods_id = input('post.goods');
        $image = request()->file('file');
        foreach ($image as $img) {
            //var_dump($img);
            if($img) {
                $info = $img->move(ROOT_PATH . 'public' . DS . 'upload');
                if($info) {
                    $fileName = $info->getSaveName();
                    // var_dump($fileName);
                    // die;
                    $extension = $info->getExtension();
                    $imagePath = '/'.'upload/'. str_replace('\\', '/', $fileName);
                    $data['img_path'] = $imagePath;
                    if($goods_id) {
                        $midImage = '.'. DS . 'upload'.DS.'zoom'.DS.'mid'. DS . time(). '.' . $extension;
                        $mid = Image::open('.'.$imagePath);
                        $mid->thumb(350,350)->save($midImage);
                        $data['mid'] = substr($midImage, 1);
                        $minImage = './' . 'upload/zoom/min/' . time(). '.' . $extension; 
                        $min = Image::open('.'.$imagePath);
                        $mid->thumb(60,60)->save($minImage);
                        $data['small'] = substr($minImage, 1);
                        $data['goods_id'] = $goods_id;
                    }
                    
                } else {
                    echo $file->getError();
                }
            }
            date_default_timezone_set('PRC');
            $data['img_time'] = time();
            $data['img_type'] = $img_type;

            $res = Db::name('images')->insert($data);
            if ($res) {
                 $this->success('添加成功','pictureAdd');
             } else {
                $this->success('添加失败','pictureAdd');
             }
           
            
            
        }
    }
    

    public function goodsDetail($goods_id)
    {
        $data = Db::name('goods')->where(array('goods_id'=>$goods_id))->find();
        $bra = Db::name('brand')->where(array('bra_num'=>$data['brand_num']))->find();
        $cate = Db::name('category')->where(array('cate_num'=>$data['cate_num']))->find();
        foreach ($bra as $key => $value) {
            $data[$key] = $value;
        }
        foreach ($cate as $key => $value) {
            $data[$key] = $value;
        }

        $brand = Db::name('brand')->select();
        $category = Db::name('category')->select();
        $this->assign([
            'data'=>$data,
            'brand'=>$brand,
            'category'=>$category,
        ]);
        return $this->fetch('product_detail');
    }

    //商品回收站
    public function recycleBin()
    {
        //$result = Db::query("select * from gs_goods left join gs_images on gs_goods.goods_id = gs_images.goods_id where gs_goods.is_del=1");
        $result = Db::name('goods')->where(array('is_del'=>1))->select();
        //获取商品数量
        $count = count($result);
        if ($count) {
            //当前页码数
            $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
            //每页现实的数据条数
            $num = 2;
            //总页数
            $total = ceil($count / $num);
            if($page <= 1) {
                $page = 1;
            }
            if ($page >= $total) {
                $page = $total;
            }
            $offest = ($page - 1) * $num;
            //$data = Db::query("select * from gs_goods left join gs_images on gs_goods.goods_id = gs_images.goods_id where gs_goods.is_del=1 limit $offest,$num");
            $data = Db::name('goods')->where(array('is_del'=>1))->limit($offest,$num)->select();
            foreach ($data as $key => $value) {
                $res = Db::name('images')->where(array('goods_id'=>$value['goods_id']))->find();
                $data[$key]['img_path'] = $res['img_path'];
            }
            
            $this->assign('count',$count);
            $this->assign('num',$num);
            $this->assign('page',$page);
            $this->assign('data',$data);
        } else {
            $count = 0;
            $num = 0;
            $page = 0;
            $this->assign('count',$count);
            $this->assign('num',$num);
            $this->assign('page',$page);            
        }
        return $this->fetch('recycle_bin');
    }
    //商品回收站删除  完全删除
    public function del()
    {
        $goods_id = input('get.goods_id');
        Db::name('goods')->delete($goods_id);
        $this->redirect('/admin/goods/recycleBin');
    }
    //回收站商品恢复
    public function recover()
    {
        $goods_id = input('get.goods_id');
        $data['is_del'] = 0;
        Db::name('goods')->where('goods_id',$goods_id)->update($data);
        $this->redirect('/admin/goods/recycleBin');
    }

    //文件下载
    public function download()
    {
        echo '哈哈';
        die;
        $fileName = "/uploads/goods_list.csv";
        header("Content-Type:application/octet-stream");
        header('Content-Disposition:attachment;  filename ='.$fileName);
        header('Content-Transfer-Encodiong:binary');
        header('Content-Length:'.filesize($fileName));
        readfile($fileName);
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
