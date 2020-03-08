<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/02
 */
namespace app\admin\model\article;

use traits\ModelTrait;
use app\admin\model\article\Article as ArticleModel;
use basic\ModelBasic;
use service\UtilService as Util;

/**
 * 文章分类model
 * Class ArticleCategory
 * @package app\admin\model\wechat
 */
class Banner extends ModelBasic
{
    use ModelTrait;

    /**
     * 获取系统分页数据   分类
     * @param array $where
     * @return array
     */
    public static function systemPage($where = array()){
        $model = new self;
        if($where['title'] !== '') $model = $model->where('title','LIKE',"%$where[title]%");
        if($where['status'] !== '') $model = $model->where('status',$where['status']);
        if($where['type'] !== '') $model = $model->where('type',$where['type']);
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

}