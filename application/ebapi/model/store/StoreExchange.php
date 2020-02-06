<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/12/20
 */

namespace app\ebapi\model\store;


use basic\ModelBasic;

use traits\ModelTrait;

class StoreExchange extends ModelBasic
{
    use ModelTrait;

    public static function userBuyOnTree($uid,$product_id,$id,$num=1){
        $exchange=self::where('uid',$uid)->where('product_id',$product_id)->field('tree_num,order_id')->find();
        if($exchange['tree_num']==null){          //如果查询出来的棵数为null,则用户未购买过茶树
            return self::insert(['product_id'=>$product_id,'uid'=>$uid,'tree_num'=>1,'order_id'=>$id]);
        }else{                        //用户先前购买过茶树，本次购买棵数加1
            self::where('uid',$uid)->where('product_id',$product_id)->update(['order_id'=>$exchange['order_id'].','.$id]);
            return self::where('uid',$uid)->where('product_id',$product_id)->setInc('tree_num',$num);
        }
    }

    public static function userTreeAllInfo($uid,$field='*'){
        return $result = self::where('uid',$uid)->field($field)->select();
    }



}