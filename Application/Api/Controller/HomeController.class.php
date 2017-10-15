<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Api\Controller;
use Think\Controller;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {

	/* 空操作，用于输出404页面 */
	public function _empty(){
		$this->redirect('Index/index');
	}

    protected function _initialize(){
        define('UID',is_login());
        /* 读取站点配置 */
        $config = api('Config/lists');
        C($config); //添加配置

        if(!C('WEB_SITE_CLOSE')){
            $this->error('站点已经关闭，请稍后访问~');
        }
        $this->assign('goods_list',$this->get_category_list());
        $this->assign('user_id',UID);
        $this->assign('user_name',session('user_auth.username'));
    }

	/* 用户登录检测 */
	protected function login(){
		/* 用户登录检测 */
		is_login() || $this->error('您还没有登录，请先登录！', U('User/login'));
	}

    /**
     * 多表分页
     */
    protected function page_list($model,$sql){
        if(is_string($model)){
            $model  =   M($model);
        }
        $REQUEST    =   (array)I('request.');
        if( isset($REQUEST['r']) ){
            $listRows = (int)$REQUEST['r'];
        }else{
            $listRows = C('LIST_ROWS_HOME') > 0 ? C('LIST_ROWS_HOME') : 20;
        }
        $total = count($model->query($sql));
        $page = new \Think\Page($total, $listRows, $REQUEST);
        if($total>$listRows){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $p =$page->show();
        $this->assign('_page', $p? $p: '');
        $this->assign('_total',$total);
        $sql = $sql.' limit '.$page->firstRow.','.$page->listRows;
        return $model->query($sql);
    }
    /**
     * 通用分页列表数据集获取方法
     *
     *  可以通过url参数传递where条件,例如:  index.html?name=asdfasdfasdfddds
     *  可以通过url空值排序字段和方式,例如: index.html?_field=id&_order=asc
     *  可以通过url参数r指定每页数据条数,例如: index.html?r=5
     *
     * @param sting|Model  $model   模型名或模型实例
     * @param array        $where   where查询条件(优先级: $where>$_REQUEST>模型设定)
     * @param array|string $order   排序条件,传入null时使用sql默认排序或模型属性(优先级最高);
     *                              请求参数中如果指定了_order和_field则据此排序(优先级第二);
     *                              否则使用$order参数(如果$order参数,且模型也没有设定过order,则取主键降序);
     *
     * @param array        $base    基本的查询条件
     * @param boolean      $field   单表模型用不到该参数,要用在多表join时为field()方法指定参数
     * @author 朱亚杰 <xcoolcc@gmail.com>
     *
     * @return array|false
     * 返回数据集
     */
    protected function lists ($model,$where=array(),$order='',$base = array(),$field=true){
        $options    =   array();
        $REQUEST    =   (array)I('request.');
        if(is_string($model)){
            $model  =   M($model);
        }

        $OPT        =   new \ReflectionProperty($model,'options');
        $OPT->setAccessible(true);

        $pk         =   $model->getPk();
        if($order===null){
            //order置空
        }else if ( isset($REQUEST['_order']) && isset($REQUEST['_field']) && in_array(strtolower($REQUEST['_order']),array('desc','asc')) ) {
            $options['order'] = '`'.$REQUEST['_field'].'` '.$REQUEST['_order'];
        }elseif( $order==='' && empty($options['order']) && !empty($pk) ){
            $options['order'] = $pk.' desc';
        }elseif($order){
            $options['order'] = $order;
        }
        unset($REQUEST['_order'],$REQUEST['_field']);

        $options['where'] = array_filter(array_merge( (array)$base, /*$REQUEST,*/ (array)$where ),function($val){
            if($val===''||$val===null){
                return false;
            }else{
                return true;
            }
        });
        if( empty($options['where'])){
            unset($options['where']);
        }
        $options      =   array_merge( (array)$OPT->getValue($model), $options );
        $total        =   $model->where($options['where'])->count();

        if( isset($REQUEST['r']) ){
            $listRows = (int)$REQUEST['r'];
        }else{
            $listRows = C('LIST_ROWS') > 0 ? C('LIST_ROWS') : 10;
        }
        $page = new \Think\Page($total, $listRows, $REQUEST);
        if($total>$listRows){
            $page->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $p =$page->show();
        $this->assign('_page', $p? $p: '');
        $this->assign('_total',$total);
        $options['limit'] = $page->firstRow.','.$page->listRows;

        $model->setProperty('options',$options);
        return $model->field($field)->select();
    }
    /**
     * 根据商品id获取总库存量
     */
    public function getStock($goods_id = 0){
        if(!$goods_id){
            return false;
        }else{
            $map['goods_id'] = array('eq',$goods_id);
        }
        $arr = M('b_warehouse_info')->field('stock,lock')->where($map)->select();
        $num = 0;
        if($arr){
            foreach($arr as $key=>$val){
                $num += $val['stock'] - $val['lock'];
            }
        }
        return $num;
    }
    /**
     * 获取商品图片
     */
    function getGoodsImg($goods_id = '',$h = 210,$w = 210){
        if(!$goods_id){
            return false;
        }
        $map['goods_id'] = $goods_id;
        $list = M('b_goods_picture')->where($map)->order('sort ASC')->limit(0,6)->select();
        foreach($list as $v){
            $lists[$v['type']][] = $v['pic_url']."?x-oss-process=image/resize,m_pad,h_".$h.",w_".$w;
        }
        return $lists;
    }
    //获取商品分类
    function getCategory(){
        $res =  M('m_category_info')->field()->select();
        return list_to_tree($res,'id','pid');
    }

    function category(){
        $category_list = $this->getCategory();
        foreach($category_list as $zk=>$zv){
            $category_arr[] = M('m_category_info')->field('id')->where('pid='.$zv['id'])->select();
            foreach($category_arr as $k=>$v){
                foreach($v as $key=>$val){
                    $arr2[$k][$key]=$val['id'];
                }
                if($arr2){
                    foreach($arr2 as $f_k=>$f_v){
                        $map['c.id'] = array('in',$f_v);
                    }
                }
                $map['g.goods_status'] = 1;
                $map['g.is_onsale'] = 1;
                $category_list[$zk]['brand'] = M('b_goods_info')->alias('g')->field('b.id,b.brand_name,b.logo')->join('left join b_brand_info as b on g.brand_id = b.id')
                    ->join('left join m_category_info as c on g.category_id = c.id')->where($map)->group('g.brand_id')->select();
            }
        }
        foreach($category_list as $yk=>$yv){
            foreach($yv['brand'] as $xk=>$xv){
                $category_list[$yk]['brand'][$xk]['logo'] = $this->get_image_path($xv['logo']);
            }
        }
        return $category_list;
    }

    /**
     * 重新获得品牌图片相册的地址
     */
    public function get_image_path($image = '')
    {
        if (strpos($image, "http") === false) {
            $image = 'http://www.pgjk.com/data/brandlogo/' . $image;
        }

        $url = empty($image) ? 'http://www.pgjk.com/data/brandlogo/no_picture.gif' : $image;
        return $url;
    }

    /**
     * 各页面品牌列表显示
     */
    public function show_brand_list($district = '',$brand_id = 0){
        $map['status'] = 1;
        $map[$district] = array('neq',0);
        if(!empty($brand_id)){
            $map['ob.brand_id'] = $brand_id;
        }
        $sql = M()->table('operations_brand AS ob')
            ->field("ob.*,bi.brand_name,bi.logo as logo")
            ->join("b_brand_info AS bi ON bi.id = ob.brand_id",'left')
            ->where($map)
            ->order("$district ASC")
            ->buildSql();
        $brand_info = $this->page_list('b_goods_info',$sql);
        return $brand_info;
    }
    /**
     * 得到新订单号
     * @return string
     */
    public function get_order_sn()
    {
        $time = explode(" ", microtime());
        $time = $time[1] . ($time[0] * 1000);
        $time = explode(".", $time);
        $time = isset($time[1]) ? $time[1] : 0;
        $time = date('YmdHis') + $time;

        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);
        return $time . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
    /**
     * 返回当前用户购物车商品详情
     */
    public function getCartInfo(){
        $user_id = UID;
        $goods_id_str = I('goods_id') ? I('goods_id') : '';
        if(isset($goods_id_str) && !empty($goods_id_str)){
            $map['goods_id'] = array('in',explode(',',$goods_id_str));
        }
        $map['user_id'] = array('eq',$user_id);
        $map['number'] = array('gt',0);
        $lists = M('b_cart')->where($map)->select();
        $num = 0;
        $totle = 0;
        foreach($lists as $key=>$val){
            $lists[$key]['images'] = $this->getGoodsImg($val['goods_id'],50,50);
            $num += $val['number'];
            $totle += $val['price'] * $val['number'];
        }
        return array('num'=>$num,'totle'=>number_format($totle,2),'list'=>array_values($lists));
    }
    /**
     * 返回当前用户购物车分组商品详情
     */
    public function getCartInfoGroup(){
        $user_id = UID;
        $goods_id_str = I('goods_id') ? I('goods_id') : '';
        if(isset($goods_id_str) && !empty($goods_id_str)){
            if(is_array($goods_id_str)){
                $map['goods_id'] = array('in',$goods_id_str);
            }else{
                $map['goods_id'] = array('in',explode(',',$goods_id_str));
            }
        }
        $map['user_id'] = array('eq',$user_id);
        $map['number'] = array('gt',0);
        $lists = M('b_cart')->where($map)->select();
        if(empty($lists)){
            return array('error' => 1,'msg' => '购物车为空！');
        }
        $num = 0;
        $totle = 0;
        foreach($lists as $key=>$val){
            $list[$val['store_id']]['store_id'] = $val['store_id'];
            $list[$val['store_id']]['store_name'] = $this->getStoreName($val['store_id']);
            $list[$val['store_id']]['goods'][] = array(
                'user_id' => $val['user_id'],
                'goods_id' => $val['goods_id'],
                'goods_sn' => $val['goods_sn'],
                'number' => $val['number'],
                'brand_id' => $val['brand_id'],
                'goods_name' => $val['goods_name'],
                'goods_letter' => $val['goods_letter'],
                'price' => number_format($val['price'],2),
                'market_price' => $val['market_price'],
                'weight' => $val['weight'],
                'is_cross' => $val['is_cross'],
                'sum_price' => number_format($val['price'] * $val['number'],2),
                'images' => $this->getGoodsImg($val['goods_id'],70,70)
            );
            $num += $val['number'];
            $totle += $val['price'] * $val['number'];
        }
        return array('num'=>$num,'totle'=>number_format($totle,2),'list'=>array_values($list));
    }
    /**
     * 获取店铺信息
     */
    public function getStoreName($store_id = 0){
        $map['store_id'] = array('eq',$store_id);
        $res = M('b_store_info')->where($map)->getField('name');
        return $res;
    }
    /**
     * 获取店铺信息
     */
    public function getBrandName($brand_id = 0){
            $map['id'] = array('eq',$brand_id);
        $res = M('b_brand_info')->where($map)->getField('brand_name');
        return $res;
    }

    /*
     * 获取地区名字
     */
    public function get_region_name($region_id = 0){
        $region_name = M('p_region')->where(array('region_id'=>$region_id))->getField('local_name');
        return $region_name;
    }
	
	public function get_category_list(){
		$category_list = $this->category();

        foreach ($category_list as $k =>$v){
            $floor_index = $k+1;
            if($floor_index == 1){
                $floor = 'category_one';
            }elseif($floor_index == 2){
                $floor = 'category_two';
            }elseif($floor_index == 3){
                $floor = 'category_three';
            }elseif($floor_index == 4){
                $floor = 'category_four';
            }elseif($floor_index == 5){
                $floor = 'category_five';
            }elseif($floor_index == 6){
                $floor = 'category_six';
            }
            $arra = array();
            foreach ($v['brand'] as $ks => $vs){
                $brand = $this->show_brand_list($floor,$vs['id']);
                if(count($brand) == 0){
                    unset($category_list[$k]['brand']);
                }else{
                    unset($category_list[$k]['brand']);
                    $arra = $brand;
                }
            }
            $category_list[$k]['brand'] = $arra;
        }
		return $category_list;

	}
	/*
	 * 获取国家列表
	 */
	public function get_country_list(){
	    $country_list = M('m_country');
	    $list =  $country_list->select();
	    return $list;
    }


    /**
     * 返回该品类下所有品牌，传入品类id和品牌id
     */
    function getBrandList($id = '',$brand_id = ''){
        if(!$id && !$brand_id) return false;
        if($brand_id){
            $map['id'] = array('eq',$brand_id);
            $map['status'] = array('eq',1);
            $list = M('b_brand_info')->field('id as brand_id,brand_name,brand_letter,logo,web_url')->where($map)->select();
            foreach($list as $key=>$val){
                $list[$key]['logo'] = $this->get_image_path($val['logo']);
            }
            return $list;
        }else{
            $category = D('Categorymall');
            $map['gi.category_id'] = array('in',$category->getChild($id));
            $map['gi.goods_status'] = array('eq',1);
            $map['_string'] = " gi.brand_id is not NULL";
            $list = M()->table('b_goods_info AS gi')
                ->field('gi.brand_id,bi.brand_name,bi.brand_letter,bi.logo,bi.web_url')
                ->join('b_brand_info AS bi ON bi.id = gi.brand_id','left')
                ->where($map)
                ->group('gi.brand_id')
                ->select();
            foreach($list as $key=>$val){
                $list[$key]['logo'] = $this->get_image_path($val['logo']);
            }
            return $list;
        }
    }
    /*
     * 返回根据国家馆下面的品牌列表
     */
    function  getBrandListByCountry($country_id = 0){
        $map['country'] = array('eq',$country_id);
        $map['status'] = array('eq',1);
        $list = M('b_brand_info')->distinct(true)->field('id as brand_id,brand_name,brand_letter,logo,web_url')->where($map)->select();
        if($list){
            foreach($list as $key=>$val){
                $list[$key]['logo'] = $this->get_image_path($val['logo']);
            }
            return $list;
        }else{
            return array();
        }
    }

    /*
     * 获取所有品牌列表
     */
    function getAllBrandList(){
        $map['status'] = array('eq',1);
        $list = M('b_brand_info')->distinct(true)->field('id as brand_id, brand_name,brand_letter,logo,web_url')->where($map)->select();
        if($list){
            foreach($list as $key=>$val){
                $list[$key]['logo'] = $this->get_image_path($val['logo']);
            }
            return $list;
        }else{
            return array();
        }
    }
    /**
     * 返回用户ID
     */
    public function getUid(){
        echo UID;exit;
    }
}
