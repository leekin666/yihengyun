<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/12
 * Time: 10:52
 */

namespace Admin\Controller;


class SqlController extends AdminController
{
    private $_key = 'yijiasancan_bbc';
    private  $_secret = '01348ebfeec40bbb8ebcf38c99adf838';
    public function addgoods(){
        header("Content-type: text/html; charset=utf-8");
        $arr = json_decode($this->get_goods_list(),true);
        for ($i = 1;$i<ceil($arr['count'])/100;$i++){
            $arrs = json_decode($this->get_goods_list($i,100),true);
            foreach ($arrs['data'] as $k=>$v){
                if($v['user_id'] == 63){
                    $data['is_cross'] =3;
                }elseif($v['user_id'] == 64){
                    $data['is_cross'] =4;
                }elseif($v['user_id'] == 75){
                    $data['is_cross'] =1;
                }
                $store_id = $this->get_shop_name($v['store_name']);
                $data['store_id'] = $store_id;
                $data['is_hot']   = 1;
                $data['is_new']   = 1;
                $data['goods_name']   = $v['goods_name'];
                $data['brand_id']     = $this->get_brand_name($v['brand_name'],$store_id);
                $data['category_id']       = $this->get_cat_name($v['catName']);
                $data['details']      = htmlspecialchars($v['goods_desc']);
                $data['price']        = $v['price'];
                $data['market_price'] = $v['market_price'];
                $data['weight']       = $v['goods_weight'];
                $data['num']          = $v['goods_number'];
                $data['is_real']      = 1;
                $data['is_onsale']    = 1;
                $data['add_date']     = $v['add_time'];
                $data['goods_status'] = 1;
                $data['goods_sn']     = $v['goods_sn'];
                $conditon['goods_sn'] = $v['goods_sn'];
                $count = M('b_goods_info')->where($conditon)->find();
                if($count){
                    $data['category_id']  = $count['category_id'];
                    M('b_goods_info')->where($conditon)->save($data);
                    $map['goods_id']   = $count['goods_id'];
                    if(M("b_goods_picture")->where($map)->find()){
                        $main_picture['pic_url']  = $v['original_img'];
                       // M('b_goods_picture')->where($map)->save($main_picture);
                        foreach($v['goods_gallery'] as $ks => $vs){
                            $goods_picture['sort']     = $vs['img_desc'];
                            $goods_picture['pic_url']  = $vs['img_original'];
                            $goods_picture['type']     = 'f';
                          //  M('b_goods_picture')->where($map)->save($goods_picture);
                        }
                    }else{
                        $main_picture['goods_id'] = $count['goods_id'];
                        $main_picture['pic_url']  = $v['original_img'];
                        $main_picture['type']  = 'm';
                        M('b_goods_picture')->add($main_picture);
                        foreach($v['goods_gallery'] as $ks => $vs){
                            $goods_picture['goods_id'] = $count['goods_id'];
                            $goods_picture['sort']     = $vs['img_desc'];
                            $goods_picture['pic_url']  = $vs['img_original'];
                            $goods_picture['type']     = 'f';
                            M('b_goods_picture')->add($goods_picture);
                        }
                    }
                    echo "更新商品成功".$v['goods_sn']."</br>";
                }else{
                    M('b_goods_info')->add($data);
                    $goods_id = M('b_goods_info')->getLastInsID();
                    $main_picture['goods_id'] = $goods_id;
                    $main_picture['pic_url']  = $v['original_img'];
                    $main_picture['type']  = 'm';
                    M('b_goods_picture')->add($main_picture);
                    foreach($v['goods_gallery'] as $ks => $vs){
                        $goods_picture['goods_id'] = $goods_id;
                        $goods_picture['sort']     = $vs['img_desc'];
                        $goods_picture['pic_url']  = $vs['img_original'];
                        $goods_picture['type']     = 'f';
                        M('b_goods_picture')->add($goods_picture);
                    }
                    echo "添加商品成功".$v['goods_sn']."</br>";
                }
            }

        }
        }
    public function index(){
        $this->display();
    }

    public function get_token(){
        $url = 'http://www.pgjk.com/api/buyersinfo/buyersinfo.php?act=token&key='.$this->_key.'&secret='.$this->_secret;
        $param['key'] = $this->_key;
        $param['secret'] = $this->_secret;
        $response = http($url,$param);
        $arr = json_decode($response,true);
        $token = $arr['token'];
        return $token;
    }

    public function get_goods_list($page = 1,$page_size = 100){
        $url =  'http://www.pgjk.com/api/buyersinfo/buyersinfo.php?act=goodslist&key='.$this->_key.'&token='.$this->get_token().'&page='.$page.'&page_size='.$page_size;
        //        $param['key'] = $this->_key;
//        $param['token'] = $this->get_token();
        $response = http($url);
        return $response;
    }

    public function get_brand_name($brand_name = '',$store_id = 0){
        $condition['brand_name'] = "$brand_name";
        $condition['store_id']   = $store_id;
        $brand_id =M("b_brand_info")->where($condition)->getField('id');
        return $brand_id;
    }
    public function get_cat_name($catName = ''){
        $condition['name'] = $catName;
        $category_id =M("m_category_info")->where($condition)->getField();
        return $category_id;
    }

    public function get_shop_name($store_name = ''){
        $map['name'] = $store_name;
        $store_id = M("b_store_info")->where($map)->getField();
        return $store_id;
    }
}