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
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {

    //系统首页
    public function index(){
        $this->assign('_brand_list',$this->show_brand_list('index_hot_brand'));
        $category_list = $this->getCategory();
        foreach ($category_list as $k => $v){
            $arr = array();
            foreach ($v['_child'] as $ks => $vs){
                $arr[] = $vs['id'];
            }
            $goods_list = $this->get_goods_list($arr,$k+1);
            $category_list[$k]['goods_list'] = $goods_list;
        }
//        var_dump($category_list);exit;
        $this->assign('category_list',$category_list);
        $this->assign('is_short_menu',1);
        $this->assign('is_show_category',1);
        $this->display();
    }
    //获取类目下面所有商业版商品
    public function get_goods_list($catArr = array(),$index = 0){
        if($index == 1){
            $index_d = 'og.index_one';
        }elseif($index == 2){
            $index_d = 'og.index_two';
        }elseif($index == 3){
            $index_d = 'og.index_three';
        }elseif($index == 4){
            $index_d = 'og.index_four';
        }elseif($index == 5){
            $index_d = 'og.index_five';
        }elseif($index == 6){
            $index_d = 'og.index_six';
        }
        $str = implode(',',$catArr);

        $map['gi.category_id'] = array('in',$str);
        $map['gi.goods_status'] = array('neq',0);
        $map[$index_d] = array('neq',0);
        $sql = M()->table('b_goods_info AS gi')
            ->field("gi.*")
            ->join("operations_goods AS og ON gi.goods_id = og.goods_id",'left')
            ->where($map)
            ->order("$index_d ASC")
            ->buildSql();
        $list = $this->page_list('b_goods_info',$sql);
        foreach ($list as $k=>$v){
            $goods_id = $v['goods_id'];
            $goods_img =  $this->getGoodsImg($goods_id);
            if($goods_img){
                $list[$k]['good_imgs'] = $goods_img;
            }
        }
        return $list;

    }
    
    
   


}