<?php
namespace app\ebapi\model\article;

use traits\ModelTrait;
use basic\ModelBasic;


/**
 * TODO 小程序文章分类Model
 * Class ArticleCategory
 * @package app\ebapi\model\article
 */
class ArticleCategory extends ModelBasic
{
    use ModelTrait;

    /**
     * TODO 获取文章分类
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getArticleCategory(){
        return self::where('hidden',0)->where('is_del',0)->where('status',1)->where('pid',0)->order('sort DESC')->field('id,title,image')->select();
    }

    /**
     * TODO  获取分类字段
     * @param $id $id 编号
     * @param string $field $field 字段
     * @return mixed|string
     */
    public static function getArticleCategoryField($id,$field = 'title'){
        if(!$id) return '';
        return self::where('id',$id)->value($field);
    }

    /**
     * TODO 获取文章分类内容
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getArticleIndex($first = 0, $limit = 3){
        $category = self::getArticleCategory(); 
        foreach ($category as $k=>$v){
            $article = Article::field('*')->where('status', 1)
                    ->where('hide', 0)->where(['cid'=>$v['id']])->order('sort DESC,add_time DESC')->limit($first, $limit)->select();
            foreach ($article as $key=>$value){
                $article[$key]['add_time'] = date("Y-m-d",$value['add_time']);
            }
            $category[$k]['list'] = $article;
        }
        return $category;
    }


}