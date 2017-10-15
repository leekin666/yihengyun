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
class GoodsController extends HomeController {

	//系统首页
    public function index(){
        $id = I('id') ? I('id') : 0;
        $goods_detail = M("b_goods_info")->where(array('goods_id' => $id,'goods_status' => 1))->find();
        if($goods_detail){
            $goods_detail['details'] =  htmlspecialchars_decode($goods_detail['details']);
            $f = $this->getGoodsImg($id,800,800);
            $this->assign('good_imgs',$f);
            //地址联动
            $this->assign('_address',getRegion());
            //类目列表
            $category = D('CategoryMall');
            $goods_detail['category_name'] = $category->getLastCategory($goods_detail['category_id']);
            $goods_detail['brandName'] = $this->getBrandName($goods_detail['brand_id']);
            $this->assign('_goods_list',$category->getSameLevel($goods_detail['category_id']));
            $this->assign('_goodsdetail',$goods_detail);
            $this->assign('is_short_menu',1);
            $this->display('index');
        }else{
            $this->error('此商品已下架！！！');
        }

    }

public function getregions(){
	$parent_id = I("parent_id");
	echo getRegion($parent_id,true);exit;
}
    
 
    
    
   


}