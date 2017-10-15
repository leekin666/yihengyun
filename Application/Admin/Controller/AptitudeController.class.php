<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/18
 * Time: 17:16
 */

namespace Admin\Controller;
use User\Api\UserApi;

class AptitudeController extends AdminController
{
        public function index(){
            header("Content-type: text/html; charset=utf-8");
//           $b_names =  $this->get_all_business();
            $keywords_val = I('keywords_val');
            $status_val = I('status_val');
            $this->assign('status_val',$status_val);
            if($keywords_val){
                $map['id|b_name']    =   array('like', '%'.(string)$keywords_val.'%');
            }
            if($status_val != null){
                $map['_string'] = 'status = '.$status_val;
            }else{
                $map['_string'] = 'status in (0,1,5,10,100)';
            }
            $business_info = $this->lists('b_bussiness_info', $map,'b_id DESC');
            foreach($business_info as $k=>$v){
                   $condition['b_id'] = $v['b_id'];
                   $qualifi = M('b_qualification_info')->where($condition)->select();
                   foreach ($qualifi as $key => $value){
                       $store_name = M('b_store_info')->where($condition)->getField('name');
                       $qualifi[$key]['store_name'] = $store_name;
                   }

                $business_info[$k]['qualification'] = $qualifi;
            }
            $this->assign('_list', $business_info);
            $this->assign('status', array('0'=>'待审核','10'=>'审核未通过','100'=>'审核通过'));
            $this->meta_title = '商家列表';
            $this->display('index');
        }
        public function edit(){
            header("Content-type: text/html; charset=utf-8");
            if(IS_POST){
                $aptitude       = D('Aptitude');
                $qualification  = D('qualification');
                $store          = D('store');
                $b_id            = $aptitude->update();

                if(!empty($_POST['brand_zz'])){
                    $qua = $qualification->update($b_id);
                    $st = $store->update($qua);
                    if( $qua == false &&  $st == false){
                        $error = $aptitude->getError();
                        $this->error(empty($error) ? '未知错误！' : $error);
                    }
                }

                if(!empty($_POST['c_zz'])){
                    $data['b_id']    = $b_id;
                    $data['name']    = I("c_name");
                    $data['contact'] = I("c_contact");
                    $data['contact_tel']  = I("c_mobile");
                    $data['period']  = I("c_period");
                    $data['remark']  = I("c_remark");
                    $data['q_type']   = 3;
                    $qua = M('b_qualification_info')->add($data);
                    $store = D('store');
                    $store->update($qua);
                }

                if(!empty($_POST['f_zz'])){
                    $data['b_id']    = $b_id;
                    $data['name'] = I("f_name");
                    $data['summary'] = I('f_summary');
                    $data['contact'] = I("f_contact");
                    $data['contact_tel']          = I("f_mobile");
                    $data['q_type']   = 4;
                    $q_id = M('b_qualification_info')->add($data);
                    $store = D('store');
                    $store->update($q_id);
                }

            }else{
                $this->assign('_region',getRegion());
                $this->assign('_type',$this->get_comany_type());
                $this->assign('role',$this->get_admin_list());
                $condition['b_id'] = I("id");
                $res   =   M('b_bussiness_info')->where($condition)->find();
                $res['create_term'] = date('Y-m-d',$res['create_term']);
                $res['end_term'] = date('Y-m-d',$res['end_term']);
                $res['begion_term'] = date('Y-m-d',$res['begion_term']);
                $map['b_id'] = $res['b_id'];
                $qual = M("b_qualification_info")->where($map)->select();
                foreach ($qual as $k => $v){
                    $qual[$k]['store_name'] = M('b_store_info')->where($map)->getField('name');
                    $qual[$k]['summary']    = M('b_store_info')->where($map)->getField('summary');
                }
                $res['qual'] = $qual;
                $this->assign('row',$res);
                $this->display('add');
            }

        }
        public function add(){
            if(IS_POST){
                $aptitude       = D('Aptitude');
                $qualification  = D('qualification');
                $store          = D('store');
                if(I("b_id")){
                     $b_id            = I("b_id");
                    if(!empty($_POST['brand_zz'])){
                        $qua = $qualification->update($b_id);
                    }

                    if(!empty($_POST['c_zz'])){
                        $data['name']    = I("c_name");
                        $data['contact'] = I("c_contact");
                        $data['contact_tel']  = I("c_mobile");
                        $data['account_period']  = I("c_period");
                        $data['remark']  = I("c_remark");
                        $data['q_type']   = 3;
                        M('b_qualification_info')->where(array('q_id' => I('c_q_id')))->save($data);

                    }

                    if(!empty($_POST['f_zz'])){

                        $data['name'] = I("f_name");
                        $data['summary'] = I('f_summary');
                        $data['contact'] = I("f_contact");
                        $data['account_period'] = I('f_period');
                        $data['contact_tel']          = I("f_mobile");
                        $data['q_type']   = 4;
                        M('b_qualification_info')->where(array('q_id' => I('f_q_id')))->save($data);;

                    }

                    $st = $store->update($b_id);

                    //添加商家管理
//                    $pass = think_ucenters_md5(I("password"), UC_AUTH_KEY);
//                    $b_member_info['password']  = $pass;
//                    $b_member_info['user_name']  = I("admin_acount");
//                    $b_member_info['group_id']  = I("role");
//                    $b_member_login['password']  = I("admin_acount");
//                    $b_member_login['username'] = $pass;
//                    M("b_member_info")->add($b_member_info);
//                    M("m_member_login")->add($b_member_login);

                    if($b_id !== false){
                        $this->success('更新商家信息成功！',U('index'));
                    }else{
                        $error = $aptitude->getError();
                        $this->error(empty($error) ? '未知错误！' : $error);
                    }

                }else{
                    //获取新增企业ID
                    $b_id  = $aptitude->update();
                    //如果选中品牌资质 则添加品牌资质
                    if(!empty($_POST['brand_zz'])){
                        $qua = $qualification->update($b_id);
                        if( $qua == false){
                            $error = $aptitude->getError();
                            $this->error(empty($error) ? '未知错误！' : $error);
                        }
                    }
                    //如果选中采购资质 则添加采购资质
                    if(!empty($_POST['c_zz'])){
                        $data['b_id']    = $b_id;
                        $data['name']    = I("c_name");
                        $data['contact'] = I("c_contact");
                        $data['contact_tel']  = I("c_mobile");
                        $data['period']  = I("c_period");
                        $data['remark']  = I("c_remark");
                        $data['q_type']   = 3;
                        M('b_qualification_info')->add($data);
                    }
                    //如果选中服务资质 则添加服务资质
                    if(!empty($_POST['f_zz'])){
                        $data['b_id']    = $b_id;
                        $data['name'] = I("f_name");
                        $data['summary'] = I('f_summary');
                        $data['contact'] = I("f_contact");
                        $data['contact_tel']          = I("f_mobile");
                        $data['q_type']   = 4;
                        M('b_qualification_info')->add($data);
                    }

                    //给商家添加一个店铺
                    $store = $store->update($b_id);

                    //添加商家管理
//                    $pass = think_ucenters_md5(I("password"), UC_AUTH_KEY);
//                    $b_member_info['password']  = $pass;
//                    $b_member_info['user_name']  = I("admin_acount");
//                    $b_member_info['group_id']  = I("role");
//                    $b_member_login['password']  = I("admin_acount");
//                    $b_member_login['username'] = $pass;
//                    M("b_member_info")->add($b_member_info);
//                    M("m_member_login")->add($b_member_login);

                    if($b_id !== false && $store !== false){
                        $group_id = I("group_id")?I("group_id"):0;
                        $datas['b_id'] = $b_id;
                        //给企业分配管理员
                        M("b_member_info")->where(array('uid'=>$group_id))->save($datas);
                        $this->success('新增成功！',U('index'));
                    }else{
                        $error = $aptitude->getError();
                        $this->error(empty($error) ? '未知错误！' : $error);
                    }
                }
            }else{
                $this->assign('_region',getRegion());
                $this->assign('_type',$this->get_comany_type());
                //角色列表
                $this->assign('role',$this->get_admin_list());
//            var_dump($region);exit;
                $this->display('add');
            }
        }
        public function get_all_business(){
            $res = M("b_bussiness_info")->getField('b_id,b_name',true);
            return $res;
        }
        public function getregion(){
            $parent_id = I('parent_id');
            $region = getregion($parent_id,true);
            echo $region;exit;
        }
            //获取公司类型
        public function get_comany_type(){
            $str = "全民所有制,集体所有制,有限责任公司,有限责任公司（国有独资）,有限责任公司（国有控股）,有限责任公司（国内合资）,有限责任公司（自然人控股或私营性企业控股）,有限责任公司（自然人投资或控股）,有责任公司（自然人独资）,
                                有限责任公司（非自然人投资或控股的法人独资）,
                                有限责任公司（外商投资企业投资）,
                                有限责任公司（外国法人独资）,
                                有限责任公司（中外合资）,
                                有限责任公司（中外合作）,
                                有限责任公司（外商合资）,
                                有限责任公司（外国非法人经济组织独资）,
                                有限责任公司（外国自然人独资）,
                                有限责任公司（台港澳与外国投资者合资）,
                                有限责任公司（台港澳与境内合资）,
                                有限责任公司（台港澳与境内合作）,
                                有限责任公司（台港澳合资）,
                                有限责任公司（台港澳法人独资）,
                                有限责任公司（台港澳非法人经济组织独资）,
                                有限责任公司（台港澳自然人独资）,
                                一人有限责任公司（法人独资）,
                                一人有限责任公司（自然人独资）,
                                一人有限责任公司(外商投资企业法人独资),
                                一人有限责任公司(外商投资企业自然人独资),
                                股份有限公司,
                                股份有限公司（非上市、国有控股）,
                                股份有限公司（中外合资，未上市）,
                                股份有限公司（中外合资，上市）,
                                股份有限公司（外商合资，未上市）,
                                股份有限公司（外商合资，上市）,
                                股份有限公司（台港澳与外国投资者合资，未上市）,
                                股份有限公司（台港澳与外国投资者合资，上市）,
                                股份有限公司（台港澳与境内合资，未上市）,
                                股份有限公司（台港澳与境内合资，上市）,
                                股份有限公司（台港澳合资，未上市）,
                                股份有限公司（台港澳合资，上市）";
            $arr = explode(',',$str);
            return $arr;
        }
        public function get_admin_list(){
            $admin = M("b_member_info")->where(array('type' => 'admin'))->select();
            return $admin;
        }

    //冻结企业
        public function freeze(){
            $arr_ids = implode(',',I("ids"));
            $map['b_id'] = array('in',$arr_ids);
            $data['status'] = 1;
            $res = M("b_bussiness_info")->where($map)->save($data);
            if($res !== false){
                $this->success('冻结成功！',U('index'));
            }else{
                $this->error(empty($error) ? '未知错误！' : $error);
            }
        }

        //企业删除
    public function delete(){
        $arr_ids = implode(',',I("ids"));
        $map['b_id'] = array('in',$arr_ids);
        $data['status'] = 5;
        $res = M("b_bussiness_info")->where($map)->save($data);
        if($res !== false){
            $this->success('删除成功！',U('index'));
        }else{
            $this->error(empty($error) ? '未知错误！' : $error);
        }
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
}