<?php

namespace Business\Model;
use Think\Model;

/**
 * 报修模型
 */

class GoodsModel extends Model{
    protected $trueTableName = 'b_goods_info';

    protected $_validate = array(
        array('brand_id', 'require', '请选择商品品牌', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('category', 'require', '请选择商品分类', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('goods_name', 'require', '商品中文名称不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('price', 'require', '本店售价不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
        array('logo', 'require', '商品主图不能为空', self::EXISTS_VALIDATE, 'regex', self::MODEL_BOTH),
    );

    protected $_auto = array(
        array('add_date', NOW_TIME, self::MODEL_INSERT),
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
        $map['mi.uid'] = UID;
        $store_id = M('b_member_info')->alias('mi')->field('si.store_id')
            ->join('left join b_store_info as si on mi.b_id = si.b_id')
            ->where($map)->find();

        $data['category_id'] =I('category');
        $data['store_id'] =$store_id['store_id'];
        $data['type'] = I('com_type');
        $data['goods_logo'] = I('logo');
        $data['goods_img'] = I('goods_img');
        $data['details'] = I('desc1');
        $data['details_letter'] = I('desc2');
        $data['note'] = I('description');
        $data['is_new'] = I('operate1')?I('operate1'):0;
        $data['is_hot'] = I('operate2')?I('operate2'):0;
        $data['is_boutique'] = I('operate3')?I('operate3'):0;
        $data['returned'] = I('service1')?I('service1'):0;
        $data['flash'] = I('service2')?I('service2'):0;
        $data['putaway'] = I('examine');
        $data['help_sales'] = I('sale');
        $data['add_date'] = strtotime(I('add_date'));
        /* 添加或更新数据 */
        if(empty($data['goods_id'])){
            $res = $this->add($data);
        }else{
            $res = $this->save($data);
        }
        return $res;
    }

}
?>