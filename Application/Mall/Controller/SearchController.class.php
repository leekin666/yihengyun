<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Mall\Controller;

/**
 * 前台首页控制器
 */
class SearchController extends HomeController {

    /**
     * 搜索商品列表
     */
    public function goods(){
        $keyword = I('keywords') ? trim(I('keywords')) : '';
        $store_search_cmt = I('store_search_cmt') ? I('store_search_cmt') : 0;
        $search_type = I('search_type') ? I('search_type') : '';
        $brand_id = I('brand_id') ? I('brand_id') : 0;
        $price_min = I('price_min') ? I('price_min') : 0;
        $price_max = I('price_max') ? I('price_max') : 0;
        $p = I('p') ? I('p') : 1;
        $sort = I('sort') ? I('sort') : 'id';
        $order = I('order') ? I('order') : 'ASC';
        $style = I('style') ? I('style') : 'g';//默认大图

        if($brand_id) $map['gi.brand_id'] = array('in',$brand_id);
        if($price_min || $price_max) $map['_string'] = "gi.price >= ".$price_min." and gi.price <= ".$price_max;
        if($keyword) $map['gi.goods_name'] = array('like',"%".$keyword."%");
        $map['gi.is_onsale'] = array('eq',1);
        $map['gi.goods_status'] = array('eq',1);
        $sql = M()->table('b_goods_info AS gi')
            ->field("gi.*,bi.brand_name,si.name as store_name")
            ->join("b_brand_info AS bi ON bi.id = gi.brand_id",'left')
            ->join("b_store_info AS si ON si.store_id = gi.store_id",'left')
            ->where($map)
            ->order($sort." ".$order)
            ->buildSql();
        $list = $this->page_list('b_goods_info',$sql);
        $brand_list = $this->getAllBrandList();
        foreach($list as $key=>$val){
            $list[$key]['goods_img'] = $this->getGoodsImg($val['goods_id']);
        }
        //商业版品牌显示列表
        $this->assign('brand_list',$brand_list);
        $this->assign('brandName',$this->getBrandName($brand_id));
        $this->assign('_lists',$list);
        $this->assign('brand_id',$brand_id);
        $this->assign('price_min',$price_min);
        $this->assign('price_max',$price_max);
        $this->assign('p',$p);
        $this->assign('sort',$sort);
        $this->assign('order',$order);
        $this->assign('style',$style);
        $this->assign('keyword',$keyword);
        $this->assign('store_search_cmt',$store_search_cmt);
        $this->assign('search_type',$search_type);
        $this->display();
    }
    /**
     * 搜索店铺列表
     */
    public function store(){
        $keyword = I('keywords') ? trim(I('keywords')) : '';
        if($keyword) $map['_string'] = " name like '%".$keyword."%' OR title like '%".$keyword."%' OR key_words like '%".$keyword."%' ";
        $list = $this->lists('b_store_info',$map);
        foreach($list as $key=>$val){
            $list[$key]['goods'] = $this->getStoreGoods($val['store_id']);
            $list[$key]['brand'] = $this->getStoreBrand($val['store_id']);
        }
        $this->assign('list',$list);
        $this->assign('count',count($list));
        $this->assign('keyword',$keyword);
        $this->display();
    }
    /**
     * 搜索渠道列表
     */
    public function channel(){
        $keyword = I('keywords') ? trim(I('keywords')) : '';
        if($keyword) $map['_string'] = " channel_name like '%".$keyword."%' OR desc like '%".$keyword."%' ";
        $list = $this->lists('m_channel_info',$map);
        $this->assign('_list',$list);
        $this->assign('count',count($list));
        $this->assign('keyword',$keyword);
        $this->display();
    }

    /**
     * 搜索预览,自动提示
     */
    public function lookup(){
        $keyword = I('keyword') ? trim(I('keyword')) : '';
        $search_type = I('search_type') ? trim(I('search_type')) : '';
        if($search_type == 'goods'){
            //搜索栏目和商品
            $map['name'] = array('like',"%".$keyword."%");
            $map['pid'] = array('neq',0);
            $map['status'] = array('eq',1);
            $list_cat = M('m_category_info')->where($map)->limit(6)->select();
            if($list_cat){
                $cat = array();
                $category = D('CategoryMall');
                foreach($list_cat as $k=>$v){
                    $cat[$k] = $category->getLastCategory($v['id']);
                    $cat[$k]['cat_id'] = $v['id'];
                }
            }
            $where['goods_name'] = array('like',"%".$keyword."%");
            $where['goods_status'] = array('eq',1);
            $list_goods = M('b_goods_info')->field('goods_id,brand_id,goods_name')->where($where)->limit(10)->select();
            foreach($list_goods as $k=>$v){
                $list_goods[$k]['goods_name_style'] = str_replace($keyword,"<font style='font-weight:normal;color:#ec5151;'>".$keyword."</font>",$v['goods_name']);
            }
            if($cat || $list_goods) $is_show = 1;
            $this->assign('cat',$cat);
            $this->assign('list_goods',$list_goods);
        }elseif($search_type == 'store'){
            //搜索店铺
            $map['_string'] = " name like '%".$keyword."%' OR title like '%".$keyword."%' OR key_words like '%".$keyword."%' ";
            $list_shop = M('b_store_info')->where($map)->select();
            foreach($list_shop as $k=>$v){
                $list_shop[$k]['name_style'] = str_replace($keyword,"<font style='font-weight:normal;color:#ec5151;'>".$keyword."</font>",$v['name']);
            }
            if($list_shop) $is_show = 1;
            $this->assign('list_shop',$list_shop);
        }
        $this->assign('is_show',$is_show);
        $this->assign('search_type',$search_type);
        echo $this->fetch();
    }
    /**
     * 获取店铺下的商品
     */
    public function getStoreGoods($store_id = 0,$num = 4){
        if(!$store_id){
            return false;
        }
        $map['store_id'] = array('eq',$store_id);
        $map['goods_status'] = array('eq',1);
        $list_goods = M('b_goods_info')->field('goods_id,goods_name,price')->where($map)->limit($num)->select();
        foreach($list_goods as $k=>$v){
            $list_goods[$k]['goods_img'] = $this->getGoodsImg($v['goods_id']);
        }
        return $list_goods;
    }
    /**
     * 获取店铺下的主营类目
     */
    public function getStoreBrand($store_id = 0,$num = 4){
        if(!$store_id){
            return false;
        }
        $map['store_id'] = array('eq',$store_id);
        $map['status'] = array('eq',1);
        $list = M()->query("SELECT COUNT(brand_id) AS count,brand_id FROM `b_goods_info` WHERE ( `store_id` = ".$store_id." ) and brand_id is not null GROUP BY brand_id order by count DESC limit ".$num);
        if($list){
            $brand_name = array();
            foreach($list as $k=>$v){
                $brand_name[] = $this->getBrandName($v['brand_id']);
            }
        }
        return implode(' | ',$brand_name);
    }

}