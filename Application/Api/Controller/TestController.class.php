<?php
/**
 * Created by phpStorm.
 * User: ronghaichuan
 * Date: 2017/9/29
 * Time: 21:38
 */

namespace Api\Controller;


use Think\Think;

class TestController extends \Think\Controller
{
        public function index(){
        	print_r(base64_decode('8J+Ys/CfmpTwn5ih8J+RjPCfj77wn5iH8J+YieaWh+eroOW+iOWlveWVig=='));die;
        }
}