<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/30
 * Time: 11:32
 */

namespace Business\Controller;


class GoodsController extends AdminController
{
    //商品列表页
    public function index(){
        $nickname       =   trim(I('nickname'));
        if($nickname != null){
            $map['goods_name|goods_sn'] = array('like', '%' . (string)$nickname . '%');
            //$map['status']  = array(array('neq',-1),array('neq',-10),'and');
            $map['_logic'] = 'AND';
        }
        $this->assign('status',-1);
        if(I('status') != null){
            if(I('status') != -1){
                if(I('status') == 40){
                    $map['goods_status'] = array(array('eq',40),'and');
                }else{
                    $map['is_onsale'] = array(array('eq',I('status')),'and');
                    $map['goods_status'] = array(array('neq',10),array('neq',0),array('neq',40),'and');
                }

                $this->assign('status',I('status'));
            }else{
                $this->assign('status',-1);
                $map['goods_status'] = array(array('neq',10),array('neq',0),'and');
            }
        }else{
            $map['goods_status'] = array('in','1,40');
        }
        $map['store_id'] = $this->get_store_id();
        $goods_info   = $this->lists('b_goods_info',$map);
//        echo M('b_goods_info')->getLastSql();exit;
        foreach ($goods_info as $k=>$v){
            $brand = $this->getBrandName($v['brand_id']);
            $goods_info[$k]['add_date'] = $v['add_date']?date('Y/m/d H:m:s',$v['add_date']):'';
            $goods_info[$k]['brand_name'] =$brand['brand_name'] ;
            $goods_info[$k]['category_name'] = getCategoryName($v['category_id']);
            $goods_info[$k]['main_img']      = $this->getImg($v['goods_id'],'m');
        }
//        echo '<pre>';print_r($goods_info);exit;
        $this->assign('_list', $goods_info);
        $this->assign('act','index');

        $this->meta_title = '商品列表';
        $this->display('index');
    }
    //添加商品
    public function add(){
//        echo '<pre>';print_r(getCategory());exit;

        $goods = D('Goods');
        if(IS_POST){
            $res = $goods->update();
            if($res !== false){
                $goods_id = $goods->getLastInsID();
                $goods_img = explode(',',I('goods_img'));
                if($goods_id){
                    $main_logo = array('goods_id'=>$goods_id,'sort'=>1,'pic_url'=>I('logo'),'type'=>'m');
                    $result = M('b_goods_picture')->add($main_logo);
                    foreach($goods_img as $v){
                        $goods_logo = array('goods_id'=>$goods_id,'sort'=>1,'pic_url'=>$v,'type'=>'f');
                        $resu = M('b_goods_picture')->add($goods_logo);
                    }
                }
                if($result && $resu){
                    $this->success('新增成功！',U('Index'));
                }else{
                    $this->error('新增失败！',U('Index'));
                }

            }else{
                $error = $goods->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $this->assign('_goods_brand'  ,get_goods_brand());
            $this->assign('type', array('1'=>'国内商品','2'=>'一般贸易商品','3'=>'保税','4'=>'直邮','9'=>'跨境商品'));
//            $this->assign('_cross', array('3'=>'保税','4'=>'直邮'));
            $this->assign('is_new', array('1'=>'新品'));
            $this->assign('_goods_type'  ,getCategory());
            $this->meta_title = '新增商品';
            $this->display('add');
        }

    }
    //审核商品
    public function check(){
        $nickname       =   I('nickname');
        if($nickname != null){
            $map['goods_name|goods_sn'] = array('like', '%' . (string)$nickname . '%');
            //$map['status']  = array(array('neq',-1),array('neq',-10),'and');
            $map['_logic'] = 'AND';
        }
        if(I('status') != null){
            $map['putaway'] = array(array('eq',I('status')),'and');
            $this->assign('status',I('status'));
        }
        $map['goods_status'] = array(array('eq',0),'and');
        $goods_info   = $this->lists('b_goods_info',$map);
        foreach ($goods_info as $k=>$v){
            $brand = $this->getBrandName($v['brand_id']);
            $goods_info[$k]['add_date'] = date('Y/m/d H:m:s',$v['add_date']);
            $goods_info[$k]['brand_name'] =$brand['brand_name'] ;
            $goods_info[$k]['category_name'] = getCategoryName($v['category']);
        }
        $this->assign('act','check');
        $this->assign('_list', $goods_info);
        $this->meta_title = '审核商品列表';
        $this->display('index');
    }

    //查看商品
    public function look(){
        $id = I("id");
        $goods_info =  M("b_goods_info")->where('goods_id ='.$id)->find();
        $goods_info['main_img'] = $this->getImg($goods_info['goods_id'],'m');

        $goods_info['goods_img_arr'] = $this->getImg($goods_info['goods_id'],'f');
        $this->assign('_goods_info',$goods_info);
        $this->assign('act','look');
        $this->meta_title = '商品详情';
        $this->display();
    }

    //查看回收站商品详情
    public function recycle_look(){
        $id = I("id");
        $goods_info =  M("b_goods_info")->where('goods_id ='.$id)->find();
        $goods_info['main_img'] = $this->getImg($goods_info['goods_id'],'m');
        $goods_info['goods_img_arr'] = $this->getImg($goods_info['goods_id'],'f');
        $this->assign('_goods_info',$goods_info);
        $this->assign('act','recycle_look');
        $this->meta_title = '商品回收站详情';
        $this->display('look');
    }
    //编辑商品
    public function edit(){
        $id = I('id') ? I('id') : '';
        $goods = D('Goods');
        if(IS_POST){ //提交表单
            $res = $goods->update();
            if($res !== false){
                $pic_id = I('pic_id');
                $pic_info = M('b_goods_picture')->where('id='.$pic_id)->find();
                $main_logo = array('goods_id'=>I('goods_id'),'pic_url'=>I('logo'),'type'=>'m');
                if($pic_info){
                    $result = M('b_goods_picture')->where('id='.$pic_id)->save($main_logo);
                }else{
                    $result = M('b_goods_picture')->add($main_logo);
                }
                $img_id[] = I('img_id');

                $goods_img = explode(',',I('goods_img'));
                if($img_id){
                    foreach($img_id as $val){
                        $arr = M('b_goods_picture')->where('id='.$val)->find();
                        foreach($goods_img as $vs){
                            if($arr){
                                $goods_logo = array('goods_id'=>I('goods_id'),'sort'=>1,'pic_url'=>$vs,'type'=>'f');
                                $resu = M('b_goods_picture')->where('id='.$val)->save($goods_logo);
                            }else{
                                $goods_logo = array('goods_id'=>I('goods_id'),'sort'=>1,'pic_url'=>$vs,'type'=>'f');
                                $resu = M('b_goods_picture')->add($goods_logo);
                            }
                        }
                    }
                }else{
                    foreach($goods_img as $v){
                        $goods_logo = array('goods_id'=>I('goods_id'),'sort'=>1,'pic_url'=>$v,'type'=>'f');
                        $resu = M('b_goods_picture')->add($goods_logo);
                    }
                }

                if($result || $resu){
                    $this->success('编辑成功！',U('Index'));
                }else{
                    $this->error('编辑失败！',U('Index'));
                }
            }else{
                $error = $goods->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
//            $list = get_goods_brand();
//            echo '<pre>';print_r($list);exit;
            $row = M('b_goods_info')->where(array('goods_id'=>$id))->find();
            $row['goods_img_arr'] = explode(',',$row['goods_img']);
            $this->assign('row',$row);
            $this->assign('main_pic',$this->get_img($id,'m'));
            $this->assign('goods_img',$this->get_img($id,'f'));
            $this->assign('_goods_brand'  ,get_goods_brand());
            $this->assign('_goods_type'  ,getCategory());
            $this->assign('type', array('1'=>'国内商品','2'=>'一般贸易商品','3'=>'保税','4'=>'直邮','9'=>'跨境商品'));
//            $this->assign('_cross', array('3'=>'保税','4'=>'直邮'));
//            $this->assign('_putaway', array('1'=>'立即上架','0'=>'暂不上架'));
            $this->assign('_help_sales', array('1'=>'是','0'=>'否'));
            $this->meta_title = '编辑品牌';
            $this->display('add');
        }
    }


    /*
     * 删除OSS图片接口
     */
    public function removePic(){
        $path = I('path')?I('path'):'';
        $path = ltrim($path,'/');
        $id = I('id')?I('id'):0;
        $condition['id'] = $id;
//        $data['pic_url'] = '';
        if(!oss_delet_object(array($path))){
            if($id){
                M("b_goods_picture")->where($condition)->delete();
                action_log('delete_images','删除OSS图片'.$path,$id,$id);
                echo 0;exit;
            }
            echo 0;exit;
        }else{
            echo 1;exit;
        };
    }

    public function recycle(){
        $nickname       =   I('nickname');
        if($nickname != null){
            $map['goods_name|goods_sn'] = array('like', '%' . (string)$nickname . '%');
            //$map['status']  = array(array('neq',-1),array('neq',-10),'and');
            $map['_logic'] = 'AND';
        }
        if(I('status') != null){
            $map['putaway'] = array(array('eq',I('status')),'and');
            $this->assign('status',I('status'));
        }
        $map['goods_status'] = array(array('eq',10),'and');
        $map['store_id'] = $this->get_store_id();
        $goods_info   = $this->lists('b_goods_info',$map);
        foreach ($goods_info as $k=>$v){
            $brand = $this->getBrandName($v['brand_id']);
            $goods_info[$k]['add_date'] = $v['add_date']?date('Y/m/d H:m:s',$v['add_date']):'';
            $goods_info[$k]['brand_name'] =$brand['brand_name'] ;
            $goods_info[$k]['category_name'] = getCategoryName($v['category_id']);
            $goods_info[$k]['main_img']      = $this->getImg($v['goods_id'],'m');
        }
        $this->assign('_list', $goods_info);
        $this->assign('act','recycle');
        $this->meta_title = '商品回收站列表';
        $this->display('index');
    }

    //删除商品
    public function remove(){
        $goods_id_arr = I('id')?I('id'):'';
        if(is_array($goods_id_arr)){
            $str = implode(',',$goods_id_arr);
            $map['goods_id']  = array('in',$str);
        }else{
            $map['goods_id']  = $goods_id_arr;
        }
        $status = M('b_goods_info')->field('goods_status')->where($map)->select();
        foreach($status as $k=>$v){
            $arr[] = $v['goods_status'];
        }
        if(in_array('40', $arr)){
            $this->error('有平台冻结的商品不能删除',U('index'));
        }else{
            $data['goods_status'] = 10;
            $res = M('b_goods_info')->where($map)->save($data);
            if($res){
                $this->success('删除成功',U('index'));
            }
        }
    }

    //回收站删除商品
    public function recovery_remove(){
        $goods_id_arr = I('id')?I('id'):'';
        if(is_array($goods_id_arr)){
            $str = implode(',',$goods_id_arr);
            $map['goods_id']  = array('in',$str);
        }else{
            $map['goods_id']  = $goods_id_arr;
        }
        $data['goods_status'] = 20;
        $res = M('b_goods_info')->where($map)->save($data);
        if($res){
            $this->success('删除成功',U('recycle'));
        }
    }

    //商品上架
    function on_sale(){
        $goods_id_arr = I('id')?I('id'):'';
        if(is_array($goods_id_arr)){
            $str = implode(',',$goods_id_arr);
            $map['goods_id']  = array('in',$str);
        }else{
            $map['goods_id']  = $goods_id_arr;
        }
        $status = M('b_goods_info')->field('goods_status')->where($map)->select();
        foreach($status as $k=>$v){
            $arr[] = $v['goods_status'];
        }
        if(in_array('40', $arr)){
            $this->error('有平台冻结的商品不能上架',U('index'));
        }else{
            $data['is_onsale'] = 1;
            $res = M('b_goods_info')->where($map)->save($data);
            if($res){
                $this->success('上架成功',U('index'));
            }
        }
    }

    //商品下架
    function off_sale(){
        $goods_id_arr = I('id')?I('id'):'';
        if(is_array($goods_id_arr)){
            $str = implode(',',$goods_id_arr);
            $map['goods_id']  = array('in',$str);
        }else{
            $map['goods_id']  = $goods_id_arr;
        }
        $status = M('b_goods_info')->field('goods_status')->where($map)->select();
        foreach($status as $k=>$v){
            $arr[] = $v['goods_status'];
        }
        if(in_array('40', $arr)){
            $this->error('有平台冻结的商品不能下架',U('index'));
        }else{
            $data['is_onsale'] = 0;
            $res = M('b_goods_info')->where($map)->save($data);
            if($res){
                $this->success('下架成功',U('index'));
            }
        }
    }

    //商品恢复
    public function recovery(){
        $goods_id_arr = I('id')?I('id'):'';
        if(is_array($goods_id_arr)){
            $str = implode(',',$goods_id_arr);
            $map['goods_id']  = array('in',$str);
        }else{
            $map['goods_id']  = $goods_id_arr;
        }
        $data['goods_status'] = 1;
        $res = M('b_goods_info')->where($map)->save($data);
        if($res){
            $this->success('恢复成功',U('index'));
        }
    }
    public function getBrandName($brand_id = 0){
        $conditon['id']= $brand_id;
        $res = M("b_brand_info")->where($conditon)->getField('id,brand_name,logo');
        return $res[$brand_id];
    }

    public function getImg($goods_id = 0,$type = ''){
        $condition['type'] = $type;
        $condition['goods_id'] = $goods_id;
        if($type == 'm'){
            $res = M('b_goods_picture')->where($condition)->find();
            $imge_url = $this->get_image_path($res['pic_url']);
            return $imge_url;
        }else{
            $arr = array();
            $res = M('b_goods_picture')->where($condition)->select();
            if($res){
                foreach ($res as $k => $v){

                    $arr[] = $this->get_image_path($v['pic_url']);
                }
            }
            return $arr;
        }

    }


    public function get_img($goods_id = 0,$type = ''){
        $condition['type'] = $type;
        $condition['goods_id'] = $goods_id;
        if($type == 'm'){
            $res = M('b_goods_picture')->where($condition)->find();
            $imge_url = $this->get_image_path($res['pic_url']);
            return $imge_url;
        }else{
            $arr = array();
            $res = M('b_goods_picture')->where($condition)->select();
            if($res){
                foreach ($res as $k => $v){
                    $arr[] = $this->get_image_path($v['pic_url']);
                }
            }
            return $arr;
        }

    }

    /**
     * 重新获得品牌图片相册的地址
     */
    public function get_image_path($image = '')
    {
        if (strpos($image, "http") === false) {
            $image = 'http://www.pgjk.com/' . $image;
        }
        $url = empty($image) ? 'http://www.pgjk.com/no_picture.gif' : $image;
        return $url;
    }

    public function get_store_id(){
        $map['mi.uid'] = UID;
        $store_id = M('b_member_info')->alias('mi')->field('si.store_id')
            ->join('left join b_store_info as si on mi.b_id = si.b_id')
            ->where($map)->find();
        return $store_id['store_id'];
    }



}