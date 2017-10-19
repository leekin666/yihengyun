<?php

namespace Api\Controller;

<<<<<<< HEAD
use OAuth2\Autoloader;
use Api\Controller\RedisController;

class OauthController extends HomeController {

    const DOMAIN = 'http://yun.lc/Api';
    const API_VERSION = 'v1';
    const REDIRECT_URL = self::DOMAIN;
    const AUTHORIZE_REDIRECT_URL = self::DOMAIN . '/oauth/authorize.html';
    const ENCRYPT_SECRET = 'YiHeng';
    const API_KEY = 'YunApi';

    protected $oauth2;
    protected $redis;

    protected function _initialize() {
        header("Content-Type: application/json");
        $this->redis  = new RedisController();
        $this->oauth2 = $this->OAuthServer();
    }

    protected function OAuthServer() {
        \OAuth2\Autoloader::register();
        $storage = new \OAuth2\Storage\Pdo(array('dsn' => 'mysql:dbname=bbc_v2;host=127.0.0.1;port=3306', 'username' => C('DB_USER'), 'password' => C('DB_PWD')));
=======
class OauthController extends HomeController {

    const DOMAIN = 'http://yun.iawim.com/Api';
    const API_VERSION = 'v1';
    const REDIRECT_URL = self::DOMAIN;
    const AUTHORIZE_REDIRECT_URL = self::DOMAIN.'/oauth/authorize.html';
    const ENCRYPT_SECRET = 'YiHeng';
    const API_KEY = 'YunApi';
    
    protected $oauth2;
    
    protected function _initialize() {
        header("Content-Type: application/json");
        $this->oauth2 = $this->OAuthServer();
    }
    
    protected function OAuthServer(){
        \OAuth2\Autoloader::register();
        $storage = new \OAuth2\Storage\Pdo(array('dsn' =>  'mysql:dbname=bbc_v2;host=localhost', 'username' => 'root' , 'password' => 'L&S123smzq'));
>>>>>>> 1e9db0ffff7e9fdba3e366c99d24a947f3b86e7e
        $server = new \OAuth2\Server($storage);
        $server->addGrantType(new \OAuth2\GrantType\AuthorizationCode($storage));
        $server->addGrantType(new \OAuth2\GrantType\ClientCredentials($storage));
        $server->addGrantType(new \OAuth2\GrantType\RefreshToken($storage));
        return $server;
    }
<<<<<<< HEAD

    public function index() {
        exit(jsonError(401, 'UnAuthorized'));
    }

=======
    
    public function index(){
        exit($this->jsonError(401, 'UnAuthorized'));
    }
    
>>>>>>> 1e9db0ffff7e9fdba3e366c99d24a947f3b86e7e
    public function login() {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        $storage = $this->oauth2->getStorage('client');
        if (!isset($post['username']) || !isset($post['password'])) {
<<<<<<< HEAD
            exit(jsonError(10012, '请求参数缺失'));
        }
        $clientRow = M('open_system')->where(['key' => $post['username'], 'secret' => md5($post['password']), 'status' => 1])->find();
        if (!$clientRow) {
            exit(jsonError(21001, '账号或密码错误'));
        }
        $this->redis->isOutlimit($post['username'].'-login');
        $client_id = md5($post['username']);
        $client_secret = md5($post['password']);
        $seed = uniqid();
        $state = substr(md5($client_id . 'authorize'), 0, 8);
        $redirect_uri = self::AUTHORIZE_REDIRECT_URL . '?response_type=code&client_id=' . $client_id . '&state=' . $state;
        $status = $storage->setClientDetails($client_id, $client_secret, $redirect_uri, 'authorization_code refresh_token', 'all', $clientRow['id']);
        if (!$status) {
            exit(jsonError(20001, '认证失败'));
        }
        header('Location:' . $redirect_uri);
    }

    public function authorize() {
        $request = \OAuth2\Request::createFromGlobals();
        if ($_GET['response_type'] != 'code') {
            exit(jsonError(20008, '不支持的RESPONSE_TYPE类型'));
        }
        $storage = $this->oauth2->getStorage('client');
        $client = $storage->getClientDetails($_GET['client_id']);
        if (!$client) {
            exit(jsonError(20011, '不合法的client_id'));
        }
        if ($_GET['state'] == substr(md5($_GET['client_id'] . 'authorize'), 0, 8)) {
=======
            exit($this->jsonError(10012, '请求参数缺失'));
        }
        $clientRow = M('distribution_open')->where(['key' => $post['username'],'secret' => md5($post['password']),'status' => 1])->find();
        if (!$clientRow) {
            exit($this->jsonError(21001, '账号或密码错误'));
        }
        $client_id = md5($post['username']);
        $client_secret = md5($post['password']);
        $seed = uniqid();
        $state = substr(md5($client_id. 'authorize'), 0, 8);
        $redirect_uri = self::AUTHORIZE_REDIRECT_URL.'?response_type=code&client_id='.$client_id.'&state='.$state;
        $status = $storage->setClientDetails($client_id, $client_secret, $redirect_uri, 'authorization_code refresh_token','all',$clientRow['id']);
        if (!$status) {
            exit($this->jsonError(20001, '认证失败'));
        }
        header('Location:'.$redirect_uri);
//        $details = $storage->getClientDetails($client_id);
//        $data = $this->client($details);
//        print_r($details);die;
//        header($string);
//        exit($this->jsonSuccess($data));
    }
    
    public function authorize() {
        $request = \OAuth2\Request::createFromGlobals();
//        print_r($request);die;
        if($_GET['response_type'] != 'code'){
            exit($this->jsonError(20008, '不支持的RESPONSE_TYPE类型'));
        }
        $storage = $this->oauth2->getStorage('client');
        $client = $storage->getClientDetails($_GET['client_id']);
        if(!$client){
            exit($this->jsonError(21002, '不合法的client_id'));
        }
        if ($_GET['state'] == substr(md5($_GET['client_id']. 'authorize'), 0, 8)) {
>>>>>>> 1e9db0ffff7e9fdba3e366c99d24a947f3b86e7e
            $response = new \OAuth2\Response();
            if ($this->oauth2->validateAuthorizeRequest($request, $response)) {
                $clientId = 0;
                $this->oauth2->handleAuthorizeRequest($request, $response, true, $clientId);
                $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
                $authorize = $this->oauth2->getStorage('authorization_code');
                $authed = $authorize->getAuthorizationCode($code);
                $data = [
                    'authorize_code' => $code,
                    'expire_time' => $authed['expires']
                ];
<<<<<<< HEAD
                exit(jsonSuccess($data));
            } else {
                exit(jsonError(20002, '授权失败'));
            }
        } else {
            exit(jsonError(20010, '回调失败'));
=======
                exit($this->jsonSuccess($data));
            } else {
                exit($this->jsonError(20002, '授权失败'));
            }
        } else {
            exit($this->jsonError(20009, '回调失败'));
        }
    }
    
    public function authorizeBack() {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        if (1) {
            $response = new \OAuth2\Response();
            if ($this->oauth2->validateAuthorizeRequest($request, $response)) {
                $clientId = 0;
                $this->oauth2->handleAuthorizeRequest($request, $response, true, $clientId);
                $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
                $authorize = $this->oauth2->getStorage('authorization_code');
                $authed = $authorize->getAuthorizationCode($code);
                $data = [
                    'authorize_code' => $code,
                    'expire_time' => $authed['expires']
                ];
                exit($this->jsonSuccess($data));
            } else {
                exit($this->jsonError(20002, '授权失败'));
            }
        } else {
            exit($this->jsonError(10013, '请求参数不合法'));
>>>>>>> 1e9db0ffff7e9fdba3e366c99d24a947f3b86e7e
        }
    }

    public function token() {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
<<<<<<< HEAD
        if ($post['grant_type'] != 'authorization_code') {
            exit(jsonError(20009, '不支持的GRANT_TYPE类型'));
        }
        $this->redis->isOutlimit($post['client_id'].'-token');
        if ($post['code']) {
            $response = new \OAuth2\Response();
            $resp = $this->oauth2->handleTokenRequest($request, $response);
            $body = $resp->getResponseBody();
            $data = json_decode($body, true);
            if (isset($data['access_token'])) {
                // $storage = $this->oauth2->getStorage('client');
                // $client = $storage->getClientDetails($post['client_id']);
                // $this->setTokenUserId($data['access_token'], $client['user_id']);
                exit(jsonSuccess($data));
            } else {
                exit(jsonError(20003, $data));
            }
        } else {
            exit(jsonError(10013, '请求参数不合法'));
        }
    }

    public function refresh() {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        if ($post) {
            $response = new \OAuth2\Response();
            $resp = $this->oauth2->handleTokenRequest($request, $response);
            $body = $resp->getResponseBody();
            $data = json_decode($body, true);
            if (isset($data['access_token'])) {
                // $storage = $this->oauth2->getStorage('client');
                // $client = $storage->getClientDetails($post['client_id']);
                // $this->setTokenUserId($data['access_token'], $client['user_id']);
                exit(jsonSuccess($data));
            } else {
                exit(jsonError(20003, $data));
            }
        } else {
            exit(jsonError(10013, '请求参数不合法'));
        }
    }

    protected function resource() {
        $request = \OAuth2\Request::createFromGlobals();
        $request->request;
        if (!$this->oauth2->verifyResourceRequest($request)) {
            $body = $this->oauth2->getResponse()->getResponseBody();
            $data = json_decode($body, true);
            exit(jsonError(40005,$data));
        }else{
            return true;
        }
    }
    
    public function setTokenUserId($access_token, $user_id) {
        $where = ['access_token' => $access_token];
        $data = ['user_id' => intval($user_id)];
        return M('oauth_access_tokens')->where($where)->save($data);
=======
        if ($post['code']) {
            $response = new \OAuth2\Response();
            $resp = $this->oauth2->handleTokenRequest(\OAuth2\Request::createFromGlobals(), $response);
            $body = $resp->getResponseBody();
            $data = json_decode($body, true);
            if (isset($data['access_token'])) {
                exit($this->jsonSuccess($data));
            } else {
                exit($this->jsonError(20003, $data));
            }
        } else {
            exit($this->jsonError(10013, '请求参数不合法'));
        }
    }

    /*
     * curl -u app_key:app_secret /authed/refresh/********.html -d "grant_type=refresh_token&refresh_token=xxx"
     */

    public function refresh() {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        if ($args['auth'] == substr(md5($post['client_id'] . $post['state'] . 'refresh'), 0, 8)) {
            $response = new \OAuth2\Response();
            $resp = $this->oauth2->handleTokenRequest(\OAuth2\Request::createFromGlobals(), $response);
            $body = $resp->getResponseBody();
            $data = json_decode($body, true);
            if (isset($data['access_token'])) {
                return $this->jsonSuccess($res, $data);
            } else {
                return $this->jsonError($res, 20003, $data);
            }
        } else {
            return $this->jsonError($res, 10013, '请求参数不合法');
        }
    }
    
    
    public function setTokenUserId($access_token, $user_id) {
        $where = ['access_token' => $access_token];
        $data = ['user_id' => intval($user_id)];
        return $this->db->oauth_access_tokens()->where($where)->update($data);
>>>>>>> 1e9db0ffff7e9fdba3e366c99d24a947f3b86e7e
    }

    public function getTokenUserId($access_token) {
        $where = ['access_token' => $access_token];
<<<<<<< HEAD
        $user_id = M('oauth_access_tokens')
        ->join('oauth_clients ON oauth_clients.client_id = oauth_access_tokens.client_id')
        ->where(['access_token' => $access_token])->getField('oauth_clients.user_id');
=======
        $user_id = $this->db->oauth_access_tokens()->where($where)->fetch()['user_id'];
>>>>>>> 1e9db0ffff7e9fdba3e366c99d24a947f3b86e7e
        if ($user_id < 1) {
            return false;
        }
        return $user_id;
    }

    public function delTokenUserId($access_token) {
        $where = ['access_token' => $access_token];
        $data = ['user_id' => 0];
<<<<<<< HEAD
        return M('oauth_access_tokens')->where($where)->update($data);
    }

    public function getOauthRequest() {
        $oauthRequest = \OAuth2\Request::createFromGlobals();
        return $oauthRequest->request;
    }
    
=======
        return $this->db->oauth_access_tokens()->where($where)->update($data);
    }
    
    public function getOauthRequest(){
        $oauthRequest = \OAuth2\Request::createFromGlobals();
        return $oauthRequest->request;
    }
>>>>>>> 1e9db0ffff7e9fdba3e366c99d24a947f3b86e7e
}
