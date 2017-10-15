<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/25
 * Time: 12:54
 */

namespace Mall\Controller;
use Think\Controller;

class WhatpayController extends HomeController
{
    public function _initialize(){
        require_once 'wxpay/WxPay.NativePay.php';
        require_once 'wxpay/WxPay.config.php';
//        import("WxPay.PayNotifyCallBack");
    }
    public function index() {
        $notify = new \NativePay();
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("易恒云微信支付");
        $input->SetAttach("test");
        $input->SetOut_trade_no(\WxPayConfig::MCHID.date("YmdHis"));
        $input->SetTotal_fee($_POST['total_fee']*100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test");
        $input->SetNotify_url("http://www.yihengyun.com/mall/Whatpay/notify/");
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id($_POST['order_sn']);
        $result = $notify->GetPayUrl($input);
        $url2 = $result["code_url"];
        echo urlencode($url2);exit;
    }
    /**
     * @function：微信支付回调处理
     * @author 荣海川
     * @time 2016-9-22下午3:41:59
     */
    public function notify() {
        // 获取微信回调的数据
        $notifiedData = $GLOBALS ['HTTP_RAW_POST_DATA'];
        $postdata = json_decode(json_encode(simplexml_load_string($notifiedData, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        //微信端签名
        $wxsign = $postdata['sign'];
        unset($postdata['sign']);

        foreach ($postdata as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);

        $buff = "";
        foreach ($Parameters as $k => $v)
        {
            $buff .= $k . "=" . $v . "&";
        }
        $String = '';
        if (strlen($buff) > 0)
        {
            $String = substr($buff, 0, strlen($buff)-1);
        }
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".\WxPayConfig::APPID;
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $sign = strtoupper($String);
        //验证成功
        if ($wxsign == $sign) {
            //交易成功
            if($postdata['result_code'] == 'SUCCESS'){
                //获取log_id
                $out_trade_no_o = explode('O', $postdata['out_trade_no']);
                $out_trade_no_r = explode('R', $out_trade_no_o[1]);//订单号log_id
                $order_sn = $out_trade_no_r[0];
                // 改变订单状态
                order_paid($order_sn);
            }
            $returndata['return_code'] = 'SUCCESS';
        }
    }


}