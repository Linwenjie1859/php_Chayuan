<?php

/**

 *

 * @author: xaboy<365615158@qq.com>

 * @day: 2017/11/02

 */

namespace app\ebapi\model\store;

use app\admin\model\store\StoreMerchantType;
use app\admin\model\system\SystemAdmin;
use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;

/**
 * 店铺管理 Model
 * Class WechatNews
 * @package app\admin\model\wechat
 */
class StoreMerchant extends ModelBasic {

    use ModelTrait;

    /**
     * TODO 获取文章分类
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getMerchantIndex($uid,$first = 0, $limit = 3){
        $category =  StoreMerchantType::where('hidden',0)->where('is_del',0)->where('status',1)
            ->where('pid',0)->order('sort DESC')->field('id,name,image')->select();

        foreach ($category as $k=>$v){
            $merchant = self::alias("a")->field('a.id,a.store_name,a.store_logo,a.views,a.lng,a.lat,b.uid as is_collect')->where('a.status', 1)
                    ->join("store_merchant_relation b","a.id = b.mer_id and b.uid = $uid",'left')
                    ->where(['a.type'=>$v['id']])->order('a.sort DESC,a.addtime DESC')->limit($first, $limit)->select();
            foreach ($merchant as $key=>$value){
                $merchant[$key]['is_collect'] = $value['is_collect'] ? "1" : "0";
            }
            $category[$k]['list'] = $merchant;
        }
        return $category;
    }

    /**
     * 获取店铺基本信息
     * @param $id
     * @param $uid
     * @return array|false|\PDOStatement|string|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getMerchantInfo($id,$uid){

        $merchant = self::alias("a")->field('a.id,a.store_name,a.store_logo,a.views,a.fav_count,a.sale_count,a.lng,a.lat,b.uid as is_collect')->where('a.status', 1)
            ->join("store_merchant_relation b","a.id = b.mer_id and b.uid = $uid",'left')
            ->where(['id'=>$id])->find();
            
        return $merchant;
    }
}