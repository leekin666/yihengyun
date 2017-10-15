<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Admin\Controller;
use User\Api\UserApi as UserApi;
use PSCWS4\PSCWS4;

/**
 * 后台首页控制器
 * @author 麦当苗儿 <zuojiazi@vip.qq.com>
 */
class IndexController extends AdminController {

    /**
     * 后台首页
     * @author 麦当苗儿 <zuojiazi@vip.qq.com>
     */
    public function index(){
        // PSCWS4  中文分词
//        import("Vendor.Pscws.Pscws4");
//        $pscws = new PSCWS4('utf-8');
//        $pscws -> set_charset('utf-8');
//        $pscws->set_dict(CONF_PATH . 'etc/dict.utf8.xdb');
//        $pscws->set_rule(CONF_PATH . 'etc/rules.utf8.ini');
//        $pscws->set_ignore(true);
//        $pscws->send_text("同仁堂燕窝");
//        $words = $pscws->get_tops(10, 'r,v,p');
//        $pscws->close();
//        var_dump($words);
//        echo "1111";exit;
        if(UID){
            $this->meta_title = '管理首页';
            $this->display();
        } else {
            $this->redirect('Public/login');
        }
    }

}
