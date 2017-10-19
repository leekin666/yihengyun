<?php

namespace Api\Controller;

class GoodsController extends OauthController{

    //商品列表
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
        $count = M('s_show_goods')->where(['channel_id' => $user_id,'status' => 1])->count();
        $num = ceil($count / $limit);

        $map['os.id'] = $user_id;
        $map['bgi.is_real'] = 1;
        $map['bgi.goods_status'] = 1;

        $sql = M()->table('open_system AS os')
            ->field("bgi.goods_id,
                bgi.brand_id,
                bgi.goods_name,
                bgi.goods_letter,
                bgi.goods_sn,
                bgi.details,
                bgi.details_letter,
                bgi.desc_cn,
                bgi.desc_letter,
                bgi.category_id,      
                bgi.price,
                bgi.market_price,
                bgi.weight,
                bgi.is_cross,
                bgi.returned,
                bgi.flash,
                bgi.free_shipping,
                bgi.add_date,
                bgi.update_time,
                bgi.note,
                bgi.goods_status,
                bbi.brand_name,
                ssg.goods_number,
                mci.name AS category_name")
            ->join("s_show_goods AS ssg ON ssg.channel_id = os.id",'left')
            ->join("b_goods_info as bgi ON bgi.goods_sn = ssg.goods_sn",'left')
            ->join("b_brand_info AS bbi ON bbi.id = bgi.brand_id",'left')
            ->join("m_category_info AS mci ON mci.id = bgi.category_id",'left')
            ->where($map)
            ->limit(($page - 1) * $limit,$limit)
            ->buildSql();
        $goods_info = M('b_goods_info')->query($sql);
        if(!$goods_info){
            exit(jsonError('21003','Nothing Found!'));
        }
        $param = [
            'currentPage' => $page,
            'totalPage' => $num
        ];
        exit(jsonSuccess($goods_info,$param));
    }

    //商品详情
    public function detail(){
        $post = $this->getOauthRequest();
        if ($post['id'] || $_GET['id']) {
            $goods_id = intval($post['id']);
            if($_GET['id']){
                $goods_id = intval($_GET['id']);
            }
            $map = ['goods_id' => $goods_id, 'goods_status' => 1];
            $row = M('b_goods_info')->where($map)->find();
            $row['brand_name'] = M('m_brand')->where(['id' => $goods_id])->getField('brand_name');
            exit(jsonSuccess($row));
        }else{
            exit(jsonError('20007','缺少必要的参数'));
        }
    }

    //商品类目
    public function category(){
        $res =  M('m_category_info')->field()->select();
        $return = list_to_tree($res,'id','pid');
        exit(jsonSuccess($return));
    }

    //商品库存
    public function stock(){
        $this->resource();
        $post = $this->getOauthRequest();
        if(!$post['goods_sn']){
            exit(jsonError('20007','缺少必要的参数'));
        }
        $user_id =$this->getTokenUserId($post['access_token']);
        if($user_id < 1){
            jsonError('21002','不存在此渠道信息');
        }
        $goods_number = M('s_show_goods')->where(['channel_id' => $user_id, 'goods_sn' => intval($post['goods_sn']),'status' => 1])->getField('goods_number');
        if(!$goods_number){
            jsonError('21003','商品不存在');
        }
        $return = ['goods_number' => $goods_number];
        exit(jsonSuccess($return));
    }

}