<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/12
 * Time: 10:52
 */

namespace Api\Controller;


class GetbrandController extends HomeController
{
    public function getbrand(){
        $page_count = $this->getbrand_count();
        $nums = ceil($page_count / 100);
        for ($i = 0;$i<$nums;$i++) {
            $arr = json_decode($this->get_brand($i, 100), true);
            foreach ($arr['data'] as $k => $v) {
                $data['brand_name'] = $v['brand_name'];
                $data['brand_letter'] = $v['brand_letter'];
                $data['slogan'] = '';
                $data['slogan_letter'] = '';
                $data['country'] = $v['country_id'];
                $data['create_date'] = '';
                $data['add_date'] = $v['add_time'];
                $data['update_time'] = '';
                $data['delete_time'] = '';

                $data['desc_cn'] = $v['brand_desc'];
                $data['desc_letter'] = '';
                $data['logo'] = $this->get_brand_logo($v['brand_logo']);
                $data['big_logo'] = $this->get_big_logo($v['big_logo']);
                $data['web_url'] = $this->get_web_url($v['site_url']);
                $data['sort'] = 0;
                $data['status'] = 1;
                $brand_list = M('m_brand')->where(array('brand_name' => $v['brand_name']))->find();
                if ($brand_list) {
                    $data['update_time'] = time();
                    $res = M('m_brand')->where('brand_id='.$brand_list['brand_id'])->save($data);
                } else {
                    $res = M('m_brand')->add($data);
                }
            }
        }
        if($res){
            die('It is ok!');
        }
    }

    private function getbrand_count(){
//        $page_url = "http://test.api.com/v1/getbrand/getbrand_count?";
        $page_url = "http://106.14.93.24/v1/getbrand/getbrand_count?";
        $res = http($page_url);
        return $res;
    }

    public function get_brand($page='',$pagesize=''){
//        $url = "http://test.api.com/v1/getbrand/getbrand";
        $url = "http://106.14.93.24/v1/getbrand/getbrand";
        $param['page'] = $page;
        $param['pagesize'] = $pagesize;
        $response = http($url,$param);
        return $response;
    }

    public function shop(){
        header("Content-type:text/html;charset=utf-8");
        $arr = json_decode($this->shop_list(), true);
//        echo '<pre>';print_r($arr);exit;
        foreach ($arr['data'] as $k => $v) {
            $bussiness = M('b_bussiness_info')->where(array('b_name' => $v['user_name']))->find();
            if($bussiness){
                $bussiness_info = array('add_date'=>time());
                $res = M('b_bussiness_info')->where(array('b_name' => $v['user_name']))->save($bussiness_info);
                if($res){
                    $qualification = array('remark'=>'更新数据');
                    $resu = M('b_qualification_info')->where(array('b_id' => $bussiness_info['b_id']))->save($qualification);
                    if($resu){
                        $qualification_info = M('b_qualification_info')->where(array('b_id' => $bussiness_info['b_id']))->find();
                        $store = array('remark'=>'更新数据');
                        $result = M('b_store_info')->where('q_id='.$qualification_info['q_id'])->save($store);
                    }
                }
            }else{
                if($v['user_name']){
                    $bussiness_info = array('b_name' => $v['user_name'], 'type' => 1,'add_date'=>time());
                    $res = M('b_bussiness_info')->add($bussiness_info);
                    if($res){
                        $b_id = M('b_bussiness_info')->getLastInsID();
                        $qualification = array('b_id'=>$b_id,'q_type'=>2,'q_about'=>$v['user_name'],'q_status'=>100);
                        $resu = M('b_qualification_info')->add($qualification);
                        if($resu){
//                            $q_id = M('b_qualification_info')->getLastInsID();
                            $store = array('b_id'=>$b_id,'name'=>$v['user_name'],'summary'=>$v['user_name'],'title'=>$v['user_name'],'key_words'=>$v['user_name'],'category'=>'','s_status'=>100);
                            $result = M('b_store_info')->add($store);
                        }
                    }
                }
            }
        }
        if($result){
            die('It is ok!');
        }
    }

    private function shop_list(){
//        $page_url = "http://test.api.com/v1/getbrand/getshop?";
        $page_url = "http://106.14.93.24/v1/getbrand/getshop?";
        $res = http($page_url);
        return $res;
    }


    public function shopbrand(){
        header("Content-type:text/html;charset=utf-8");
        $page_count = $this->getshop_count();
        $nums = ceil($page_count / 100);
        for ($i = 0;$i<$nums;$i++) {
            $arr = json_decode($this->shopbrand_list($i, 100), true);
//            $arr = json_decode($this->shopbrand_list(0, 170), true);
//            echo '<pre>';print_r($page_count);exit;
            foreach ($arr['data'] as $k => $v) {
                $store = M('b_store_info')->where(array('name'=>$v['user_name']))->find();

                $data['store_id'] = $store['store_id'];
                $data['brand_name'] = $v['brandName'];
                $data['brand_letter'] = $v['brand_letter'];
                $data['slogan'] = '';
                $data['slogan_letter'] = '';
                $data['country'] = $v['country_id'];
                $data['create_date'] = '';
                $data['add_date'] = $v['add_time'];
                $data['update_time'] = '';
                $data['delete_time'] = '';

                $data['desc_cn'] = $v['brand_desc'];
                $data['desc_letter'] = '';
                $data['logo'] = $this->get_brand_logo($v['brand_logo']);
//                $data['big_logo'] = $this->get_brand_logo($v['big_logo']);
                $data['web_url'] = $this->get_web_url($v['site_url']);
                $data['sort'] = 0;
                $data['status'] = 1;
                $brand_list = M('b_brand_info')->where(array('store_id' => $store['store_id'], 'brand_name' => $v['brandName']))->find();

                if ($brand_list) {
                    $data['update_time'] = time();
                    $result = M('b_brand_info')->where('id='.$brand_list['id'])->save($data);
//                    $brand = M('b_brand')->where(array('brand_name'=>$v['brand_name']))->find();
//                    $brand_link = M('b_brand_link')->where(array('bid'=>$brand_list['id'],'brand_id'=>$brand['brand_id']))->find();
//                    $result = M('b_brand_link')->where(array('bid'=>$brand_list['id'],'brand_id'=>$brand['brand_id']))->save($brand_link);
                } else {
                    $res = M('b_brand_info')->add($data);
                    if($res){
                        $brand = M('m_brand')->where(array('brand_name'=>$v['brand_name']))->find();
                        $bid = M('b_brand_info')->getLastInsID();
                        $brand_link = array('bid'=>$bid,'brand_id'=>$brand['brand_id']);
                        $result = M('s_brand_link')->add($brand_link);
                    }
                }
            }
        }
        if($result){
            die('It is ok!');
        }
    }

    private function shopbrand_list($page='',$pagesize=''){
//        $url = "http://test.api.com/v1/getbrand/getshop_brand";
        $url = "http://106.14.93.24/v1/getbrand/getshop_brand";
        $param['page'] = $page;
        $param['pagesize'] = $pagesize;
        $response = http($url,$param);
        return $response;
    }


    public function getshop_count(){
//        $page_url = "http://test.api.com/v1/getbrand/getshop_count?";
        $page_url = "http://106.14.93.24/v1/getbrand/getshop_count?";
        $res = http($page_url);
        return $res;
    }

    public function get_brand_logo($image=''){
        if($image){
            $image = 'http://www.pgjk.com/data/brandlogo/' . $image;
        }else{
            $image = '';
        }
        return $image;
    }

    public function get_big_logo($image=''){
        if($image){
            $image = 'http://www.pgjk.com/data/big_logo/' . $image;
        }else{
            $image = '';
        }
        return $image;
    }

    public function get_web_url($url=''){
        $urls = substr($url, 7, 1);
        if($urls){
            $web_url = $url;
        }else{
            $web_url = '';
        }
        return $web_url;
    }
}