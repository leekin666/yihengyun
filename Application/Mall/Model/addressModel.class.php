<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Mall\Model;
use Think\Model;

/**
 * 分类模型
 */
class addressModel extends Model{
    protected $trueTableName = "b_user_address";

    protected $_validate = array(
        array('user_id', 'require', '用户不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('consignee', 'require', '收货人不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('mobile', 'require', '手机号不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('province', 'require', '请选择省', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('city', 'require', '请选择市', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('district', 'require', '请选择区', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('address', 'require', '详细地址不能为空', self::MUST_VALIDATE , 'regex', self::MODEL_BOTH),
    );

    /**
     * 更新购物车
     */
    public function update(){
        $data = $this->create();
        if(!$data){ //数据对象创建错误
            return false;
        }
        $data['user_id'] = I('user_id') ? I('user_id') : 32;
        /* 添加或更新数据 */
        return empty($data['address_id']) ? $this->add() : $this->save();
    }

}