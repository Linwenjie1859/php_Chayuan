<?php
namespace app\ebapi\model\scenic;

use think\Db;
use traits\ModelTrait;
use basic\ModelBasic;

/**
 * Class Article
 * @package app\ebapi\model\article
 */
class ScenicCategory extends ModelBasic
{

    use ModelTrait;

    /**
     * @Notes:  获取排序靠前的景区分类
     * @author Handsome Lin
     * @date 2020/3/7 11:54
     */
    public static function getViewScenic($field = '*',$limit=''){
        $model = new self();
        $model = $model->field($field);
        $model = $model->where('status', 1);
        $model = $model->where('hidden', 0);
        $model = $model->order('sort DESC');
        if($limit!='')$model = $model->limit($limit);
        return $model->select();
    }
}
