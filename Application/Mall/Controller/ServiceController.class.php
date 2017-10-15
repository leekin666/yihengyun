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
 * 主要获取首页聚合数据
 */
class ServiceController extends HomeController {

    //系统首页
    public function index(){
        $first_cat = I('first_cat') ? I('first_cat') : 0;
        $second_cat = I('second_cat') ? I('second_cat') : 0;
        $price_min = I('price_min') ? I('price_min') : 0;
        $price_max = I('price_max') ? I('price_max') : 0;
        $p = I('p') ? I('p') : 1;
        $sort = I('sort') ? I('sort') : 'id';
        $order = I('order') ? I('order') : 'ASC';
        $style = I('style') ? I('style') : 'g';//默认大图
        if($second_cat){
            $map['gi.category_id'] = array('eq',$second_cat);
        }else{
            $map['gi.category_id'] = array('in',$this->getChild($first_cat));
        }
        if($price_min || $price_max) $map['_string'] = "gi.price >= ".$price_min." and gi.price <= ".$price_max;
        $map['gi.goods_status'] = array('eq',1);
        $map['gi.is_real'] = array('eq',0);//虚拟商品
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
        $this->assign('_lists',$list);
        $this->assign('cat_fic_second',$this->get_category_fictitious(2,$first_cat));//根据一级类目获取二级虚拟类目
        $this->assign('first_cat',$first_cat);
        $this->assign('second_cat',$second_cat);
        $this->assign('price_min',$price_min);
        $this->assign('price_max',$price_max);
        $this->assign('p',$p);
        $this->assign('sort',$sort);
        $this->assign('order',$order);
        $this->assign('style',$style);
        $this->display();
    }
    public function detail(){
        $id = I('id') ? I('id') : 0;
        $goods_detail = M("b_goods_info")->where(array('goods_id' => $id))->find();
        $goods_detail['details'] =  htmlspecialchars_decode($goods_detail['details']);
        $goods_detail['images'] = $this->getGoodsImg($id,400,400);
        $goods_detail['brandName'] = $this->getBrandName($goods_detail['brand_id']);
        $subQuery = M('b_goods_group')->field('group_id')->where(array('goods_id'=>$id))->buildSql();
        $goods_ids = M('b_goods_group')->where('group_id='.$subQuery)->getField('goods_id',true);
        if($goods_ids){
            $map['gi.goods_id'] = array('in',$goods_ids);
            $goods_arr = M()->table('b_goods_info AS gi')
                ->field('gi.*,gg.alias')
                ->join('b_goods_group AS gg ON gg.goods_id = gi.goods_id','left')
                ->where($map)
                ->select();
        }
        $this->assign('info',$goods_detail);
        $this->assign('list',$goods_arr);
        $this->assign('goods_id',$id);
        $this->display();
    }
    /**
     * 根据类目ID获取所有子类ID，包含自身ID
     */
    function getChild($category_id = 0){
        if(!$category_id){
            $arr = M('m_category_fictitious')->getField('id',true);
        }else{
            $map['pid'] = array('eq',$category_id);
            $arr = M('m_category_fictitious')->where($map)->getField('id',true);
        }
        if(!$arr){
            $arr = array($category_id);
        }else{
            array_unshift($arr,$category_id);
        }
        return $arr;
    }

}