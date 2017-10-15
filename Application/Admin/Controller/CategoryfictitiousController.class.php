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
 * 后台类目管理控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class CategoryFictitiousController extends AdminController {

    /**
     * 商城类目管理列表
     */
    public function index(){
        $tree = D('CategoryFictitious')->getTree(0,'id,name,sort,pid,status');
        $this->assign('tree', $tree);
        C('_SYS_GET_CATEGORY_TREE_', true); //标记系统获取类目树模板
        $this->meta_title = '商城虚拟类目管理';
        $this->display();
    }

    /**
     * 显示类目树，仅支持内部调
     * @param  array $tree 类目树
     */
    public function tree($tree = null){
        C('_SYS_GET_CATEGORY_TREE_') || $this->_empty();
        $this->assign('tree', $tree);
        $this->display('tree');
    }

    /* 编辑类目 */
    public function edit($id = null, $pid = 0){
        $Category = D('CategoryFictitious');

        if(IS_POST){ //提交表单
            if(false !== $Category->update()){
                $this->success('编辑成功！', U('index'));
            } else {
                $error = $Category->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $cate = '';
            if($pid){
                /* 获取上级类目信息 */
                $cate = $Category->info($pid, 'id,name,status');
                if(!($cate && 1 == $cate['status'])){
                    $this->error('指定的上级类目不存在或被禁用！');
                }
            }

            /* 获取类目信息 */
            $info = $id ? $Category->info($id) : '';

            $this->assign('info',       $info);
            $this->assign('category',   $cate);
            $this->meta_title = '编辑类目';
            $this->display();
        }
    }

    /* 新增类目 */
    public function add($pid = 0){
        $Category = D('CategoryFictitious');

        if(IS_POST){ //提交表单
            if(false !== $Category->update()){
                $this->success('新增成功！', U('index'));
            } else {
                $error = $Category->getError();
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        } else {
            $cate = array();
            if($pid){
                /* 获取上级类目信息 */
                $cate = $Category->info($pid, 'id,name,status');
                if(!($cate && 1 == $cate['status'])){
                    $this->error('指定的上级类目不存在或被禁用！');
                }
            }

            /* 获取类目信息 */
            $this->assign('category', $cate);
            $this->meta_title = '新增类目';
            $this->display('edit');
        }
    }

    /**
     * 删除一个类目
     * @author huajie <banhuajie@163.com>
     */
    public function remove(){
        $cate_id = I('id');
        if(empty($cate_id)){
            $this->error('参数错误!');
        }

        //判断该类目下有没有子类目，有则不允许删除
        $child = M('m_category_fictitious')->where(array('pid'=>$cate_id))->field('id')->select();
        if(!empty($child)){
            $this->error('请先删除该类目下的子类目');
        }

        //删除该类目信息
        $res = M('m_category_fictitious')->delete($cate_id);
        if($res !== false){
            //记录行为
            action_log('update_category', 'Categoryfictitious', $cate_id, UID);
            $this->success('删除类目成功！');
        }else{
            $this->error('删除类目失败！');
        }
    }

    /**
     * 移动类目
     * @author huajie <banhuajie@163.com>
     */
    public function move(){
        $to = I('post.to');
        $from = I('post.from');
        $res = M('m_category_fictitious')->where(array('id'=>$from))->setField('pid', $to);
        if($res !== false){
            $this->success('类目移动成功！', U('index'));
        }else{
            $this->error('类目移动失败！');
        }
    }
    /**
     * 设置一条或者多条数据的状态
     */
    public function setStatus($Model='m_category_fictitious'){

        $ids    =   I('request.ids');
        $status =   I('request.status');
        if(empty($ids)){
            $this->error('请选择要操作的数据');
        }

        $map['id'] = array('in',$ids);
        switch ($status){
            case -1 :
                $this->delete($Model, $map, array('success'=>'删除成功','error'=>'删除失败'));
                break;
            case 0  :
                $this->forbid($Model, $map, array('success'=>'禁用成功','error'=>'禁用失败'));
                break;
            case 1  :
                $this->resume($Model, $map, array('success'=>'启用成功','error'=>'启用失败'));
                break;
            default :
                $this->error('参数错误');
                break;
        }
    }

}
