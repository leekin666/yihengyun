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
use Common\Model\CountryModel;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class StoreController extends HomeController {

    //品牌首页
    public function index(){
        header("Content-type:text/html;charset=utf-8");
        $id = I('id')?I('id'):'';
        $brand_id = I('brand_id') ? I('brand_id') : 0;
        $price_min = I('price_min') ? I('price_min') : 0;
        $price_max = I('price_max') ? I('price_max') : 0;
        $p = I('p') ? I('p') : 1;
        $sort = I('sort') ? I('sort') : 'goods_id';
        $order = I('order') ? I('order') : 'ASC';
        $style = I('style') ? I('style') : 'g';//默认大图

        if($price_min || $price_max) $map['_string'] = "gi.price >= ".$price_min." and gi.price <= ".$price_max;
        if($brand_id){
            $map['gi.brand_id'] = array('in',$this->getBrandBusiness($brand_id));
        }
//        echo '<pre>';print_r($map['gi.brand_id']);exit;
        $map['si.store_id'] = $id;
        $map['gi.goods_status'] = array('eq',1);
//        $map['gi.is_onsale'] = array('eq',1);


        $brand_list = $this->getBrandListByStore($id);

        $store_info = M('b_store_info')->where('store_id='.$id)->find();
        $sql = M()->table('b_store_info AS si')
            ->field("gi.*,si.name as store_name")
            ->join("b_goods_info AS gi ON si.store_id = gi.store_id",'left')
//            ->join("b_brand_info AS bi ON bi.store_id = si.store_id",'left')
            ->where($map)
            ->order($sort." ".$order)
            ->buildSql();
//        print_r($sql);exit;
        $store_list = $this->page_list('b_store_info',$sql);
        foreach($store_list as $key=>$val){
            $store_list[$key]['goods_img'] = $this->getGoodsImg($val['goods_id']);
        }





        $this->assign('brand_name',$this->getBrandName($brand_id));
        $this->assign('store_info',$store_info);
        $this->assign('store_list',$store_list);
        $this->assign('brand_list',$brand_list);
        $this->assign('id',$id);
        $this->assign('brand_id',$brand_id);
        $this->assign('price_min',$price_min);
        $this->assign('price_max',$price_max);
        $this->assign('p',$p);
        $this->assign('sort',$sort);
        $this->assign('order',$order);
        $this->assign('style',$style);
        $this->display();
    }
}