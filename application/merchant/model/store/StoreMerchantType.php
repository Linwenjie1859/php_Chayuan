<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\merchant\model\store;

use traits\ModelTrait;
use basic\ModelBasic;
use service\UtilService as Util;
use app\merchant\model\store\StoreMerchant as StoreMerchantModel;

/**
 * Class StoreCategory
 * @package app\merchant\model\store
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
     * 删除分类
     * @param $id
     * @return bool
     */
    public static function delArticleCategory($id)
    {
        if(count(self::getMerchant($id,'*'))>0)
            return self::setErrorInfo('请先删除改分类下的店铺!');
        return self::edit(['is_del'=>1],$id,'id');
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
     * 获取分类底下的文章
     * id  分类表中的分类id
     * return array
     * */
    public static function getMerchant($id,$field){
        $res = StoreMerchantModel::where('status',1)->column($field,'id');
        $new_res = array();
        foreach ($res as $k=>$v){
            $cid_arr = explode(',',$v['cid']);
            if(in_array($id,$cid_arr)){
                $new_res[$k] = $res[$k];
            }
        }
        return $new_res;
    }
}