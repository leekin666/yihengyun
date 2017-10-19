<?php

namespace Api\Controller;

class OauthResourceController extends OauthController {

    protected function _initialize() {
        parent::_initialize();
        $this->resource();
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

}
