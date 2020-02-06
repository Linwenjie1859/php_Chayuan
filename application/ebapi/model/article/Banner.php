<?php
namespace app\ebapi\model\article;

use traits\ModelTrait;
use basic\ModelBasic;


/**
 * TODO 小程序文章分类Model
 * Class ArticleCategory
 * @package app\ebapi\model\article
 */
class Banner extends ModelBasic
{
    use ModelTrait;

    /**
     * TODO 获取文章分类
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getBannerList($type){
        return self::where('hidden',0)->where('is_del',0)->where('status',1)->where('type',$type)->order('sort DESC')->field('id,title,image')->select();
    }


}