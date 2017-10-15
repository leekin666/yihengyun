<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/19
 * Time: 18:04
 */

namespace Admin\Model;


class LicenseModel
{
    protected $trueTableName = 'b_business_license';

//    protected $_validate = array(
//        array('store_id', 'require', '系统还未分配店铺！', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//        array('brand_name', 'require', '品牌名不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
//    );
//
//    protected $_auto = array(
//        array('add_date', NOW_TIME, self::MODEL_INSERT),
//        array('update_time', NOW_TIME, self::MODEL_INSERT),
//        array('status', '0', self::MODEL_INSERT)
//    );

    /**
     * 更新信息
     */
    public function update(){
        $data = $this->create();
        if(!$data){ //数据对象创建错误
            return false;
        }
        /* 添加或更新数据 */
        if(empty($data['b_id'])){
            $res = $this->add($data);
        }else{
            $data['update_time'] = time();
            $data['status'] = 0;
            $res = $this->save($data);
        }

        return $res;
    }
}