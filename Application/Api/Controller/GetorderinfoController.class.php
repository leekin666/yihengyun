<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/12
 * Time: 10:52
 */

namespace Api\Controller;


class GetorderinfoController extends HomeController
{
    public function getorder(){
        header("Content-type:text/html;charset=utf-8");
        $page_count = $this->getorder_count();
        $nums = ceil($page_count / 100);
        for ($i = 0;$i<$nums;$i++) {
            $arr = json_decode($this->get_order_info($i, 100), true);
            foreach ($arr['data'] as $k => $v) {
                $data['order_sn'] = $v['order_sn'];
                $data['account_id'] = '0';
                $data['delivery_name'] = $v['consignee'];
                $data['delivery_mobile'] = $v['mobile'];
                $data['delivery_provice'] = $v['province'];
                $data['delivery_city'] = $v['city'];
                $data['delivery_block'] = $v['district'];
                $data['delivery_address'] = $v['address'];
                if($v['pay_id'] == 11 || $v['pay_id'] == 18){
                    $data['pay_type'] = 1;
                }elseif($v['pay_id'] == 14){
                    $data['pay_type'] = 2;
                }elseif($v['pay_id'] == 16){
                    $data['pay_type'] = 3;
                }else{
                    $data['pay_type'] = 0;
                }
                $data['pay_code'] = '';
                $data['pay_amount'] = $v['money_paid'];
                $data['goods_amount'] = $v['goods_amount'];
                $data['ship_fee'] = $v['shipping_fee'];
                $data['tax_fee'] = $v['tax'];
                $data['pay_time'] = $v['pay_time'];
                $data['express_name'] = '';
                $data['express_code'] = $v['invoice_no'];
                $data['add_time'] = $v['add_time'];
                $data['check_status'] = 0;
                if($v['pay_status'] == 1){
                    $data['pay_status'] = 30;
                }elseif($v['pay_status'] == 2){
                    $data['pay_status'] = 20;
                }else{
                    $data['pay_status'] = 10;
                }
                if($v['shipping_status'] == 1){
                    $data['express_status'] = 20;
                }elseif($v['shipping_status'] == 2){
                    $data['express_status'] = 30;
                }else{
                    $data['express_status'] = 10;
                }

                $data['sale_status'] = 0;
                $data['buyer_type'] = '';
                $data['account_period_time'] = '';
                $order_list = M('b_order_info')->where(array('order_sn' => $v['order_sn']))->find();
                if ($order_list) {
                    $data['update_time'] = time();
                    $res = M('b_order_info')->where('order_sn='.$v['order_sn'])->save($data);
                    if($res){
                        $map['order_id'] = $order_list['order_id'];
                        $map['goods_sn'] = $v['goods_sn'];
                        $map['goods_name'] = $v['goods_name'];
                        $map['goods_pic'] = $v['goods_thumb'];
                        $map['goods_price'] = $v['goods_price'];
                        $map['help_sell'] = 0;
                        $map['ship_fee'] = $v['shopping_fee'];
                        $map['add_time'] = time();
                        $goods_list = M('b_order_goods')->where(array('order_id' => $order_list['order_id'],'goods_sn'=>$v['goods_sn']))->find();
                        if ($goods_list) {
                            $data['add_time'] = time();
                            $result = M('b_order_goods')->where(array('order_id' => $order_list['order_id'],'goods_sn'=>$v['goods_sn']))->save($map);
                        } else {
                            $result = M('b_order_goods')->add($map);
                        }
                    }
                } else {
                    $res = M('b_order_info')->add($data);
                    $order_id = M('b_order_info')->getLastInsID();
                    if($order_id){
                        $map['order_id'] = $order_id;
                        $map['goods_sn'] = $v['goods_sn'];
                        $map['goods_name'] = $v['goods_name'];
                        $map['goods_pic'] = $v['goods_thumb'];
                        $map['goods_price'] = $v['goods_price'];
                        $map['help_sell'] = 0;
                        $map['ship_fee'] = $v['shopping_fee'];
                        $map['add_time'] = time();
                        $goods_list = M('b_order_goods')->where(array('order_id' => $order_id,'goods_sn'=>$v['goods_sn']))->find();
                        if ($goods_list) {
                            $data['add_time'] = time();
                            $result = M('b_order_goods')->where(array('order_id' => $order_id,'goods_sn'=>$v['goods_sn']))->save($map);
                        } else {
                            $result = M('b_order_goods')->add($map);
                        }
                    }
                }
            }
        }
        if($res){
            die('It is ok!');
        }
    }

    private function getorder_count(){
        $page_url = "http://test.api.com/v1/getorderinfo/getpages?";
//        $page_url = "http://106.14.93.24/v1/getorderinfo/getpages?";
        $res = http($page_url);
        return $res;
    }

    public function get_order_info($page='',$pagesize=''){
        $url = "http://test.api.com/v1/getorderinfo/index";
//        $url = "http://106.14.93.24/v1/getorderinfo/index";
        $param['page'] = $page;
        $param['pagesize'] = $pagesize;
        $response = http($url,$param);
        return $response;
    }

    public function getorders(){
        header("Content-type:text/html;charset=utf-8");
        $order_arr = M('b_order_info')->order('order_id desc')->limit(1)->find();
        $page_count = $this->getorders_count($order_arr['order_sn']);
        $nums = ceil($page_count / 100);
        for ($i = 0;$i<$nums;$i++) {
            $arr = json_decode($this->get_orders_info($order_arr['order_sn'],$i, 100), true);
            foreach ($arr['data'] as $k => $v) {
                $data['order_sn'] = $v['order_sn'];
                $data['account_id'] = '0';
                $data['delivery_name'] = $v['consignee'];
                $data['delivery_mobile'] = $v['mobile'];
                $data['delivery_provice'] = $v['province'];
                $data['delivery_city'] = $v['city'];
                $data['delivery_block'] = $v['district'];
                $data['delivery_address'] = $v['address'];
                if($v['pay_id'] == 11 ||$v['pay_id'] == 18){
                    $data['pay_type'] = 1;
                }elseif($v['pay_id'] == 14){
                    $data['pay_type'] = 2;
                }elseif($v['pay_id'] == 16){
                    $data['pay_type'] = 3;
                }else{
                    $data['pay_type'] = 0;
                }
                $data['pay_code'] = '';
                $data['pay_amount'] = $v['money_paid'];
                $data['goods_amount'] = $v['goods_amount'];
                $data['ship_fee'] = $v['shipping_fee'];
                $data['tax_fee'] = $v['tax'];
                $data['pay_time'] = $v['pay_time'];
                $data['express_name'] = '';
                $data['express_code'] = $v['invoice_no'];
                $data['add_time'] = $v['add_time'];
                $data['check_status'] = 0;
                if($v['pay_status'] == 1){
                    $data['pay_status'] = 30;
                }elseif($v['pay_status'] == 2){
                    $data['pay_status'] = 20;
                }else{
                    $data['pay_status'] = 10;
                }
                if($v['shipping_status'] == 1){
                    $data['express_status'] = 20;
                }elseif($v['shipping_status'] == 2){
                    $data['express_status'] = 30;
                }else{
                    $data['express_status'] = 10;
                }

                $data['sale_status'] = 0;
                $data['buyer_type'] = '';
                $data['account_period_time'] = '';
                $order_list = M('b_order_info')->where(array('order_sn' => $v['order_sn']))->find();
                if ($order_list) {
                    $data['update_time'] = time();
                    $res = M('b_order_info')->where('order_sn='.$v['order_sn'])->save($data);
                } else {
                    $res = M('b_order_info')->add($data);
                }
            }
        }
        if($res){
            die('It is ok!');
        }
    }

    private function getorders_count($order_sn=''){
        $page_url = "http://test.api.com/v1/getorderinfo/getorderpages";
//        $page_url = "http://106.14.93.24/v1/getorderinfo/getorderpages";
        $param['order_sn'] = $order_sn;
        $res = http($page_url,$param);
        return $res;
    }

    public function get_orders_info($order_sn='',$page='',$pagesize=''){
        $url = "http://test.api.com/v1/getorderinfo/order";
//        $url = "http://106.14.93.24/v1/getorderinfo/order";
        $param['order_sn'] = $order_sn;
        $param['page'] = $page;
        $param['pagesize'] = $pagesize;
        $response = http($url,$param);
        return $response;
    }
}