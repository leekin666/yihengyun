<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/7
 * Time: 16:48
 */

namespace Business\Controller;
use User\Api\UserApi;


class BusinessUserController extends AdminController
{
    //账号列表
    public function index(){
        $user_id = UID;
//        $list = $this->get_role();
//        echo '<pre>';print_r($list);exit;
        $keywords_val = I('keywords_val');
        $status_val = I('status_val');
        if($keywords_val){
            $map['m.id|m.username|i.user_name|m.email|m.mobile|g.title']    =   array('like', '%'.(string)$keywords_val.'%');
        }
        if($status_val != null){
            $map['_string'] = 'i.status = '.$status_val;
        }else{
            $map['_string'] = 'i.status not in(-1)';
        }

//        echo '<pre>';print_r($bid);exit;
        if($user_id == 1){
//            $map['i.b_id'] = $bid['b_id'];
            $map['i.type'] = 'admin';
        }else{
            $bid = M('b_member_info')->where('uid='.$user_id)->find();
            $map['i.b_id'] = $bid['b_id'];
            $map['i.type'] = 'user';
        }
        $sql = M('b_member_info')->alias('i')
            ->field('m.*,g.title,i.status,i.user_name')
            ->join('left join b_auth_group as g on g.id = i.group_id')
            ->join('left join b_member_login as m on i.uid = m.id')
            ->where($map)
            ->order('id ASC')
            ->buildSql();
//        print_r($sql);exit;
        $business_info   = $this->page_list('m_member_login',$sql);
        foreach($business_info as $k=>$v){
            $business_info[$k]['x_status'] = $this->get_status($v['status']);
            $role = $this->get_role($v['id']);
            $business_info[$k]['role'] = $role['title'];
            $business_info[$k]['last_login_time'] = $v['last_login_time']?$v['last_login_time']:'';
        }
//        echo '<pre>';print_r($business_info);exit;

        $this->assign('_list', $business_info);
        $this->assign('status', array('0'=>'禁用','1'=>'正常','-1'=>'删除'));
        $this->assign('status_val', $status_val);
        $this->assign('keywords_val', $keywords_val);
        $this->assign('_role', $this->get_role());
        $this->assign('act','index');
        $this->meta_title = '账号列表';
        $this->display('index');
    }
    //添加账号
    public function add(){
        $user_id = UID;
        $username = I('user');
        $password = I('pwd');
        $per = I('per');
        $email = I('email');
        $mobile = I('tel');
        $name = I('name');

        if(IS_POST){
            if($mobile){
                if(!preg_match("/^1[34578]{1}\d{9}$/",$mobile)){
                    echo -3;exit;
                }
            }else{
                echo -10;exit;
            }
            if(strlen($password) < 6){
                echo -4;exit;
            }
            if($email){
                if(!preg_match("/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/",$email)){
                    echo -5;exit;
                }
            }else{
                echo -6;exit;
            }
            $mem_list = M('b_member_info')->where('uid='.$user_id)->find();
//            echo '<pre>';print_r($mem_list);exit;
            if($mem_list['type'] == 'admin'){
                $User   =   new UserApi;
                $arr    =   $User->register($username, $password, $email,$mobile);
                if($arr > 0){
                    M()->startTrans();//开启事物
                    $flag=false;

                    if(0 < $arr){ //添加成功
                        $flag=true;
                    }

                    $user = array('uid' => $arr, 'nickname' => $username,'user_name'=>$name,'b_id'=>$mem_list['b_id'],'group_id'=>$per, 'status' => 1,'type'=>'user','reg_time'=>time());

                    if(M('b_member_info')->add($user) > 0 && $flag){
                        M()->commit();
                        echo 1;exit;
                    }else{
                        M()->rollback();
                        echo -1;exit;
                    }
                }else{
                    echo -100;exit;
                }
            }elseif($mem_list['type'] == 'user'){
                echo -2;exit;
            }else{
                $User   =   new UserApi;
                $arr    =   $User->register($username, $password, $email,$mobile);
                if($arr > 0){
                    M()->startTrans();//开启事物
                    $flag=false;

                    if(0 < $arr){ //添加成功
                        $flag=true;
                    }

                    $user = array('uid' => $arr, 'nickname' => $username,'user_name'=>$name,'b_id'=>$mem_list['b_id'],'group_id'=>$per, 'status' => 1,'type'=>'admin','reg_time'=>time());

                    if(M('b_member_info')->add($user) > 0 && $flag){
                        M()->commit();
                        echo 1;exit;
                    }else{
                        M()->rollback();
                        echo -1;exit;
                    }
                }else{
                    echo -100;exit;
                }
            }
        }
    }

    //编辑账号
    public function edit(){
        if(IS_POST){
            $user_id = I('id');
            $user_name = I('user');
            $role = I('per');
            $phone = I('tel');
            $email = I('email');
            $nickname = I('name');

            $ucenter_member = array('username' => $user_name,'email'=>$email,'mobile'=>$phone);
            $res = M('b_member_login')->where('id='.$user_id)->save($ucenter_member);
            $member = array('nickname' => $user_name,'user_name'=>$nickname,'group_id'=>$role);
            $resu = M('b_member_info')->where('uid='.$user_id)->save($member);
            if($res || $resu){
                echo 1;exit;
            }else{
                echo -1;exit;
            }
        }
    }

    public function is_edit($id){
        $user_list = M('b_member_info')->alias('i')
            ->field('m.*,g.id as gid,g.title,i.status,i.user_name')
            ->join('left join b_auth_group as g on g.id = i.group_id')
            ->join('left join b_member_login as m on i.uid = m.id')->where('m.id='.$id)->find();
        if($user_list){
            echo json_encode(array('status'=>2,'data'=>$user_list));
        }
    }

    /* 更新状态 */
    public function changestatus(){
        $ids = I('request.ids');
        $status = I('request.status');
        if(empty($ids)){
            $this->error('请选择要操作的数据！');
        }
        $where['uid'] = array('in',$ids);
        if($status == 1){//账号启用
            $data = array('status'=>1);
        }else if($status == 0){//禁用
            $data = array('status'=>0);
        }else{//删除
            $data = array('status'=>-1);
        }
        M("b_member_info")->where($where)->save($data);
        $this->success('操作成功！');

    }
    public function restart($id){
        $password = '123456';
        $password =$this->think_ucenters_md5($password, 'u"XFk7.fCxE9BU#mWYP5!yQwH)0b{ILog8|^?;O(');
        $data = array('password' => $password,'reg_time'=>time());
        $res = M('b_member_login')->where('id='.$id)->save($data);
        if($res){
            echo 1;exit;
        }else{
            echo 2;exit;
        }
    }
    //获取角色名称
    public function get_role(){
        $uid = UID;
        if($uid == 1){
            $map['g.id'] = array('neq',1);
            $role_list = M('b_auth_group')->alias('g')->field('g.id,g.title')->where($map)->group('g.title')->select();
        }else{
            $map['m.uid'] = $uid;
            $map['m.type'] = 'admin';
            $role_list = M('b_member_info')->alias('m')->join('left join b_auth_group as g on g.b_id = m.b_id')->field('g.id,g.title')->where($map)->group('g.title')->select();
        }
        return $role_list;
    }

    /* 返回状态名称，并附带样式 */
    public function get_status($status=''){
        switch($status){
            case 0; $status_cn = '<span style="background: #ff8300;color:#fff;padding:1px 4px;">禁用</span>';break;
            case 1; $status_cn = '<span style="background: #3c8dbc;color:#fff;padding:1px 4px;"">正常</span>';break;
            case -1; $status_cn = '<span style="background: #ffc947;color:#fff;padding:1px 4px;"">删除</span>';break;
        }
        return $status_cn;
    }


    // 获取调用接口的IP地址
    public function get_ip(){
        if ($_SERVER["REMOTE_ADDR"]) {
            $ip = $_SERVER["REMOTE_ADDR"];
        } elseif (getenv("HTTP_X_FORWARDED_FOR")) {
            $ip = getenv("HTTP_X_FORWARDED_FOR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $ip = getenv("HTTP_CLIENT_IP");
        } elseif (getenv("REMOTE_ADDR")) {
            $ip = getenv("REMOTE_ADDR");
        } else {
            $ip = "";
        }
        return $ip;
    }

    public function think_ucenters_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }
}