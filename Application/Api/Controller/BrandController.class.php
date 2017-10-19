<?php

namespace Api\Controller;


class BrandController extends OauthController
{
    public function lists(){

        $page = $_POST['page']?$_POST['page'] : 1;
        $limit  = $_POST['limit'] ? $_POST['limit'] : 10;
        if ($limit > 50){
            $limit = 50;
        }
        $map['status'] = 1;
        $order = $_POST['order'] ? $_POST['order'] : 'brand_id desc,status';
        
        $count = M('m_brand')->where($map)->count();
        $num = ceil($count / $limit);
        $brandList = M('m_brand')->where($map)->order($order)->limit(($page - 1) * $limit,$limit)->select();
        if(!$brandList){
            exit(jsonError('21003','Nothing Found!'));
        }
        $param = [
            'currentPage' => $page,
            'totalPage' => $num
        ];
        exit(jsonSuccess($brandList,$param));
    }

    
}