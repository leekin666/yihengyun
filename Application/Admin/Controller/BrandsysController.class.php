<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;

/**
 * 后台品牌管理控制器
 */
class BrandsysController extends AdminController {
    /**
     * 品牌管理列表
     */
    public function index(){
        $keywords_val = I('keywords_val');
        $status_val = I('status_val');
        if($keywords_val){
            $map['bi.brand_name|bi.brand_letter|bi.country|bi.desc_cn|bi.web_url']    =   array('like', '%'.(string)$keywords_val.'%');
        }
        if($status_val != null){
            $map['_string'] = 'bi.status <> -10 and bi.status = '.$status_val;
        }else{
            $map['_string'] = 'bi.status <> -10';
        }
        $sql = M('b_brand_info')->table('b_brand_info as bi')
            ->field('bi.*,si.name as store_name')
            ->join('b_store_info as si on bi.store_id = si.store_id','left')
            ->where($map)
            ->order('id DESC,update_time DESC')
            ->buildSql();
        $bid = M('b_member_info')->where(array('uid'=>UID))->getField('b_id');
        $map['store_id'] = array('eq',$bid);
        $brand_list = $this->page_list('b_brand_info',$sql);
        foreach($brand_list as $k=>$v){
            $brand_list[$k]['country_name'] = $this->get_country_name($v['country']);
            $brand_list[$k]['x_status'] = $this->get_status($v['status']);
        }
        $this->assign('_list', $brand_list);
        $this->assign('status', array('1'=>'正常','10'=>'商家禁用','20'=>'平台冻结'));
        $this->assign('status_val', $status_val);
        $this->assign('keywords_val', $keywords_val);
        $this->assign('act','index');
        $this->meta_title = '品牌列表';
        $this->display();
    }
    /**
     * 查看
     */
    public function look(){
        $id = I('id');
        $info = M('b_brand_info')->where(array('id'=>$id))->find();
        $status_cn = $this->get_status($info['status']);
        $info['status_cn'] = $status_cn;
        $this->assign('info',$info);
        $this->assign('act',I('act'));
        $this->meta_title = '查看详情';
        $this->display();
    }
    /**
     * 审核
     */
    public function check(){
        $keywords_val = I('keywords_val');
        if($keywords_val){
            $map['bi.id|bi.brand_name|bi.brand_letter|bi.country|bi.desc_cn|bi.web_url']    =   array('like', '%'.(string)$keywords_val.'%');
        }
        $map['bi.status'] = array('eq',0);
        $sql = M('b_brand_info')->table('b_brand_info as bi')
            ->field('bi.*,si.name as store_name')
            ->join('b_store_info as si on bi.store_id = si.store_id','left')
            ->where($map)
            ->order('update_time DESC')
            ->buildSql();
        $brand_list = $this->page_list('b_brand_info',$sql);
        $this->assign('_list', $brand_list);
        $this->assign('keywords_val', $keywords_val);
        $this->assign('act','check');
        $this->meta_title = '品牌审核';
        $this->display();
    }
    /* 回收站 */
    public function recycle(){
        $keywords_val = I('keywords_val');
        if($keywords_val){
            $map['bi.id|bi.brand_name|bi.brand_letter|bi.country|bi.desc_cn|bi.web_url']    =   array('like', '%'.(string)$keywords_val.'%');
        }
        $map['bi.status'] = array('eq',-1);
        $sql = M('b_brand_info')->table('b_brand_info as bi')
            ->field('bi.*,si.name as store_name')
            ->join('b_store_info as si on bi.store_id = si.store_id','left')
            ->where($map)
            ->order('update_time DESC')
            ->buildSql();
        $brand_list = $this->page_list('b_brand_info',$sql);
        $bid = M('b_member_info')->where(array('uid'=>UID))->getField('b_id');
        $map['store_id'] = array('eq',$bid);
        foreach($brand_list as $k=>$v){
            $brand_list[$k]['country_name'] = $this->get_country_name($v['country']);
            $brand_list[$k]['x_status'] = $this->get_status($v['status']);
        }
        $this->assign('_list', $brand_list);
        $this->assign('keywords_val', $keywords_val);
        $this->assign('act','recycle');
        $this->meta_title = '回收站';
        $this->display();
    }
    /**
     * 查看页面单个冻结
     */
    public function freeze(){
        $id = I('id') ? I('id') : 0;
        $map['id'] = $id;
        $map['status'] = array('in',array('1','10'));
        $data['status'] = 20;//平台冻结
        $res = M("b_brand_info")->where($map)->save($data);
        if($res !== false){
            $this->success('操作成功',U('index'));
        }else{
            $this->error('操作失败');
        }
    }
    /* 更新状态 */
    public function changestatus(){
        $ids = I('request.ids');
        $status = I('request.status');
        if(!$ids){
            $this->error('请选择要操作的数据！');
        }
        $where['id'] = array('in',$ids);
        if($status == 20){//平台冻结
            $where['status'] = array('in',array('1','10'));
        }else if($status == 0){//平台解冻
            $where['status'] = array('eq','20');
        }

        M("b_brand_info")->where($where)->setField(array('status'=>$status));
        $this->success('操作成功');
    }

    /* 驳回 */
    public function rebut(){
        $ids = I('ids');
        $text = I('text');
        if(!$ids){
            $this->error('请选择要操作的数据！');
        }
        foreach($ids as $k=>$v){
            $data = array(
                'brand_id' => $v,
                'rebut' => trim($text),
                'create_time' => time(),
                'uid' => UID
            );
            M('rebut_log')->add($data);
        }
        $map['id'] = array('in',$ids);
        $res = M('b_brand_info')->where($map)->setField(array('status'=>'-2'));
        if($res){
            echo 'success';exit;
        }else{
            echo 'fail';exit;
        }
    }

    /* 返回状态名称，并附带样式 */
    public function get_status($status = 0){
        switch($status){
            case 1; $status_cn = '<span style="background: #3c8dbc;color:#fff;padding:1px 4px;"">正常</span>';break;
            case 10; $status_cn = '<span style="background: #ffc947;color:#fff;padding:1px 4px;"">商家禁用</span>';break;
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
