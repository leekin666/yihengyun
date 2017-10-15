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
class BrandController extends HomeController {

	//品牌首页
    public function index(){
        $map['status'] = 1;
        if(I('id')){
            $map['country'] = I('id');
            $this->assign('id',I('id'));
        }
        $sql = M('m_brand')->where($map)->order('add_date ASC')->buildSql();
        $brand_info = $this->page_list('m_brand',$sql);
        foreach($brand_info as $k=>$v){
            $brand_info[$k]['logo'] = $this->get_image_path($v['logo']);
        }
        //国家列表
      $this->assign('country_list',get_country_list());
        $this->assign('brand_list',$brand_info);
        $this->display();
    }

    //品牌详情页
    public function detail(){
        header("Content-type:text/html;charset=utf-8");
        $id = I('id')?I('id'):'';
        $brand_id = I('brand')?I('brand'):'';
        $price_min = I('price_min') ? I('price_min') : 0;
        $price_max = I('price_max') ? I('price_max') : 0;
        $p = I('p') ? I('p') : 1;
        $sort = I('sort') ? I('sort') : 'brand_id';
        $order = I('order') ? I('order') : 'ASC';
        $style = I('style') ? I('style') : 'g';//默认大图

        $brand_info = M('m_brand')->where('brand_id='.$id)->find();
        $brand_info['logo'] = $this->get_image_path($brand_info['logo']);
        $brand_info['big_logo'] = $brand_info['big_logo']?$brand_info['big_logo']:'';
//        echo '<pre>';print_r($brand_info);exit;
        $brand_list = $this->getAllBrandSelf();
        if($price_min || $price_max) $map['_string'] = "gi.price >= ".$price_min." and gi.price <= ".$price_max;
        $map['gi.brand_id'] = array('in',$this->getBrandBusiness($id));
        $map['gi.goods_status'] = array('eq',1);
//        $map['gi.is_onsale'] = array('eq',1);
        $sql = M()->table('b_goods_info AS gi')
            ->field("gi.*,bi.brand_name,si.name as store_name")
            ->join("b_brand_info AS bi ON bi.id = gi.brand_id",'left')
            ->join("b_store_info AS si ON si.store_id = gi.store_id",'left')
            ->where($map)
            ->order($sort." ".$order)
            ->buildSql();
//        echo '<pre>';print_r($sql);exit;
        $goods_info = $this->page_list('b_goods_info',$sql);
        foreach($goods_info as $key=>$val){
            $goods_info[$key]['goods_img'] = $this->getGoodsImg($val['goods_id']);
        }

        $this->assign('goods_info',$goods_info);
        $this->assign('brand_info',$brand_info);
        $this->assign('brand_list',$brand_list);
        $this->assign('id',$id);
        $this->assign('price_min',$price_min);
        $this->assign('price_max',$price_max);
        $this->assign('p',$p);
        $this->assign('sort',$sort);
        $this->assign('order',$order);
        $this->assign('style',$style);
        $this->display('brandlist');
    }
}