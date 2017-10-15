<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/7
 * Time: 16:48
 */

namespace Business\Controller;


class BusinessInfoController extends AdminController
{
    //企业信息
    public function index(){
        header("Content-type: text/html; charset=utf-8");
        $condition['b_id'] = $this->get_quality_id();
        //var_dump($condition);exit;
        //获取企业信息
        $_bussiness_info = M('b_bussiness_info')->where($condition)->find();
        $_bussiness_info['add_date'] = date('Y年m月d日',$_bussiness_info['add_date']);
        $_bussiness_info['start_term'] = date('Y年m月d日',$_bussiness_info['start_term']);
        $_bussiness_info['end_term']   = date('Y年m月d日',$_bussiness_info['end_term']);
        $this->assign('_bussiness_info',$_bussiness_info);
        //获取资质信息
        $res = M('b_qualification_info')->where($condition)->select();
        foreach ($res as $k=>$v){
            if($v['q_type'] == 2){
                $res[$k]['name'] = '品牌方资质';
            }
            if($v['q_type'] == 3){
                $res[$k]['name'] = '采购方资质';
            }

            if($v['q_type'] == 4){
                $res[$k]['name'] = '服务方资质';
            }
        }
        $this->assign('qualification',$res);

        //获取店铺信息
        $store = M('b_store_info')->where($condition)->find();
        $store['detail_category'] = getDetailCategoryName($store['category']);
//        var_dump($store);exit;
        $store['category'] = getCategoryName($store['category']);
        $this->assign('store',$store);

        $this->assign('act','index');
        $this->display('index');
    }
    //合作资质信息
    public function qualification(){
        $condition['q_id'] = $this->get_quality_id();


        $this->assign('act','qualification');
        $this->display();
    }
    //店铺信息
    public function store(){


        $this->assign('act','store');
        $this->display();
        }

    //获取商家资质ID
    public function get_quality_id()
    {
        $condition['uid'] = UID;
        $res = M('b_member_info')->where($condition)->find();
        return $res['b_id'];
    }
    //
}