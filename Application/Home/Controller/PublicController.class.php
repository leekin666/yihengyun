<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;
use User\Api\UserApi;

class PublicController extends \Think\Controller {

    /**
     * 后台用户登录
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function login($username = null, $password = null, $verify = null){
        if(IS_POST){
            /* 检测验证码 TODO: */
//            if(!check_verify($verify)){
//                $this->error('验证码输入错误！');
//            }

            /* 调用UC登录接口登录 */
            $User = new UserApi;
            $uid = $User->login($username, $password);
            if(0 < $uid){ //UC登录成功
                /* 登录用户 */
                $Member = D('Member');
                if($Member->login($uid)){ //登录用户
                    //TODO:跳转到登录前页面
                    $this->success('登录成功！', U('Index/index'));
                } else {
                    $this->error($Member->getError());
                }

            } else { //登录失败
                switch($uid) {
                    case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                    case -2: $error = '密码错误！'; break;
                    default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
                }
                $this->error($error);
            }
        } else {
            if(is_login()){
                $this->redirect('/Mall');
            }else{
                /* 读取数据库中的配置 */
                $config	=	S('DB_CONFIG_DATA');
                if(!$config){
                    $config	=	D('Config')->lists();
                    S('DB_CONFIG_DATA',$config);
                }
                C($config); //添加配置

                $this->display();
            }
        }
    }
    /**
     * ajax登录
     */
    public function ajaxLogin(){
        $username = I('username') ? I('username') : null;
        $password = I('password') ? I('password') : null;
        $verify = I('verify') ? I('verify') : null;
        /* 检测验证码 TODO: */
//        if(!check_verify($verify)){
//            $this->error('验证码输入错误！');
//        }

        /* 调用UC登录接口登录 */
        $User = new UserApi;
        $uid = $User->login($username, $password);
        if(0 < $uid){ //UC登录成功
            /* 登录用户 */
            $Member = D('Member');
            if($Member->login($uid)){ //登录用户
                echo json_encode(array('error'=>0,'message'=>'登录成功！'));exit;
            } else {
                echo json_encode(array('error'=>1,'message'=>'登录失败！'));exit;
            }

        } else { //登录失败
            switch($uid) {
                case -1: $error = '用户不存在或被禁用！'; break; //系统级别禁用
                case -2: $error = '密码错误！'; break;
                default: $error = '未知错误！'; break; // 0-接口参数错误（调试阶段使用）
            }
            echo json_encode(array('error'=>1,'message'=>$error));exit;
        }
        exit;
    }

    /* 退出登录 */
    public function logout(){
        if(is_login()){
            D('Member')->logout();
            session('[destroy]');
//            $this->success('退出成功！', U('login'));
            $this->redirect('/home');
        } else {
            $this->redirect('login');
        }
    }

    public function verify(){
        $verify = new \Think\Verify();
        $verify->entry(1);
    }
    /**
     * 未登录返回模板
     */
    public function notLogin(){
        echo $this->fetch('login');exit;
    }
   
}