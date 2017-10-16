<?php

/**
 * Created by phpStorm.
 * User: ronghaichuan
 * Date: 2017/10/9
 * Time: 13:53
 */

namespace Api\Controller;

class OauthController extends HomeController {

    const DOMAIN = 'http://yun.iawim.com/Api';
    const API_VERSION = 'v1';
    const REDIRECT_URL = self::DOMAIN;
    const AUTHORIZE_REDIRECT_URL = self::DOMAIN.'/oauth/authorize.html';
    const ENCRYPT_SECRET = 'YiHeng';
    const API_KEY = 'YunApi';

    public function register() {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        $storage = $this->oauth2->getStorage('client');
        if (isset($post['username']) && isset($post['password'])) {
            $checkOpenUserRes = M('distribution_open')->where(['key' => $post['username'],'secret' => md5($post['password']),'status' => 1])->find();
            if ($checkOpenUserRes) {
                $client_id = md5($post['username']);
                $client_secret = md5(uniqid());
                $status = $storage->setClientDetails($client_id, $client_secret, self::AUTHORIZE_REDIRECT_URL, 'authorization_code refresh_token');
                if ($status) {
                    $details = $storage->getClientDetails($client_id);
                    $data = $this->client($details);
                    exit($this->jsonSuccess($data));
                } else {
                    exit($this->jsonError(20001, '认证失败'));
                }
            } else {
                exit($this->jsonError(10013, '请求参数不合法'));
            }
        } else {
            exit($this->jsonError(10012, '请求参数缺失'));
        }
    }

    public function authorize() {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        
        if ($checkOpenUserRes) {
            $response = new \OAuth2\Response();
            if ($this->oauth2->validateAuthorizeRequest($request, $response)) {
                $clientId = 0;
                $this->oauth2->handleAuthorizeRequest($request, $response, true, $clientId);
                $code = substr($response->getHttpHeader('Location'), strpos($response->getHttpHeader('Location'), 'code=') + 5, 40);
//                print_r($code);die;
                $authorize = $this->oauth2->getStorage('authorization_code');
                $authed = $authorize->getAuthorizationCode($code);
                $data = [
                    'authorize_code' => $code,
                    'expire_time' => $authed['expires']
                ];
                return $this->jsonSuccess($data);
            } else {
//                $response->send();
                exit($this->jsonError(20002, '授权失败'));
            }
        } else {
            exit($this->jsonError(10013, '请求参数不合法'));
        }
    }

    /*
     * 有效期为 1209600s，可以在 OAuth2/ResponseType/AccessToken.php 中的 AccessToken class 中的构造函数配置中进行修改。
     * curl -u app_key:app_secret /authed/token/********.html -d grant_type=authorization_code&code=$authcode
     */

    public function token() {
        $request = \OAuth2\Request::createFromGlobals();
        $post = $request->request;
        if ($args['auth'] == substr(md5($post['client_id'] . $post['state'] . 'token'), 0, 8)) {
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
    
    public function client($details) {
        $seed = md5(uniqid());
        $authorize = substr(md5($details['client_id'] . $seed . 'authorize'), 0, 8);
        $token = substr(md5($details['client_id'] . $seed . 'token'), 0, 8);
        $refresh = substr(md5($details['client_id'] . $seed . 'refresh'), 0, 8);
        $resource = substr(md5($details['client_id'] . $seed . 'resource'), 0, 8);
        return array(
            'app_key' => $details['client_id'],
            'app_secret' => $details['client_secret'],
            'authorize_url' => self::REDIRECT_URL.'/oauth/authorize/'.$authorize.'.html',
            'token_url' => self::REDIRECT_URL.'/oauth/token/'. $token.'.html',
            'refresh_url' => self::REDIRECT_URL.'/oauth/refresh/'.$refresh.'.html',
            // 'source_url' => Config::REDIRECT_URL.'/oauth/resource/'.$resource.'.html',
            'state' => $seed,
            'expire_time' => 30
        );
    }
    
    public function setTokenUserId($access_token, $user_id) {
        $where = ['access_token' => $access_token];
        $data = ['user_id' => intval($user_id)];
        return $this->db->oauth_access_tokens()->where($where)->update($data);
    }

    public function getTokenUserId($access_token) {
        $where = ['access_token' => $access_token];
        $user_id = $this->db->oauth_access_tokens()->where($where)->fetch()['user_id'];
        if ($user_id < 1) {
            return false;
        }
        return $user_id;
    }

    public function delTokenUserId($access_token) {
        $where = ['access_token' => $access_token];
        $data = ['user_id' => 0];
        return $this->db->oauth_access_tokens()->where($where)->update($data);
    }
    
    public function getOauthRequest(){
        $oauthRequest = \OAuth2\Request::createFromGlobals();
        return $oauthRequest->request;
    }
}
