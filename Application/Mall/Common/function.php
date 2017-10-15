<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

/**
 * 前台公共库文件
 * 主要定义前台公共函数库
 */

/**
 * 检测验证码
 * @param  integer $id 验证码ID
 * @return boolean     检测结果
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function check_verify($code, $id = 1){
	$verify = new \Think\Verify();
	return $verify->check($code, $id);
}

/**
 * 获取列表总行数
 * @param  string  $category 分类ID
 * @param  integer $status   数据状态
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_list_count($category, $status = 1){
    static $count;
    if(!isset($count[$category])){
        $count[$category] = D('Document')->listCount($category, $status);
    }
    return $count[$category];
}

/**
 * 获取段落总数
 * @param  string $id 文档ID
 * @return integer    段落总数
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_part_count($id){
    static $count;
    if(!isset($count[$id])){
        $count[$id] = D('Document')->partCount($id);
    }
    return $count[$id];
}

/**
 * 获取导航URL
 * @param  string $url 导航URL
 * @return string      解析或的url
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
function get_nav_url($url){
    switch ($url) {
        case 'http://' === substr($url, 0, 7):
        case '#' === substr($url, 0, 1):
            break;        
        default:
            $url = U($url);
            break;
    }
    return $url;
}


////获得访客浏览器类型
function GetBrowser(){
    if(!empty($_SERVER['HTTP_USER_AGENT'])){
        $br = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE/i',$br)) {
            $br = 'MSIE';
        }elseif (preg_match('/Firefox/i',$br)) {
            $br = 'Firefox';
        }elseif (preg_match('/Chrome/i',$br)) {
            $br = 'Chrome';
        }elseif (preg_match('/Safari/i',$br)) {
            $br = 'Safari';
        }elseif (preg_match('/Opera/i',$br)) {
            $br = 'Opera';
        }else {
            $br = 'Other';
        }
        return $br;
    }else{return "获取浏览器信息失败！";}
}

////获得访客浏览器语言
function GetLang(){
    if(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
        $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $lang = substr($lang,0,5);
        if(preg_match("/zh-cn/i",$lang)){
            $lang = "简体中文";
        }elseif(preg_match("/zh/i",$lang)){
            $lang = "繁体中文";
        }else{
            $lang = "English";
        }
        return $lang;

    }else{return "获取浏览器语言失败！";}
}

////获取访客操作系统
function GetOs(){
    if(!empty($_SERVER['HTTP_USER_AGENT'])){
        $OS = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/win/i',$OS)) {
            $OS = 'Windows';
        }elseif (preg_match('/mac/i',$OS)) {
            $OS = 'MAC';
        }elseif (preg_match('/linux/i',$OS)) {
            $OS = 'Linux';
        }elseif (preg_match('/unix/i',$OS)) {
            $OS = 'Unix';
        }elseif (preg_match('/bsd/i',$OS)) {
            $OS = 'BSD';
        }else {
            $OS = 'Other';
        }
        return $OS;
    }else{return "获取访客操作系统信息失败！";}
}

////获得访客真实ip
function Getip(){
    if(!empty($_SERVER["HTTP_CLIENT_IP"])){
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    if(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){ //获取代理ip
        $ips = explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
    }
    if($ip){
        $ips = array_unshift($ips,$ip);
    }

    $count = count($ips);
    for($i=0;$i<$count;$i++){
        if(!preg_match("/^(10|172\.16|192\.168)\./i",$ips[$i])){//排除局域网ip
            $ip = $ips[$i];
            break;
        }
    }
    $tip = empty($_SERVER['REMOTE_ADDR']) ? $ip : $_SERVER['REMOTE_ADDR'];
    if($tip=="127.0.0.1"){ //获得本地真实IP
        return get_onlineip();
    }else{
        return $tip;
    }
}

////获得本地真实IP
function get_onlineip() {
    $mip = file_get_contents("http://city.ip138.com/city0.asp");
    if($mip){
        preg_match("/\[.*\]/",$mip,$sip);
        $p = array("/\[/","/\]/");
        return preg_replace($p,"",$sip[0]);
    }else{return "获取本地IP失败！";}
}

////根据ip获得访客所在地地名
function Getaddress($ip=''){
    if(empty($ip)){
        $ip = Getip();
    }
    $ipadd = file_get_contents("http://int.dpool.sina.com.cn/iplookup/iplookup.php?ip=".$ip);//根据新浪api接口获取
    if($ipadd){
        $charset = iconv("gbk","utf-8",$ipadd);
        preg_match_all("/[\x{4e00}-\x{9fa5}]+/u",$charset,$ipadds);

        return $ipadds;   //返回一个二维数组
    }else{return "addree is none";}
}


//在线交易订单支付处理函数
//函数功能：根据支付接口传回的数据判断该订单是否已经支付成功；
//返回值：如果订单已经成功支付，返回true，否则返回false；

function checkorderstatus($ordid){
    $Ord=M('b_order_info');
    $ordstatus=$Ord->where(array('order_sn' => $ordid))->getField('pay_status');
    if($ordstatus==20){
        return true;
    }else{
        return false;
    }
}
//处理订单函数
//更新订单状态，写入订单支付后返回的数据
function orderhandle($parameter){
    $ordsn=$parameter['out_trade_no'];
    $data['payment_trade_no']      = $parameter['trade_no'];
    $data['payment_trade_status']  = $parameter['trade_status'];
    $data['payment_notify_id']     = $parameter['notify_id'];
    $data['payment_notify_time']   = $parameter['notify_time'];
    $data['payment_buyer_email']   = $parameter['buyer_email'];
    $data['pay_status']            = 20;
    $Ord=M('b_order_info');
    $Ord->where('order_sn='.$ordsn)->save($data);
}

//支付成功更新订单状态
function order_paid($order_sn = ''){
    $condition['order_sn'] = $order_sn;
    $order = M('b_order_parent');
    $orderList = $order->where($condition)->select();
    $arr = array();
    foreach ($orderList as $k => $v){
        $data['pay_status'] = 20;
        $data['express_status'] = 0;
        $data['sale_status']    = 10;
        $data['pay_type']       = 1;
        $data['pay_time']       = time();
        $map['order_sn']  = $v['order_sn_child'];
        M("b_order_info")->where($map)->save($data);
    }
    return true;
}

//获取国家列表
function get_country_list(){
    $list = M("m_country")->select();
    return $list;
}
