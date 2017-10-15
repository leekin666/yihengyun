<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/8/30
 * Time: 11:32
 */

namespace Admin\Controller;


class GoodsysController extends AdminController
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
        }
//        $map['goods_status'] = 1;
        $goods_info   = $this->lists('b_goods_info',$map);
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

    //查看商品
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
        $map['goods_status'] = array(array('eq',20),'and');
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
    public function recycle_remove(){
        $goods_id_arr = I('id')?I('id'):'';
        if(is_array($goods_id_arr)){
            $str = implode(',',$goods_id_arr);
            $map['goods_id']  = array('in',$str);
        }else{
            $map['goods_id']  = $goods_id_arr;
        }
        $data['goods_status'] = 30;
        $res = M('b_goods_info')->where($map)->save($data);
        if($res){
            $this->success('删除成功',U('recycle'));
        }
    }

    //商品解冻
    function on_sale(){
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
            $this->success('解冻成功',U('index'));
        }
    }

    //商品冻结
    function off_sale(){
        $goods_id_arr = I('id')?I('id'):'';
        if(is_array($goods_id_arr)){
            $str = implode(',',$goods_id_arr);
            $map['goods_id']  = array('in',$str);
        }else{
            $map['goods_id']  = $goods_id_arr;
        }
        $data['goods_status'] = 40;
        $res = M('b_goods_info')->where($map)->save($data);
        if($res){
            $this->success('冻结成功',U('index'));
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
            return $res;
        }else{
            $arr = array();
            $res = M('b_goods_picture')->where($condition)->select();
            if($res){
                foreach ($res as $k => $v){
                    $arr[] = $v;
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





}