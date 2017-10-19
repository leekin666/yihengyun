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
abstract class AbstractController extends Controller {
	
    protected $redis;

    protected $oauth2;

    public function __construct(ContainerInterface $container)
    {
        $this->redis = $container->get('redis');
        $this->oauth2 = $container->get('oauth2'); 
        // print_r($this->db);die;
    }
   
    protected function jsonSuccess(Response $response, $data = null, $message = '')
    {
        header('Content-Type: application/json');
        header('Content-Type: text/html;charset=utf-8');
        $result = [
            'errno' => ErrorCode::SUCCESS,
        ];
        if ($data !== null) {
            $result['data'] = $data;
        } else {
            $result['message'] = $message;
        }
        return json_decode($reslut);
    }

    protected function jsonError(Response $response, $errno, $defaultErrmsg = null, $data = null)
    {
        header('Content-Type: application/json');
        header('Content-Type: text/html;charset=utf-8');
        $errmsg = '出错了';
        if (!$errno) {
            $errno = ErrorCode::LOGIC_ERROR;
        }
        if ($defaultErrmsg) {
            $errmsg = $defaultErrmsg;
        }
        $result = [
            'errno' => $errno,
            'errmsg' => $errmsg,
        ];
        if ($data !== null) {
            $result['data'] = $data;
        }
        return json_decode($reslut);
    }

    public function __invoke(Request $request)
    {
        $action = 'http' . ucfirst(strtolower($request->getMethod()));
        return call_user_func_array([$this, $action], func_get_args());
    }
	
	
}
