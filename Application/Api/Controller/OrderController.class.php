<?php
/**
 * Created by phpStorm.
 * User: ronghaichuan
 * Date: 2017/10/9
 * Time: 13:53
 */

namespace Api\Controller;


class OrderController extends HomeController
{

    //易恒云同步订单接口
   public function index($day = 1,$form = 'month'){

      $time =  strtotime("-".$day ." ".$form);
       $map['add_time']  = array('between',array($time,time()));
       $sql = M()->table('b_order_info')
           ->field("*")
           ->where($map)
           ->buildSql();
       $order_info = $this->page_list('b_order_info',$sql);
       if($order_info){
           foreach ($order_info as $k=> $v){
               $goodsList = M('b_order_goods')->where(array('order_id'=>$v['order_id']))->select();
               foreach ($goodsList as $ks =>$val){
                    $store_id = M("b_goods_info")->where(array('goods_sn'=>$val['goods_sn']))->getField('store_id');
                    $store_name = M("b_store_info")->where(array('store_id' => $store_id))->getField('name');
                    $goodsList[$ks]['store_name'] = $store_name;
               }
               $order_info[$k]['delivery_provice'] = $this->get_region_name($v['delivery_provice']);
               $order_info[$k]['delivery_city']    = $this->get_region_name($v['delivery_city']);
               $order_info[$k]['delivery_block']   = $this->get_region_name($v['delivery_block']);
               $order_info[$k]['goodsList'] = $goodsList;
           }
           $count = M('b_order_info')->where($map)->count();
           $result['errCode']  = 0;
           $result['count']    = $count;
           $result['Message']  = '数据返回成功！';
           $result['data']     = $order_info;
           exit(json_encode($result));
       }else{
            exit(json_encode('NO Data'));
       }
   }

   //订单列表接口
    public function lists(){
        $this->resource();
        $post = $this->getOauthRequest();
        $user_id =$this->getTokenUserId($post['access_token']);
        if($user_id < 1){
            jsonError('21002','不存在此渠道信息');
        }

        $page = $_POST['page']?$_POST['page'] : 1;
        $limit  = $_POST['limit'] ? $_POST['limit'] : 10;
        if ($limit > 50){
            $limit = 50;
        }
        $count = M('b_order_goods')->where($map)->count();
        $num = ceil($count / $limit);

        $orderList = M('b_order_goods')->query($sql);
        if(!$goods_info){
            exit(jsonError('21003','Nothing Found!'));
        }
        $param = [
            'currentPage' => $page,
            'totalPage' => $num
        ];
        exit(jsonSuccess($goods_info,$param));
    }


}