<?php
namespace app\ebapi\controller;


use app\admin\model\system\SystemAttachment;
use app\core\model\routine\RoutineCode;//待完善
use app\ebapi\model\article\Banner;
use app\ebapi\model\store\StoreCategory;
use app\ebapi\model\store\StoreMerchant;
use app\ebapi\model\store\StoreMerchantRelation;
use app\ebapi\model\store\StoreOrderCartInfo;
use app\ebapi\model\store\StoreProduct;
use app\ebapi\model\store\StoreProductAttr;
use app\ebapi\model\store\StoreProductRelation;
use app\ebapi\model\store\StoreProductReply;
use app\core\util\GroupDataService;
use service\JsonService;
use app\core\util\SystemConfigService;
use service\UploadService;
use service\UtilService;
use app\core\util\MiniProgramService;
use think\Cache;

/**
 * 小程序产品和产品分类api接口
 * Class StoreApi
 * @package app\ebapi\controller
 *
 */
class MerchantApi extends AuthController
{

    public static function whiteList()
    {
        return [
            'get_merchant_index',
            'merchant_info',
            'get_merchant_index',
        ];
    }

    /**
     * @Modify: Mr. Lin
     * @function: 惠农产品首页，获取店铺列表
     * @instructions: 
     * @param {type} 
     * @return: JSON
     */
    public function get_merchant_index(){
        $merInfo = StoreMerchant::getMerchantIndex($this->userInfo['uid']);

        $banner = GroupDataService::getData('benifit_hot_banner')?:[];//TODO 首页banner图
        return $this->successful(compact('merInfo','banner'));
    }

    /**
     * 获取店铺基本信息
     * @param $id
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function merchant_info($id){

        $merInfo = StoreMerchant::getMerchantInfo($id,$this->userInfo['uid']);

        $similarity = StoreProduct::where(['mer_id'=>$merInfo['id'],'is_best'=>'1','is_del'=>'0'])->field('id,store_name,cate_id,image,ficti as sales,price,stock,store_info,ot_price,type')->order(' sort DESC, id DESC')->limit(4)->select();

        return JsonService::successful(compact('merInfo','similarity'));
    }

   /**
     * @Modify: Mr. Lin
     * @function: 添加店铺收藏
     * @instructions: 
     * @param {type} 
     * @return: JSON
     */
    public function collect_merchant($merId,$category = 'product'){
        if(!$merId || !is_numeric($merId)) return JsonService::fail('参数错误');
        $res = StoreMerchantRelation::merchantRelation($merId,$this->userInfo['uid'],'collect',$category);
        if(!$res) return JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful();
    }

    /**
     * @Modify: Mr. Lin
     * @function: 取消店铺收藏
     * @instructions: 
     * @param {type} 
     * @return: JSON
     */
    public function uncollect_merchant($merId,$category = 'product'){
        if(!$merId || !is_numeric($merId)) return JsonService::fail('参数错误');
        $res = StoreMerchantRelation::unMerchantRelation($merId,$this->userInfo['uid'],'collect',$category);
        if(!$res) return JsonService::fail(StoreProductRelation::getErrorInfo());
        else return JsonService::successful();
    }

    /**
     * @Modify: Mr. Lin
     * @function: 获取收藏店铺
     * @instructions: 
     * @param {type} 
     * @return: JSON
     */
    public function get_user_collect_merchant($page = 0,$limit = 8)
    {   
        $list = StoreMerchantRelation::getUserCollectMerchant($this->uid,$page,$limit);
        foreach ($list as $k=>$merchant){
            $list[$k]['similarity'] = StoreProduct::where(['mer_id'=>$merchant['mid'],'is_best'=>'1','is_del'=>'0'])->field('id,store_name,cate_id,image,ficti as sales,price,stock,store_info,ot_price,type')->order(' sort DESC, id DESC')->limit(4)->select();
        }
        return JsonService::successful($list);
    }

}