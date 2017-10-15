<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/7
 * Time: 16:48
 */

namespace Business\Controller;


class BusinessChannelController extends AdminController
{
    //计划列表
    public function index()
    {
        $keywords_val = I('keywords_val');
        $status_val = I('status_val');
        if($keywords_val){
            $map['id|name|desc']    =   array('like', '%'.(string)$keywords_val.'%');
        }
        if($status_val != null){
            $map['_string'] = 'status = '.$status_val;
        }else{
            $map['_string'] = 'status not in(-1)';
        }
        $channel_list = M('b_operate_plan')->where($map)->select();
        foreach($channel_list as $k=>$v){
            $channel_list[$k]['start_time'] =$v['start_time']?date('Y-m-d H:i:s',$v['start_time']):'';
            $channel_list[$k]['end_time'] =$v['end_time']?date('Y-m-d H:i:s',$v['end_time']):'';
            $channel_list[$k]['x_type'] = $this->get_type($v['type']);
            $channel_list[$k]['x_exce_status'] = $this->get_exce_status($v['exce_status']);
        }

//        echo '<pre>';print_r($channel_list);exit;
        $this->assign('_list', $channel_list);
        $this->assign('status', array('0'=>'禁用','1'=>'正常'));
        $this->assign('act','index');
        $this->meta_title = '计划列表';
        $this->display('index');
    }

    public function look()
    {
        $id = I('id') ? I('id') : '';
        $map['id'] = $id;
        $arr = M('b_operate_plan')->where($map)->find();
        $arr['start_time'] = $arr['start_time']?date('Y-m-d H:i:s',$arr['start_time']):'';
        $arr['end_time'] = $arr['end_time']?date('Y-m-d H:i:s',$arr['end_time']):'';
        $arr['x_type'] = $this->get_type($arr['type']);
        $arr['content'] = json_decode($arr['content'],true);
//        echo '<pre>';print_r($arr);exit;
        $this->assign('_arr', $arr);
        $this->assign('_content', $arr['content']);
        $this->assign('act','look');
        $this->meta_title = '计划详情';
        $this->display('look');
    }

    public function add()
    {
        header("Content-type:text/html;charset=utf-8");
//        M('b_goods_info_log')->where('goods_status = 1')->delete();
//        $uid = UID;
//        $goods_list = $this->get_channel();
//        echo '<pre>';print_r($goods_list);exit;
        if(IS_POST){
//            echo '<pre>';print_r($_POST);
            $id_arr = I('channel_id');
            $level = I('level');
            $data['name'] = I('plan');
            $data['desc'] = I('plan_desc');
            $data['start_time'] = strtotime('2017-01-01 00:00:00');
            $data['end_time'] = strtotime('2017-12-31 23:59:59');
            if($id_arr){
                foreach($id_arr as $i_k=>$i_v){
                    $channel_arr[$i_v] = M('m_channel_info')->field('id,channel_name,channel_logo,desc')->where('id='.$i_v)->find();
                    $channel_arr[$i_v]['level'] = M('m_channel_level')->field('id,column_name')->where('channel_id='.$i_v)->select();
                }
            }
            foreach($channel_arr as $z_k=>$z_v){
                foreach($z_v['level'] as $y_k=>$y_v){
                    if(!in_array($y_v['id'],$level)){
                        unset($channel_arr[$z_k]['level'][$y_k]);
                    }
                }
            }
//            echo '<pre>';print_r($channel_arr);exit;
            $data['content'] = json_encode($channel_arr);
            $data['check_status'] = 1;
            $data['type'] = 1;
            $data['pay_status'] = 0;
            $data['exce_status'] = 1;
            $data['status'] = 1;
            $res = M('b_operate_plan')->add($data);
            if ($res !== false) {
                $this->success('新增成功！', U('Index'));
            } else {
                $this->error('添加失败！', U('Index'));
            }
        }else{
//            $this->assign('_goods_list', $this->get_goods());
//            $this->assign('_goods_type'  ,getCategory());
            $this->assign('_channel'  ,$this->get_channel());
//            $this->assign('_status', array('0'=>'单次计划','1'=>'周期计划'));
//            $this->assign('_cycle_status', array('2'=>'每天','3'=>'每周','4'=>'每月(天)','5'=>'每月(周)'));
            $this->assign('act','add');
            $this->meta_title = '添加计划';
            $this->display('adds');
        }
    }

    public function edit()
    {
        header("Content-type:text/html;charset=utf-8");
        $id = I('id') ? I('id') : '';
//        $goods_list = $this->get_channel();
//        echo '<pre>';print_r($goods_list);exit;
        if(IS_POST){
            $b_id = I('pid');
            $level = I('level');
            $id_arr = I('channel_id');
            $data['name'] = I('plan');
            $data['desc'] = I('plan_desc');
            $data['start_time'] = strtotime('2017-01-01 00:00:00');
            $data['end_time'] = strtotime('2017-12-31 23:59:59');
//            echo '<pre>';print_r($id_arr);exit;
            if($id_arr){
                foreach($id_arr as $i_k=>$i_v){
                    $channel_arr[$i_v] = M('m_channel_info')->field('id,channel_name,channel_logo,desc')->where('id='.$i_v)->find();
                    $channel_arr[$i_v]['level'] = M('m_channel_level')->field('id,column_name')->where('channel_id='.$i_v)->select();
                }
            }

            foreach($channel_arr as $z_k=>$z_v){
                foreach($z_v['level'] as $y_k=>$y_v){
                    if(!in_array($y_v['id'],$level)){
                        unset($channel_arr[$z_k]['level'][$y_k]);
                    }
                }
            }
			echo '<pre>';print_r($channel_arr);exit;
            $data['content'] = json_encode($channel_arr);
            $data['check_status'] = 1;
            $data['type'] = 1;
            $data['pay_status'] = 0;
            $data['exce_status'] = 1;
            $data['status'] = 1;
            $res = M('b_operate_plan')->where('id='.$b_id)->save($data);
            if ($res !== false) {
                $this->success('编辑成功！', U('Index'));
            } else {
                $this->error('编辑失败！', U('Index'));
            }
        }else{
            $row = M('b_operate_plan')->where(array('id'=>$id))->find();
            $arr['content'] = json_decode($row['content'],true);
//            echo '<pre>';print_r($arr['content']);exit;
            $this->assign('_row', $row);
            $this->assign('_cont', $arr['content']);
//            $this->assign('_goods_list', $this->get_goods());
//            $this->assign('_goods_type'  ,getCategory());
            $this->assign('_channel'  ,$this->get_channel());
//            $this->assign('_status', array('0'=>'单次计划','1'=>'周期计划'));
//            $this->assign('_cycle_status', array('2'=>'每天','3'=>'每周','4'=>'每月(天)','5'=>'每月(周)'));
            $this->assign('act','edit');
            $this->meta_title = '编辑计划';
            $this->display('adds');
        }
    }

    public function put_goods()
    {
        $goods_id = I('goods_id');
        $goods_id = explode(',',$goods_id);
        $map['goods_id'] = array('in',$goods_id);
        if($goods_id){
            $goods_list = M('b_goods_info')->where($map)->select();
            foreach($goods_list as $k=>$v){
                $goods_log = M('b_goods_info_log')->where('goods_id='.$v['goods_id'])->find();
                if($goods_log){
                    $data['add_date'] = time();
                    M('b_goods_info_log')->where('goods_id='.$v['goods_id'])->save($data);
                }else {
                    $data['goods_id'] = $v['goods_id'];
                    $data['store_id'] = $v['store_id'];
                    $data['brand_id'] = $v['brand_id'];
                    $data['goods_name'] = $v['goods_name'];
                    $data['goods_letter'] = $v['goods_letter'];
                    $data['goods_sn'] = $v['goods_sn'];
                    $data['category_id'] = $v['category_id'];
                    $data['price'] = $v['price'];
                    $data['market_price'] = $v['market_price'];
                    $data['is_onsale'] = $v['is_onsale'];
                    $data['add_date'] = $v['add_date'];
                    $data['goods_status'] = $v['goods_status'];
                    M('b_goods_info_log')->add($data);
                }
            }


        }
    }
    //获取渠道信息
    public function get_channel()
    {
        $channel_list = M('m_channel_info')->select();
        foreach($channel_list as $k=>$val){
            $channel_list[$k]['level'] = $this->get_level($val['id']);
        }
        return $channel_list;
    }
    //获取渠道栏目
    public function get_level($id)
    {
        $level_list = M('m_channel_level')->where('channel_id='.$id)->select();
        return $level_list;
    }
    //获取商品信息
    public function get_goods(){
        $uid = UID;
        $goods_list = M('b_goods_info')->alias('b')->field('b.*,m.uid')->join('left join b_member_info as m on b.store_id = m.store_id')->where(array('b.is_onsale'=>0,'b.goods_status'=>1,'m.uid'=>$uid))->select();
        return $goods_list;
    }

    //获取商品详情
    public function get_goods_info(){
        $uid = UID;
        $category_id = I('category_id');
        if($category_id){
            $goods_list = M('b_goods_info')->alias('b')->field('b.*,m.uid')->join('left join b_member_info as m on b.store_id = m.store_id')->where(array('b.is_onsale'=>0,'b.goods_status'=>1,'b.category_id'=>$category_id,'m.uid'=>$uid))->select();
            $goods_info_log = M('b_goods_info_log')->where('category_id='.$category_id)->select();
            if(!empty($goods_info_log)){
                foreach($goods_list as $g_k=>$g_v){
                    foreach($goods_info_log as $l_k=>$l_v){
                        if($l_v['goods_id'] == $g_v['goods_id']){
                            unset($goods_list[$g_k]);
                        }
                    }
                }
            }
        }else{
            $goods_list = M('b_goods_info')->alias('b')->field('b.*,m.uid')->join('left join b_member_info as m on b.store_id = m.store_id')->where(array('b.is_onsale'=>0,'b.goods_status'=>1,'m.uid'=>$uid))->select();
            $goods_info_log = M('b_goods_info_log')->select();
            if(!empty($goods_info_log)){
                foreach($goods_list as $g_k=>$g_v){
                    foreach($goods_info_log as $l_k=>$l_v){
                        if($l_v['goods_id'] == $g_v['goods_id']){
                            unset($goods_list[$g_k]);
                        }
                    }
                }
            }
        }
        echo json_encode(array('status'=>1,'data'=>$goods_list));
    }
    //获取缓存商品详情
    public function get_goodslog_info(){
        $uid = UID;
        $category_id = I('category_id');
        if($category_id){
            $goods_list = M('b_goods_info_log')->alias('b')->field('b.*,m.uid')->join('left join b_member_info as m on b.store_id = m.store_id')->where(array('b.is_onsale'=>0,'b.goods_status'=>1,'b.category_id'=>$category_id,'m.uid'=>$uid))->select();
        }else{
            $goods_list = M('b_goods_info_log')->alias('b')->field('b.*,m.uid')->join('left join b_member_info as m on b.store_id = m.store_id')->where(array('b.is_onsale'=>0,'b.goods_status'=>1,'m.uid'=>$uid))->select();
        }
        echo json_encode(array('status'=>1,'data'=>$goods_list));
    }
    //商品售价升降序
    public function get_sale(){
        $uid = UID;
        $desc = I('desc');
        if($desc == 'desc'){
            $goods_list = M('b_goods_info')->alias('b')->field('b.*,m.uid')->join('left join b_member_info as m on b.store_id = m.store_id')->where(array('b.is_onsale'=>0,'b.goods_status'=>1,'m.uid'=>$uid))->order('price desc')->select();
        }else{
            $goods_list = M('b_goods_info')->alias('b')->field('b.*,m.uid')->join('left join b_member_info as m on b.store_id = m.store_id')->where(array('b.is_onsale'=>0,'b.goods_status'=>1,'m.uid'=>$uid))->order('price asc')->select();
        }
        echo json_encode(array('status'=>1,'data'=>$goods_list));
    }

    //商品缓存售价升降序
    public function get_sales(){
        $uid = UID;
        $desc = I('desc');
        if($desc == 'desc'){
            $goods_list = M('b_goods_info_log')->alias('b')->field('b.*,m.uid')->join('left join b_member_info as m on b.store_id = m.store_id')->where(array('b.is_onsale'=>0,'b.goods_status'=>1,'m.uid'=>$uid))->order('price desc')->select();
        }else{
            $goods_list = M('b_goods_info_log')->alias('b')->field('b.*,m.uid')->join('left join b_member_info as m on b.store_id = m.store_id')->where(array('b.is_onsale'=>0,'b.goods_status'=>1,'m.uid'=>$uid))->order('price asc')->select();
        }

        echo json_encode(array('status'=>1,'data'=>$goods_list));
    }
    /* 返回状态名称，并附带样式 */
    public function get_type($type=''){
        switch($type){
            case 1; $status_cn = '仅一次';break;
            case 2; $status_cn = '每天';break;
            case 3; $status_cn = '每周';break;
            case 4; $status_cn = '每月(天)';break;
            case 5; $status_cn = '每月(周)';break;
        }
        return $status_cn;
    }

    public function get_exce_status($exce_status){
        switch($exce_status){
            case 1; $status_cn = '未开始';break;
            case 10; $status_cn = '待执行';break;
            case 20; $status_cn = '预热中';break;
            case 30; $status_cn = '进行中';break;
            case 40; $status_cn = '已完成';break;
        }
        return $status_cn;
    }

    /* 更新状态 */
    public function changestatus(){
        $ids = I('request.ids');
        $status = I('request.status');
        if(empty($ids)){
            $this->error('请选择要操作的数据！');
        }
        $where['id'] = array('in',$ids);
        if($status == 1){//账号启用
            $data = array('status'=>$status);
        }else if($status == 0){//禁用
            $data = array('status'=>$status);
        }else{//删除
            $data = array('status'=>-1);
        }
        M("b_operate_plan")->where($where)->save($data);
        $this->success('操作成功！');
    }
    public function del(){
        $id = I('id');
        $data['status'] = I('status');
        $res = M("b_operate_plan")->where('id='.$id)->save($data);
        if($res){
            $this->success('删除成功！', U('Index'));
        }else{
            $this->success('删除失败！', U('Index'));
        }


    }
}