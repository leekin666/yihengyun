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
use Mall\Model;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class CountryController extends HomeController {

	//系统首页
    public function index(){
        $this->assign('country_list',$this->get_country_list());
        $this->display('index');
    }
    //国家详情
    public function detail(){
        $country_id = I('country_id')?I('country_id'):0;
        if(I('brand_id')){
            $brand_id = I('brand_id') ? I('brand_id') : 0;
        }
        $price_min = I('price_min') ? I('price_min') : 0;
        $price_max = I('price_max') ? I('price_max') : 0;
        $p = I('p') ? I('p') : 1;
        $sort = I('sort') ? I('sort') : 'brand_id';
        $order = I('order') ? I('order') : 'ASC';
        $style = I('style') ? I('style') : 'g';//默认大图
//        if(!$id){
//            $this->redirect('categoryList');
//        }
        if($brand_id) $map['gi.brand_id'] = array('in',$this->getBrandBusiness($brand_id));
        if($price_min || $price_max) $map['_string'] = "gi.price >= ".$price_min." and gi.price <= ".$price_max;
        $map['bi.country']  = $country_id;
        $map['gi.goods_status'] = array('eq',1);
        $sql = M()->table('b_goods_info AS gi')
            ->field("gi.*,bi.brand_name,si.name as store_name")
            ->join("b_brand_info AS bi ON bi.id = gi.brand_id",'left')
            ->join("b_store_info AS si ON si.store_id = gi.store_id",'left')
            ->where($map)
            ->order($sort." ".$order)
            ->buildSql();
//        echo $sql;exit;
        $list = $this->page_list('b_goods_info',$sql);
        $id = I('id') ? I('id') : 0;
        //根据国家馆获取品牌列表
        $brand_list = $this->getAllBrandSelf($country_id);
        foreach($list as $key=>$val){
            $list[$key]['goods_img'] = $this->getGoodsImg($val['goods_id']);
        }
        $this->assign('brand_list',$brand_list);
        $this->assign('brand_name',$this->getBrandName($brand_id));
        $this->assign('_lists',$list);
        $this->assign('id',$id);
        $this->assign('brand_id',$brand_id);
        $this->assign('price_min',$price_min);
        $this->assign('price_max',$price_max);
        $this->assign('p',$p);
        $this->assign('sort',$sort);
        $this->assign('order',$order);
        $this->assign('style',$style);
        $detal = M("m_country")->where(array('id' => $country_id))->find();
        $this->assign('detail',$detal);
        $this->display();
    }



    
 
    
    
   


}