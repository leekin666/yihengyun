<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Business\Controller;

/**
 * 后台品牌管理控制器
 */
class BrandController extends AdminController {
    /**
     * 品牌管理列表
     */
    public function index(){
        $keywords_val = I('keywords_val');
        $status_val = I('status_val');
        if($keywords_val){
            $map['brand_name|brand_letter|country|desc_cn|web_url'] = array('like', '%'.(string)$keywords_val.'%');
        }
        if($status_val != null){
            $map['_string'] = 'status in(1,10,20) and status = '.$status_val;
        }else{
            $map['_string'] = 'status in(1,10,20)';
        }
        $bid = M('b_member_info')->where(array('uid'=>UID))->getField('b_id');
        $map['store_id'] = array('eq',$bid);
        $brand_list = $this->lists('b_brand_info', $map,'id DESC,update_time DESC');
        foreach($brand_list as $k=>$v){
            $brand_list[$k]['country_name'] = $this->get_country_name($v['country']);
            $brand_list[$k]['x_status'] = $this->get_status($v['status']);
        }
        $this->assign('_list', $brand_list);
        $this->assign('status', array('1'=>'正常','10'=>'禁用','20'=>'平台冻结'));
        $this->assign('status_val', $status_val);
        $this->assign('keywords_val', $keywords_val);
        $this->meta_title = '品牌列表';
        $this->display();
    }

    /**
     * 添加
     */
    public function add(){
        $brand = D('Brand');
        if(IS_POST){
            $res = $brand->update();
            if($res !== false){
                $this->success('新增成功！',U('index'));
            }else{
                $error = $brand->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $bid = M('b_member_info')->where(array('uid'=>UID))->getField('b_id');
            if($bid){
                $store_info = M('b_store_info')->field('store_id,name')->where(array('b_id'=>$bid))->find();
            }
            $country_list = M('m_country')->field('id,name')->order('sort asc')->select();
            $this->assign('country_list',$country_list);
            $this->assign('store_info',$store_info);
            $this->meta_title = '新增品牌';
            $this->display('add');
        }
    }

    /* 编辑 */
    public function edit($id = null){
        $id = I('id') ? I('id') : 0;
        $brand = D('Brand');
        if(IS_POST){ //提交表单
            $res = $brand->update();
            if($res !== false){
                $this->success('编辑成功！',U('index'));
            }else{
                $error = $brand->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $row = M('b_brand_info')->where(array('id'=>$id))->find();
            $this->assign('row',$row);
            $bid = M('b_member_info')->where(array('uid'=>UID))->getField('b_id');
            if($bid){
                $store_info = M('b_store_info')->field('store_id,name')->where(array('b_id'=>$bid))->find();
            }
            $country_list = M('m_country')->field('id,name')->order('sort asc')->select();
            $this->assign('country_list',$country_list);
            $this->assign('store_info',$store_info);
            $this->meta_title = '编辑品牌';
            $this->display('add');
        }
    }

    /**
     * 查看
     */
    public function look(){
        $id = I('id') ? I('id') : 0;
        $row = M('b_brand_info')->where(array('id'=>$id))->find();
        $status_cn = $this->get_status($row['status']);
        $row['x_status'] = $status_cn;
        $rebut = M('rebut_log')->where(array('brand_id'=>$id))->order('create_time DESC')->select();
        //该店铺该品牌下商品数量
        $count = M('b_goods_info')->where(array('brand_id'=>$id))->count();
        $this->assign('count',$count);
        $this->assign('info',$row);
        $this->assign('rebut',$rebut);
        $this->meta_title = '查看详情';
        $this->display();
    }

    /**
     * 审核
     */
    public function check(){
        $keywords_val = I('keywords_val');
        $status_val = I('status_val');
        if($keywords_val){
            $map['brand_name|brand_letter|country|desc_cn|web_url'] = array('like', '%'.(string)$keywords_val.'%');
        }
        if($status_val != null){
            $map['_string'] = 'status in(0,-2) and status = '.$status_val;
        }else{
            $map['_string'] = 'status in(0,-2) and status <> -10';
        }
        $brand_info = $this->lists('b_brand_info', $map,'update_time DESC');
        foreach($brand_info as $k=>$v){
            $brand_info[$k]['x_status'] = $this->get_status($v['status']);
        }
        $this->assign('_list', $brand_info);
        $this->assign('status', array('0'=>'审核中','-2'=>'驳回'));
        $this->assign('status_val', $status_val);
        $this->assign('keywords_val', $keywords_val);
        $this->meta_title = '品牌审核';
        $this->display();
    }

    /* 回收站列表 */
    public function recycle(){
        $keywords_val = I('keywords_val');
        $status_val = I('status_val');
        if($keywords_val){
            $map['id|brand_name|brand_letter|country|desc_cn|web_url']    =   array('like', '%'.(string)$keywords_val.'%');
        }
        if($status_val != null){
            $map['_string'] = 'status = -1 and status = '.$status_val;
        }else{
            $map['_string'] = 'status = -1 and status <> -10';
        }
        $brand_list = $this->lists('b_brand_info', $map,'id DESC,update_time DESC');
        foreach($brand_list as $k=>$v){
            $brand_list[$k]['country_name'] = $this->get_country_name($v['country']);
            $brand_list[$k]['x_status'] = $this->get_status($v['status']);
        }
        $this->assign('_list', $brand_list);
        $this->assign('status_val', $status_val);
        $this->assign('keywords_val', $keywords_val);
        $this->meta_title = '回收站';
        $this->display();
    }

    /* 更新状态 */
    public function changestatus(){
        $ids = I('request.ids');
        $status = I('request.status');
        if(empty($ids)){
            $this->error('请选择要操作的数据！');
        }
        $where['id'] = array('in',$ids);
        if($status == 10){//店铺禁用
            $where['status'] = array('eq',1);
            $data = array('status'=>$status);
        }else if($status == -1){//删除
            $where['status'] = array('in',array(1,10));
            $data = array('status'=>$status,'delete_time'=>time());
        }else{//恢复
            $where['status'] = array('neq',20);
            $data = array('status'=>$status);
        }
        M("b_brand_info")->where($where)->setField($data);
        $this->success('操作成功！');

    }

    /**
     * ajax上传图片到oss
     */
    public function up_img_oss($path = ''){
        $path = I('tmp_img');
        $res = oss_upload($path);
        echo $res;exit;
    }

    /*
     * 删除OSS图片接口
     */
    public function removePic(){
        $path = I('path')?I('path'):'';
        $path = ltrim($path,'/');
        $id = I('id')?I('id'):0;
        $condition['id'] = $id;
        $data['logo'] = '';
       if(!oss_delet_object(array($path))){
           if($id){
               M("b_brand_info")->where($condition)->save($data);
               action_log('delete_images','删除OSS图片'.$path,$id,$id);
               echo 0;exit;
           }
           echo 0;exit;
       }else{
           echo 1;exit;
       };
    }

    //判断品牌唯一性
    public function check_brand(){
        $map['store_id'] = I('store_id') ? I('store_id') : 0;
        $map['brand_name'] = I('brand_name') ? I('brand_name') : '';
        $map['status'] = array('not in',array('-10'));
        $res = M('b_brand_info')->where($map)->select();
        if($res){
            echo 'fail';exit;
        }else{
            echo 'success';exit;
        }
    }

    /* 返回状态名称，并附带样式 */
    function get_status($status = 0){
        switch($status){
            case 1; $status_cn = '<span style="background: #3c8dbc;color:#fff;padding:1px 4px;"">正常</span>';break;
            case 10; $status_cn = '<span style="background: #ffc947;color:#fff;padding:1px 4px;"">禁用</span>';break;
            case 20; $status_cn = '<span style="background: #ff876e;color:#fff;padding:1px 4px;"">平台冻结</span>';break;
            case -1; $status_cn = '<span style="background: #ff3d5f;color:#fff;padding:1px 4px;"">删除</span>';break;
        }
        return $status_cn;
    }
    /**
     * 获取国家名称
     */
    function get_country_name($country_id = 0){
        if(!$country_id) return false;
        return M('m_country')->where(array('id'=>$country_id))->getField('name');
    }
}
