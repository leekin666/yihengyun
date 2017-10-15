<?php

namespace Admin\Model;
use Think\Model;

/**
 * 报修模型
 */

class BusinessChannelModel extends Model{
    protected $trueTableName = 'b_operate_plan';

    protected $_validate = array(
        array('name', 'require', '请选择计划名', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('desc', 'require', '请选择计划描述', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('goods_status', '1', self::MODEL_INSERT)
    );

    /**
     * 更新信息
     */
    public function update(){
        $data = $this->create();
        if(!$data){ //数据对象创建错误
            return false;
        }

        $data['name'] = I('plan');
        $data['desc'] = I('plan_desc');
        $data['start_time'] = strtotime('2017-01-01 00:00:00');
        $data['end_time'] = strtotime('2017-12-31 23:59:59');


//        echo '<pre>';print_r($data);exit;
        /* 添加或更新数据 */
        if(empty($data['id'])){
            $res = $this->add($data);
        }else{
            $res = $this->save($data);
        }
        return $res;
    }

}
?>