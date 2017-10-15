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
class CartController extends HomeController {

    public function _initialize(){
        parent::_initialize();
        if(!UID && (ACTION_NAME != 'index' && ACTION_NAME != 'check')){
            echo -9;exit;
        }
    }

    /* 购物车详情首页 */
    public function index(){
        $res = $this->getCartInfoGroup();
        $this->assign('list',$res['list']);
        $this->assign('num',$res['num']);
        $this->assign('totle',$res['totle']);
		$this->assign('is_short_menu',1);
		$this->assign('is_cart',1);
        $this->display('index');
    }

    /**
     * 结算订单
     */
    public function check(){
        $res = $this->getCartInfoGroup();
        $user_id = UID;
        //查询收货地址列表
        $map['user_id'] = array('eq',$user_id);
        $user_address = M('b_user_address')->where($map)->select();
        foreach ($user_address as $k=>$v){
           $address =  $this->get_region_name($v['province'])." ".$this->get_region_name($v['city'])." ".$this->get_region_name($v['district'])." ".$v['address'];
           $user_address[$k]['address_list'] = $address;
        }
        //查询默认收货
        $where['uid'] = array('eq',$user_id);
        $consignee = M('b_member_info')->where($where)->find();
        $this->assign('user_address',$user_address);
        $this->assign('consignee',$consignee);
        $this->assign('list',$res['list']);
        $this->assign('num',$res['num']);
        $this->assign('totle',$res['totle']);
    	$this->assign('is_short_menu',1);
    	$this->assign('is_cart',1);
    	$this->assign('is_check',1);
        $this->display('check');
    }
    /**
     * 添加购物车
     */
    public function addCart(){
        $user_id = UID;
        $goods_id = I('goods_id') ? intval(I('goods_id')) : 0;
        $goods_number = I('goods_number') ? intval(I('goods_number')) : 1;
        $divId = I('divId') ? I('divId') : '';
        if(!$goods_id || !$goods_number){
            $result = array('error' => 1, 'message' => '请选择商品和数量！', 'result' => '');
            echo json_encode($result);exit;
        }
        //商品详情
        $goods = $this->getGoodsInfo($goods_id,$goods_number);
        if(!isset($goods['goods_id']) || empty($goods['goods_id'])){
            $result = array('error' => 1, 'message' => '此商品不存在或已下架！', 'result' => '');
            echo json_encode($result);exit;
        }
        if($goods_number > $goods['stock']){
            $result = array('error' => 1, 'message' => '库存不足！', 'result' => '');
            echo json_encode($result);exit;
        }
        //拼装购物车字段
        $cart = $this->getCartField($goods);
        //判断购物车是否有该商品
        $is_have = $this->getUserCart($user_id,$goods_id);
        if(!$is_have){
            $res = M('b_cart')->add($cart);
        }else{
            $map['user_id'] = array('eq',$user_id);
            $map['goods_id'] = array('eq',$goods_id);
            if($goods_number > 0){
                $res = M('b_cart')->where($map)->setInc('number',$goods_number);
            }else{
                $res = M('b_cart')->where($map)->setDec('number',abs($goods_number));
            }
        }
        //当前商品的数量
        $numThis = M('b_cart')->where($map)->getField('number');
        //当前用户购物车所有数量和价格
        $_all = $this->getCartInfo();
        if($res){
            $result = array('error' => 0, 'message' => '添加购物车成功！','goods_id' => $goods_id, 'subtotal_number'=>$_all['num'],'goods_number' =>intval($numThis), 'goods_subtotal'=>$_all['totle'], 'divId' => $divId);
            echo json_encode($result);exit;
        }else{
            $result = array('error' => 1, 'message' => '添加购物车失败！', 'result' => '');
            echo json_encode($result);exit;
        }
    }
    /**
     * 根据数量添加购物车
     */
    public function addCartOther(){
        $user_id = UID;
        $goods_id = I('goods_id') ? intval(I('goods_id')) : 0;
        $goods_number = I('goods_number') ? intval(I('goods_number')) : 1;
        if(!$goods_id || !$goods_number){
            $result = array('error' => 1, 'message' => '请选择商品和数量！', 'result' => '');
            echo json_encode($result);exit;
        }
        //商品详情
        $goods = $this->getGoodsInfo($goods_id,$goods_number);
        if(!isset($goods['goods_id']) || empty($goods['goods_id'])){
            $result = array('error' => 1, 'message' => '此商品不存在或已下架！', 'result' => '');
            echo json_encode($result);exit;
        }
        if($goods_number > $goods['stock']){
            $result = array('error' => 1, 'message' => '库存不足！', 'result' => '');
            echo json_encode($result);exit;
        }
        //拼装购物车字段
        $cart = $this->getCartField($goods);
        //判断购物车是否有该商品
        $is_have = $this->getUserCart($user_id,$goods_id);
        if(!$is_have){
            $res = M('b_cart')->add($cart);
        }else{
            $map['user_id'] = array('eq',$user_id);
            $map['goods_id'] = array('eq',$goods_id);
            if($goods_number > 0){
                $res = M('b_cart')->where($map)->setField(array('number'=>$goods_number));
            }else{
                $goods_number = 1;
                $res = M('b_cart')->where($map)->setField(array('number'=>$goods_number));
            }
        }
        //当前商品的数量
        $numThis = M('b_cart')->where($map)->getField('number');
        //当前商品的小计
        $priceThis = M('b_cart')->where($map)->getField('number * price');
        if($res){
            $result = array('error' => 0, 'message' => '添加购物车成功！','goods_id' => $goods_id,'goods_number' =>intval($numThis),'goods_price'=>$priceThis);
            echo json_encode($result);exit;
        }else{
            $result = array('error' => 1, 'message' => '添加购物车失败！', 'result' => '');
            echo json_encode($result);exit;
        }
    }
    /**
     * 删除购物车商品
     */
    public function deleteCartGoods(){
        $user_id = UID;
        $goods_id_str = I('goods_id') ? I('goods_id') : '';
        $index_val = I('index_val') ? I('index_val') : 0;
        if(isset($goods_id_str) && !empty($goods_id_str)){
            $map['goods_id'] = array('in',explode(',',$goods_id_str));
        }
        $map['user_id'] = array('eq',$user_id);
        $res = M('b_cart')->where($map)->delete();
        if($res){
            $result = array('error' => 0, 'message' => '删除成功！', 'result' => '','index_val' => $index_val);
            echo json_encode($result);exit;
        }
    }
    /**
     * 返回当前用户购物车商品
     */
    public function cartInfo(){
        $block = I('block') ? I('block') : '';
        $res = $this->getCartInfo();
        if($block == 'header'){
            $this->assign('list',$res['list']);
            $this->assign('num',$res['num']);
            $this->assign('totle',$res['totle']);
            $this->assign('user_id',UID);
            $this->assign('user_name',session('user_auth.username'));
            echo $this->fetch('header_float_cart_info');
            exit;
        }
        if($block == 'right'){
            $this->assign('list',$res['list']);
            $this->assign('num',$res['num']);
            $this->assign('totle',$res['totle']);
            $this->assign('user_id',UID);
            $this->assign('user_name',session('user_auth.username'));
            echo $this->fetch('right_float_cart_info');
            exit;
        }
        echo json_encode($res);exit;
    }
    /**
     * 返回地区
     */
    public function region($pid = 0){
        $pid = I('id') ? I('id') : 0;
        echo getRegion($pid,true);
    }
    /**
     * 收货人地址弹层
     */
    public function addressEdit(){
        $user_id = UID;
        $address_id = I('address_id') ? I('address_id') : 0;
        $map['user_id'] = array('eq',$user_id);
        $map['address_id'] = array('eq',$address_id);
        $consignee = M('b_user_address')->where($map)->find();
        $this->assign('consignee',$consignee);
        echo $this->fetch('address_layer');
    }
    /**
     * 添加收货人地址
     */
    public function addUserAddress(){
        $json = I('csg') ? I('csg') : '';
        $data = json_decode($json,true);
        $data['user_id'] = UID;
        $res = M('b_user_address')->add($data);
        if($res){
            $result = array('error' => 0, 'message' => '添加成功！', 'result' => '');
            echo json_encode($result);exit;
        }else{
            $result = array('error' => 1, 'message' => '添加失败！', 'result' => '');
            echo json_encode($result);exit;
        }

    }
    /**
     * 添加收货人地址
     */
    public function editUserAddress(){
        $json = I('csg') ? I('csg') : '';
        $data = json_decode($json,true);
        $data['user_id'] = UID;
        $res = M('b_user_address')->save($data);
        if($res){
            $result = array('error' => 0, 'message' => '编辑成功！', 'result' => '');
            echo json_encode($result);exit;
        }else{
            $result = array('error' => 1, 'message' => '编辑失败！', 'result' => '');
            echo json_encode($result);exit;
        }
    }
    /**
     * 删除收货地址
     */
    public function deleteAddress(){
        $user_id = UID;
        $address_id = I('address_id') ? I('address_id') : 0;
        $map['user_id'] = array('eq',$user_id);
        $map['address_id'] = array('eq',$address_id);
        $res = M('b_user_address')->where($map)->delete();
        if($res){
            $result = array('error' => 0, 'message' => '删除成功！', 'result' => '');
            echo json_encode($result);exit;
        }else{
            $result = array('error' => 1, 'message' => '删除失败！', 'result' => '');
            echo json_encode($result);exit;
        }
    }
    /**
     * 显示收货人所有地址
     */
    public function addressList(){
        $user_id = UID;
        $map['user_id'] = array('eq',$user_id);
        $user_address = M('b_user_address')->where($map)->select();
        foreach ($user_address as $k=>$v){
            $address =  $this->get_region_name($v['province'])." ".$this->get_region_name($v['city'])." ".$this->get_region_name($v['district'])." ".$v['address'];
            $user_address[$k]['address_list'] = $address;
        }
        $where['uid'] = array('eq',$user_id);
        $consignee = M('b_member_info')->where($where)->find();
        $this->assign('user_address',$user_address);
        $this->assign('consignee',$consignee);
        echo $this->fetch('address_list');
    }
    /**
     * 设置用户默认收货地址
     */
    public function setDefaultAddress(){
        $user_id = UID;
        $address_id = I('address_id') ? I('address_id') : 0;
        $map['uid'] = array('eq',$user_id);
        $res = M('b_member_info')->where($map)->setField(array('address_id'=>$address_id));
        if($res){
            $result = array('error' => 0, 'message' => '设置成功！', 'result' => '');
            echo json_encode($result);exit;
        }else{
            $result = array('error' => 1, 'message' => '设置失败！', 'result' => '');
            echo json_encode($result);exit;
        }
    }
    /**
     * 获取商品详情
     */
    public function getGoodsInfo($goods_id = 0,$goods_number = 0){
        if(!$goods_id){
            return false;
        }
        $map['goods_id'] = array('eq',$goods_id);
        $res = M('b_goods_info')->where($map)->find();
        $res['number'] = $goods_number;
        if($res['is_real'] == 0){
            $res['stock'] = 9999;//虚拟商品始终有库存
        }else{
            $res['stock'] = $this->getStock($goods_id);
        }
        return $res;
    }

    /**
     * 购物车字段拼装
     */
    public function getCartField($arr = array()){
        $resArr = array(
            'user_id' => UID,
            'goods_id' => $arr['goods_id'],
            'goods_sn' => $arr['goods_sn'],
            'number' => $arr['number'],
            'store_id' => $arr['store_id'],
            'brand_id' => $arr['brand_id'],
            'goods_name' => $arr['goods_name'],
            'goods_letter' => $arr['goods_letter'],
            'product_sn' => $arr['product_sn'],
            'store_sn' => $arr['store_sn'],
            'category_id' => $arr['category_id'],
            'price' => $arr['price'],
            'market_price' => $arr['market_price'],
            'weight' => $arr['weight'],
            'is_cross' => $arr['is_cross'],
            'add_date' => $arr['add_date'],
            'update_time' => $arr['update_time']
        );
        return $resArr;
    }
    /**
     * 判断购物车是否有该商品
     */
    public function getUserCart($user_id = 0,$goods_id = 0){
        $map['user_id'] = array('eq',$user_id);
        $map['goods_id'] = array('eq',$goods_id);
        $res = M('b_cart')->where($map)->count();
        return $res;
    }


}