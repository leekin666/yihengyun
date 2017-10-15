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
class OrderController extends HomeController {

    public function _initialize(){
        parent::_initialize();
        if(!UID && ACTION_NAME != 'index'){
            echo -9;exit;
        }
    }
    /**
     * 创建订单
     */
    public function createOrder(){
        $user_id = UID;
        $pay_type = I('payment') ? I('payment') : 0;
        $address_id = I('consignee_radio') ? I('consignee_radio') : 0;
        $goods_id_str = I('goods_id') ? I('goods_id') : '';
        if(!$pay_type || !$address_id || !$goods_id_str){
            $this->error('提交失败，提交信息不完整！',U('/Mall/Cart/check'));
        }
        $cart_goods_group = $this->getCartInfoGroup();
        if($cart_goods_group['error']){
            $this->error('提交失败，未检测到购物车商品信息！',U('/Mall/Cart/check'));
        }
        $res = $this->insertOrder($pay_type,$address_id, $goods_id_str,$cart_goods_group);
        if($res['flag']){
            $parent_order_sn = M('b_order_parent')->where(array('id'=>$res['id']))->getField('order_sn');
            $this->redirect('/Mall/order/orderSuccess/parent_order_sn/'.$parent_order_sn,'订单创建成功！');
        }else{
            $this->redirect('/Mall/Cart/check','订单创建失败！');
        }
    }
    /**
     * 结算订单
     */
    public function orderSuccess(){
        $parent_order_sn = I('parent_order_sn') ? I('parent_order_sn') : '';
        if(!$parent_order_sn){
            $this->error('订单创建失败！',U('/Mall/Cart/check'));
        }
        $map['order_sn'] = array('eq',$parent_order_sn);
        $order_sn_arr = M('b_order_parent')->where($map)->getField('order_sn_child',true);
        $where['order_sn'] = array('in',$order_sn_arr);
        $order = M('b_order_info')->where($where)->select();
        $totle_price = 0;
        foreach($order as $key=>$val){
            $totle_price += $val['goods_amount'] + $val['ship_fee'] + $val['tax_fee'];
            $pay_type = $val['pay_type'];
            $address =  $this->get_region_name($val['delivery_provice'])." ".$this->get_region_name($val['delivery_city'])." ".$this->get_region_name($val['delivery_block'])." ".$val['delivery_address'];
            $delivery_name = $val['delivery_name'];
            $delivery_mobile = $val['delivery_mobile'];
        }
        $info = array(
            'totle_price' => $totle_price,
            'pay_type' => $pay_type,
            'address' => $address,
            'delivery_name' => $delivery_name,
            'delivery_mobile' => $delivery_mobile,
            'parent_order_sn' => $parent_order_sn,
        );
        $this->assign('info',$info);
        $this->assign('is_short_menu',1);
        $this->assign('is_cart',1);
        $this->assign('is_orderSuccess',1);
        $this->display();
    }
    //支付成功页面
    public function paySuccess(){
        $this->display('paysuccess');
    }
    //支付失败页面
    public function payFail(){
        $this->display('payfail');
    }
    /**
     * 获取购物车中下单的商品
     */
    public function insertOrder($pay_type = 0,$address_id = 0, $goods_id_str = '',$arr = array()){
        $user_id = UID;
        M()->startTrans();//开启事务
        //查询收货信息
        $address = M('b_user_address')->where(array('address_id'=>$address_id))->find();
        //循环插入订单
        $flag = array('flag' => false);
        foreach($arr['list'] as $key=>$val){
            $goods_arr = array();
            $sum_price = 0;
            foreach($val['goods'] as $kg=>$vg){
                $goods_arr[] = $vg;
                $sum_price += $vg['sum_price'];
            }
            $order_data = array(
                'order_sn' => $this->get_order_sn(),
                'account_id' => $user_id,
                'delivery_name' => $address['consignee'],
                'delivery_mobile' => $address['mobile'],
                'delivery_provice' => $address['province'],
                'delivery_city' => $address['city'],
                'delivery_block' => $address['district'],
                'delivery_address' => $address['address'],
                'pay_type' => $pay_type,
                'pay_code' => '',
                'pay_amount' => '',
                'goods_amount' => $sum_price,
                'ship_fee' => '',
                'tax_fee' => '',
                'pay_time' => '',
                'express_name' => '',
                'express_code' => '',
                'add_time' => time(),
                'check_status' => 30,//已确认
                'pay_status' => 10,//未支付
                'express_status' => 10,//未发货
                'sale_status' => 0,
                'buyer_type' => 10,//大B
                'account_period_time' => '',
            );
            //插入订单
            $res = M('b_order_info')->add($order_data);
            $order_arr[] = $res;
            if($res){
                $order_goods = array();
                foreach($goods_arr as $key=>$val){
                    $order_goods[] = array(
                        'order_id' => $res,
                        'goods_sn' => $val['goods_sn'],
                        'goods_name' => $val['goods_name'],
                        'goods_pic' => $val['images']['m'][0],
                        'buy_num' => $val['number'],
                        'goods_price' => $val['price'],
                        'help_sell' => '',
                        'ship_fee' => '',
                        'add_time' => time(),
                    );
                }
                $res_goods = M('b_order_goods')->addAll($order_goods);
            }
            if($res_goods){
                $map['user_id'] = array('eq',$user_id);
                if(isset($goods_id_str) && !empty($goods_id_str)){
                    if(is_array($goods_id_str)){
                        $map['goods_id'] = array('in',$goods_id_str);
                    }else{
                        $map['goods_id'] = array('in',explode(',',$goods_id_str));
                    }
                }
                M('b_cart')->where($map)->delete();
            }
        }
        $where['order_id'] = array('in',$order_arr);
        $order_sn_arr = M('b_order_info')->where($where)->getField('order_sn',true);
        $arr = array();
        foreach($order_sn_arr as $key=>$val){
            $arr[] = array(
                'account_id' => $user_id,
                'order_sn' => $this->get_order_sn(),
                'order_sn_child' => $val,
                'add_time' => time()
            );
        }
        $res_order_parent = M('b_order_parent')->addAll($arr);
        if($res_order_parent){
            $flag = array('flag' => true,'id' => $res_order_parent);
            M()->commit();//事务提交
        }else{
            $flag = array('flag' => false);
            M()->rollback();//回滚
            return $flag;
        }
        return $flag;
    }
}