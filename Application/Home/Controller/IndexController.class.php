<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use OT\DataDictionary;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class IndexController extends HomeController {

	//系统首页
    public function index(){
        $brand_number = M('m_brand')->count();
        $channel_number = M('m_channel_info')->count();
        $map['is_onsale'] = array('eq',1);
        $map['goods_status'] = array('eq',1);
        $goods_number = M('b_goods_info')->where($map)->count();

        $this->assign('brand_number',$brand_number);
        $this->assign('channel_number',$channel_number);
        $this->assign('goods_number',$goods_number);
        $this->display();
    }
    
    
   


}