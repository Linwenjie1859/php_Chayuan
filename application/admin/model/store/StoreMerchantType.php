<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\store;

use traits\ModelTrait;
use basic\ModelBasic;
use service\UtilService as Util;
use app\admin\model\store\StoreMerchant as StoreMerchantModel;

/**
 * Class StoreCategory
 * @package app\admin\model\store
 */
class StoreMerchantType extends ModelBasic
{
    use ModelTrait;

    /**
     * 获取系统分页数据   分类
     * @param array $where
     * @return array
     */
    public static function systemPage($where = array()){
        $model = new self;
        if($where['name'] !== '') $model = $model->where('name','LIKE',"%$where[title]%");
        if($where['status'] !== '') $model = $model->where('status',$where['status']);
        $model = $model->where('is_del',0);
        $model = $model->where('hidden',0);
        return self::page($model);
    }



    /**
     * 获取分类名称和id     field
     * @param $field
     * @return array
     */
    public  static function getField($field){
        return self::where('is_del','eq',0)->where('status','eq',1)->where('hidden','eq',0)->column($field);
    }
    /**
     * 分级排序列表
     * @param null $model
     * @return array
     */
    public static function getTierList($model = null)
    {
        if($model === null) $model = new self();
        return Util::sortListTier($model->select()->toArray());
    }

    /**
     * @return:
     * @author Handsome Lin
     * @date 2019/11/21 10:12
     * @Notes:根据分类id删除店铺
     */
    public static function delMerchant($id)
    {
        if(count(self::getMerchant($id,'*'))>0)     //查看该分类是否有店铺存在,统计返回的数组的个数
            return self::setErrorInfo('请先删除改分类下的店铺!');
        return self::edit(['is_del'=>1],$id,'id');
    }

    /**
     * @return:数组
     * @author Handsome Lin
     * @date 2019/11/21 10:37
     * @Notes:通过分类的id,查看该分类下还有存在的店铺
     */
    public static function getMerchant($id,$field){
        return $res = StoreMerchantModel::where('status',1)->where('type',"=",$id)->column($field,'id');
    }
}