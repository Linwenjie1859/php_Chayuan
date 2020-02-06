<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\ebapi\model\store;

use app\core\behavior\GoodsBehavior;
use service\HookService;
use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;

/**
 * 点赞收藏model
 * Class StoreProductRelation
 * @package app\ebapi\model\store
 */
class StoreMerchantRelation extends ModelBasic
{
    use ModelTrait;

    /**
     * 获取用户点赞所有产品的个数
     * @param $uid
     * @return int|string
     */
    public static function getUserIdLike($uid = 0){
        $count = self::where('uid',$uid)->where('type','like')->count();
        return $count;
    }

    /**
     * 获取用户收藏所有产品的个数
     * @param $uid
     * @return int|string
     */
    public static function getUserIdCollect($uid = 0){
        $count = self::where('uid',$uid)->where('type','collect')->count();
        return $count;
    }
    
    /**
     * @Modify: Mr. Lin
     * @function: 添加点赞 收藏
     * @instructions: 
     * @param {type} 
     * @return: JSON
     */
    public static function merchantRelation($merId,$uid,$relationType,$category = 'product')
    {
        if(!$merId) return self::setErrorInfo('产品不存在!');
        $merchant = StoreMerchant::get($merId);
        if(!$merchant) return self::setErrorInfo('产品不存在!');
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        $data = ['uid'=>$uid,'mer_id'=>$merId,'type'=>$relationType,'category'=>$category,'mer_type'=>$merchant['type']];
        if(self::be($data)) return true;
        $data['add_time'] = time();
        self::set($data);
        $num = Db::table('eb_store_merchant')->where('id', $merId)->value('fav_count');
        Db::table('eb_store_merchant')->update(['id'=>$merId, 'fav_count'=>$num+1]);
        HookService::afterListen('store_'.$category.'_'.$relationType,$merId,$uid,false,GoodsBehavior::class);
        return true;
    }

    /**
     * @Modify: Mr. Lin
     * @function: 取消 点赞 收藏
     * @instructions: 
     * @param {type} 
     * @return: JSON
     */
    public static function unMerchantRelation($merId,$uid,$relationType,$category = 'product')
    {
        if(!$merId) return self::setErrorInfo('产品不存在!');
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        self::where(['uid'=>$uid,'mer_id'=>$merId,'type'=>$relationType,'category'=>$category])->delete();
        $num = Db::table('eb_store_merchant')->where('id', $merId)->value('fav_count');
        if($num>0){
            Db::table('eb_store_merchant')->update(['id'=>$merId, 'fav_count'=>$num-1]);
        }
        HookService::afterListen('store_'.$category.'_un_'.$relationType,$merId,$uid,false,GoodsBehavior::class);
        return true;
    }

    public static function productRelationNum($merId,$relationType,$category = 'product')
    {
        $relationType = strtolower($relationType);
        $category = strtolower($category);
        return self::where('type',$relationType)->where('mer_id',$merId)->where('category',$category)->count();
    }

    public static function isProductRelation($product_id,$uid,$relationType,$category = 'product')
    {
        $type = strtolower($relationType);
        $category = strtolower($category);
        return self::be(compact('mer_id','uid','type','category'));
    }

    /*
     * 获取某个用户收藏产品
     * @param int uid 用户id
     * @param int $first 行数
     * @param int $limit 展示行数
     * @return array
     * */
    public static function getUserCollectMerchant($uid,$page,$limit)
    {
        $list = self::where('A.uid',$uid)
            ->field('B.id mid,B.mobile,B.store_name,B.store_logo,B.store_address,B.status,B.short_desc,B.sale_count,B.fav_count,B.comment_score,B.views,B.sort')->alias('A')
            ->where('A.type','collect')->where('A.category','product')
            ->order('A.add_time DESC')->join('__STORE_MERCHANT__ B','A.mer_id = B.id')
            ->page((int)$page,(int)$limit)->select()->toArray();
            foreach ($list as $k=>$merchant){
                if(!$merchant['mid']){
                    unset($list[$k]);
                }
            }
        return $list;

    }

}