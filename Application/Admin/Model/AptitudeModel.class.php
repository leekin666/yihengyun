<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/18
 * Time: 18:21
 */

namespace Admin\Model;
use Think\Model;


class AptitudeModel extends Model
{
    protected $trueTableName = 'b_bussiness_info';

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

        $data['begion_term'] = strtotime(I('begion_term'));
        $data['end_term']   = strtotime(I('end_term'));

        if(empty(I("store_ms"))){
            $data['store_ms'] = 0;
        }
        if(empty(I("brand_ms"))){
            $data['brand_ms'] = 0;
        }
        if(empty(I("goods_ms"))){
            $data['goods_ms'] = 0;
        }
        if(empty(I("oprate_ms"))){
            $data['oprate_ms'] = 0;
        }
        if(empty(I("oprate_free"))){
            $data['oprate_free'] = 0;
        }


        $data['bank_address'] = get_regin_name(I('provnice')).get_regin_name(I('city')).get_regin_name(I('area'));
        /* 添加或更新数据 */
        if(empty($data['b_id'])){
            $data['add_date'] = time();
            $res = $this->add($data);
        }else{
//            $data['update_time'] = time();
//            $data['status'] = 0;
            $res = $this->save($data);
        }

        return $res;
    }
}