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
class ChannelController extends HomeController {

	//系统首页
    public function index(){
        $sql = M('m_channel_info')->where(array('status' => 1))->order('add_time DESC')->buildSql();
        $channel_info = $this->page_list('m_channel_info',$sql);
        $this->assign('_list',$channel_info);
        $this->display('index');
    }

    public function detail(){
        //查询资质ID
        $b_id =  M('b_member_info')->where(array('uid'=>UID))->getField('b_id');
        $q_id = M("b_qualification_info")->where(array('b_id'=>$b_id))->getField('q_type');
        $id = I("id")?I("id"):0;
       $detail =  M("m_channel_info")->where(array('status' => 1,'id' => $id))->find();
       $detail['q_id'] = $q_id;
       $detail['add_time'] = date('Y-m-d',$detail['add_time']);
        $this->assign('detail',$detail);
        $this->display();

    }


    
 
    
    
   


}