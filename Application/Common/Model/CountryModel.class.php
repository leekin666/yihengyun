<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/20
 * Time: 11:53
 */

namespace Common\Model;
use Think\Model;

class CountryModel extends Model
{
    protected $trueTableName = 'm_country';

    public function get(){
        $result = $this->where('is_show = 1')->select();
        return $result;
    }
}