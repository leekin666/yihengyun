<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Api\Controller;

/**
 * 前台首页控制器
 * 主要获取首页聚合数据
 */
class GoodsController extends HomeController{

	//商品列表接口
    public function index(){
       
        $result = array(
            'errorCode' => 0,
            'Message'   => '',
            'data'      => array(),
        );
        $header = array();
        foreach ($_SERVER as $key => $value) {
            if ('HTTP_' == substr($key, 0, 5)) {
                $header[str_replace('_', '-', substr($key, 5))] = $value;
            }
        }
        $key    = $header['KEY'];
        $secret = $header['SECRET'];
        $token  = $header['TOKEN'];
        $data['key']    = $key;
        $data['secret'] = $secret;
        $data['status'] = 1;
        $user = M("distribution_open")->where($data)->find();
        if(!$user){
            $result = array(
                'errorCode' => 100,
                'Message'   => '不存在此渠道信息',
            );
            $json = json_encode($result);
        }else{
            $interfacename = 'GoodsList';
            $date          = date('Y-m-d',time());
            $user_key      = $user['key'];
            $user_secret   = $user['secret'];
            if($token == strtoupper(md5($user_key.$user_secret.$interfacename.date('Y-m-d',time())))){
                $page = $_POST['page']?$_POST['page'] : 0;
                $page_size  = $_POST['page_size'] ? $_POST['page_size'] : 10;
                if ($page_size > 100){
                    $page_size = 100;
                }
                $map['bo.id'] = $user['id'];
                $map['gi.is_real'] = 1;
                $map['gi.goods_status'] = 1;
                $sql = M()->table('distribution_open AS bo')
                    ->field("gi.*,mb.brand_name,dgg.goods_number")
                    ->join("distribution_goods AS dg ON bo.id = dg.channel_id",'left')
                    ->join("distribution_goods_group AS dgg ON dgg.id = dg.goods_group_id",'left')
                    ->join("b_goods_info as gi ON gi.goods_sn = dgg.goods_sn",'left')
                    ->join("m_brand AS mb ON mb.brand_id = gi.brand_id",'left')
                    ->where($map)
                    ->limit($page,$page_size)
                    ->buildSql();
                $goods_info = $this->page_list('b_goods_info',$sql);
                if(!$goods_info){
                    echo  json_encode('no data!!!!');exit;
                }
                $count = M('distribution_goods_group')->count();
                $result = array(
                    'errorCode' => 0,
                    'count'     => $count,
                    'Message'   => '数据返回成功',
                    'data'      => $goods_info
                );
                  echo  json_encode($result);exit;
            }else{
              echo  json_encode('no data!!!!');exit;
            }

        }
    }
}