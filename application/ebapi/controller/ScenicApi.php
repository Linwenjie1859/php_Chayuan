<?php

namespace app\ebapi\controller;

use app\ebapi\model\scenic\Scenic as ScenicModel;
use app\ebapi\model\scenic\Scenic\ScenicCategory as ScenicCategoryModel;
use service\JsonService;
use service\UtilService;

/**
 * TODO 小程序文章api接口
 * Class ArticleApi
 * @package app\ebapi\controller
 */
class ScenicApi extends Basic
{

    public function get_scenic_by_cid($cid){
        if($cid<0) return JsonService::fail('请传递参数!');
        $res = ScenicModel::selectByfield($cid,'cid');
        if($res){
            return JsonService::successful('ok',$res);
        }else{
            return JsonService::fail('获取内容失败',$res);
        }
    }



}