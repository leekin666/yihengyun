<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/7
 * Time: 19:17
 */
namespace Business\Controller;
use Business\Model\AuthRuleModel;
use Business\Model\AuthGroupModel;

class BusinessAuthController extends AdminController{

    /**
     * 后台节点配置的url作为规则存入auth_rule
     * 执行新节点的插入,已有节点的更新,无效规则的删除三项任务
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function updateRules(){
        //需要新增的节点必然位于$nodes
        $nodes    = $this->returnNodes(false);

        $AuthRule = M('BAuthRule');
        $map      = array('type'=>array('in','1,2'));//status全部取出,以进行更新
        //需要更新和删除的节点必然位于$rules
        $rules    = $AuthRule->where($map)->order('name')->select();

        //构建insert数据
        $data     = array();//保存需要插入和更新的新节点
        foreach ($nodes as $value){
            $temp['name']   = $value['url'];
            $temp['title']  = $value['title'];
            if($value['pid'] >0){
                $temp['type'] = AuthRuleModel::RULE_URL;
            }else{
                $temp['type'] = AuthRuleModel::RULE_MAIN;
            }
            $temp['status']   = 1;
            $data[strtolower($temp['name'].$temp['module'].$temp['type'])] = $temp;//去除重复项
        }

        $update = array();//保存需要更新的节点
        $ids    = array();//保存需要删除的节点的id
        foreach ($rules as $index=>$rule){
            $key = strtolower($rule['name'].$rule['module'].$rule['type']);
            if ( isset($data[$key]) ) {//如果数据库中的规则与配置的节点匹配,说明是需要更新的节点
                $data[$key]['id'] = $rule['id'];//为需要更新的节点补充id值
                $update[] = $data[$key];
                unset($data[$key]);
                unset($rules[$index]);
                unset($rule['condition']);
                $diff[$rule['id']]=$rule;
            }elseif($rule['status']==1){
                $ids[] = $rule['id'];
            }
        }
        if ( count($update) ) {
            foreach ($update as $k=>$row){
                if ( $row!=$diff[$row['id']] ) {
                    $AuthRule->where(array('id'=>$row['id']))->save($row);
                }
            }
        }
        if ( count($ids) ) {
            $AuthRule->where( array( 'id'=>array('IN',implode(',',$ids)) ) )->save(array('status'=>-1));
            //删除规则是否需要从每个用户组的访问授权表中移除该规则?
        }
        if( count($data) ){
            $AuthRule->addAll(array_values($data));
        }
        if ( $AuthRule->getDbError() ) {
            trace('['.__METHOD__.']:'.$AuthRule->getDbError());
            return false;
        }else{
            return true;
        }
    }
    /**
     * 商家权限首页
     */
    public function index(){
        if(is_administrator()){
            $list = $this->lists('BAuthGroup',array(),'id asc');
        }else{
            $list = $this->lists('BAuthGroup',array('b_id'=>$this->get_business_id(UID)),'id asc');
        }
        $list = int_to_string($list);
        $this->assign( '_list', $list );
        $this->assign( '_use_tip', true );
        $this->meta_title = '商家角色管理';
        $this->display();
    }
    /**
     * 创建管理员用户组
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function add(){
        if ( empty($this->auth_group) ) {
            $this->assign('auth_group',array('title'=>null,'id'=>null,'description'=>null,'rules'=>null,));//排除notice信息
        }
        $this->meta_title = '新增角色';
        $this->display('edit');
    }

    /**
     * 编辑管理员用户组
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function edit(){
        $this->updateRules();
        $auth_group = M('BAuthGroup')->where( array('type'=>AuthGroupModel::TYPE_USER) )
            ->find( (int)$_GET['group_id'] );
        $node_list   = $this->returnRoleNodes();
        $map         = array('type'=>AuthRuleModel::RULE_MAIN,'status'=>1);
        $main_rules  = M('BAuthRule')->where($map)->getField('name,id');
        $map         = array('type'=>AuthRuleModel::RULE_URL,'status'=>1);
        $child_rules = M('BAuthRule')->where($map)->getField('name,id');
        $this->assign('main_rules', $main_rules);
        $this->assign('auth_rules', $child_rules);
        $this->assign('node_list',  $node_list);
        $this->assign('auth_group', $auth_group);
        $this->assign('this_group', $auth_group[(int)$_GET['group_id']]);
        $this->meta_title = '编辑角色';
        $this->display();
    }

    /**
     * 管理员用户组数据写入/更新
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function writeGroup(){
        if(isset($_POST['rules'])){
            sort($_POST['rules']);
            $_POST['rules']  = implode( ',' , array_unique($_POST['rules']));
        }
        $AuthGroup       =  D('AuthGroup');
        $data = $AuthGroup->create();
        if ( $data ) {
            $this->updateRules();
            $data['b_id'] = $this->get_business_id(UID);
            if(!$data['b_id']){
                $this->error('操作失败，商家ID不能为空');
            }else{
                if ( empty($data['id']) ) {
                    $r = $AuthGroup->add($data);
                }else{
                    $r = $AuthGroup->save($data);
                }
                if($r===false){
                    $this->error('操作失败'.$AuthGroup->getError());
                } else{
                    $this->success('操作成功!',U('index'));
                }
            }
        }else{
            $this->error('操作失败'.$AuthGroup->getError());
        }
    }

    /**
     * 状态修改
     * @author 朱亚杰 <zhuyajie@topthink.net>
     */
    public function changeStatus($method=null){
        if ( empty($_REQUEST['id']) ) {
            $this->error('请选择要操作的数据!');
        }
        switch ( strtolower($method) ){
            case 'forbidgroup':
                $this->forbid('BAuthGroup');
                break;
            case 'resumegroup':
                $this->resume('BAuthGroup');
                break;
            case 'deletegroup':
                $this->delete('BAuthGroup');
                break;
            default:
                $this->error($method.'参数非法');
        }
    }

    public function tree($tree = null){
        $this->assign('tree', $tree);
        $this->display('tree');
    }
    /**
     * 根据登录用户查找商家id
     */
    public function get_business_id($user_id = 0){
        if(!$user_id){
            return false;
        }
        return M('b_member_info')->where(array('uid'=>$user_id))->getfield('b_id');
    }
    /**
     * 返回该角色拥有的角色节点
     */
    private function returnRoleNodes($tree = true){
        static $tree_nodes = array();
        if ( $tree && !empty($tree_nodes[(int)$tree]) ) {
            return $tree_nodes[$tree];
        }
        //当前登录用户的角色信息
        $group = M('b_auth_group')->where(array('id'=>$this->userGroupid()))->find();
        $have_nodes = explode(',',$group['rules']);
        $map['id'] = array('in',$have_nodes);
        $url_arr = M('b_auth_rule')->where($map)->getField('name',true);
        foreach($url_arr as $v){
            $short_url[] = str_replace('Business/','',$v);
        }
        $where['url'] = array('in',$short_url);
        if((int)$tree){
            $list = M('b_menu')->field('id,pid,title,url,tip,hide')->where($where)->order('sort asc')->select();
            foreach ($list as $key => $value) {
//                if( stripos($value['url'],MODULE_NAME)!==0 ){
                    $list[$key]['url'] = MODULE_NAME.'/'.$value['url'];
//                }
            }
            $nodes = list_to_tree($list,$pk='id',$pid='pid',$child='operator',$root=0);
            foreach ($nodes as $key => $value) {
                if(!empty($value['operator'])){
                    $nodes[$key]['child'] = $value['operator'];
                    unset($nodes[$key]['operator']);
                }
            }
        }else{
            $nodes = M('b_menu')->field('title,url,tip,pid')->where($where)->order('sort asc')->select();
            foreach ($nodes as $key => $value) {
//                if( stripos($value['url'],MODULE_NAME)!==0 ){
                    $nodes[$key]['url'] = MODULE_NAME.'/'.$value['url'];
//                }
            }
        }
        $tree_nodes[(int)$tree]   = $nodes;
        return $nodes;
    }
}