<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Mall\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 */
class CategoryController extends HomeController {

    /**
     * 商品列表
     */
    public function index(){
        $id = I('id') ? I('id') : 0;
        $cross = I('is_cross') ? I('is_cross') : 0;
        $brand_id = I('brand_id') ? I('brand_id') : 0;
        $price_min = I('price_min') ? I('price_min') : 0;
        $price_max = I('price_max') ? I('price_max') : 0;
        $p = I('p') ? I('p') : 1;
        $sort = I('sort') ? I('sort') : 'id';
        $order = I('order') ? I('order') : 'ASC';
        $style = I('style') ? I('style') : 'g';//默认大图
        if($id){
            $category = D('CategoryMall');
            $map['gi.category_id'] = array('in',$category->getChild($id));
            $brand_list = $this->getBrandSelf($id);
        }else{
            if($cross){
                $is_cross = explode(',', $cross);
                if($is_cross){
                    $con['is_cross'] = array('in',$is_cross);;
                }
                $con['goods_status'] = 1;
                $con['brand_id'] = array('neq','');;
                $goods_arr = M('b_goods_info')->field('brand_id')->where($con)->select();
                foreach($goods_arr as $ks=>$vs){
                    $arrs[] = $vs['brand_id'];
                }
                $where['bid'] = array('in',$arrs);
                $brand_list = M()->table('s_brand_link AS s')
                    ->field("s.id,s.bid,m.*")
                    ->join("m_brand AS m ON s.brand_id = m.brand_id",'left')
                    ->where($where)
                    ->group('m.brand_id')
                    ->select();
                foreach($brand_list as $key=>$val){
                    $brand_list[$key]['logo'] = $this->get_image_path($val['logo']);
                }
//                echo '<pre>';print_r($brand_list);exit;
            }else{
                $brand_list = $this->getAllBrandSelf();
            }
        }
        if($brand_id){
            $map['gi.brand_id'] = array('in',$this->getBrandBusiness($brand_id));
        }
        if($price_min || $price_max) $map['_string'] = "gi.price >= ".$price_min." and gi.price <= ".$price_max;
        if($cross){
            $is_cross = explode(',', $cross);
            if($is_cross){
                $map['gi.is_cross'] = array('in',$is_cross);;
            }
        }
        $map['gi.goods_status'] = array('eq',1);
        $map['gi.is_real'] = array('eq',1);//实体商品
        $sql = M()->table('b_goods_info AS gi')
            ->field("gi.*,bi.brand_name,si.name as store_name")
            ->join("b_brand_info AS bi ON bi.id = gi.brand_id",'left')
            ->join("b_store_info AS si ON si.store_id = gi.store_id",'left')
            ->where($map)
            ->order($sort." ".$order)
            ->buildSql();
        $list = $this->page_list('b_goods_info',$sql);

        foreach($list as $key=>$val){
            $list[$key]['goods_img'] = $this->getGoodsImg($val['goods_id']);
        }

        //类目列表
        $category = D('CategoryMall');
        $goods_detail['category_name'] = $category->getLastCategory($id);
        $this->assign('_goods_list',$category->getSameLevel($id));
        $this->assign('_goodsdetail',$goods_detail);
        $this->assign('brand_list',$brand_list);
        $this->assign('brandName',$this->getBrandName($brand_id));
        $this->assign('_lists',$list);
        $this->assign('id',$id);
        $this->assign('is_cross',$cross);
        $this->assign('brand_id',$brand_id);
        $this->assign('price_min',$price_min);
        $this->assign('price_max',$price_max);
        $this->assign('p',$p);
        $this->assign('sort',$sort);
        $this->assign('order',$order);
        $this->assign('style',$style);
        $this->display();
    }

    /**
     * 分类列表
     */
    public function categoryList(){
        $category_list = $this->get_category_list();
        $this->assign('goods_list',$category_list);
        $this->display();
    }

}