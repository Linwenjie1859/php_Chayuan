<?php

namespace app\merchant\controller\store;

use app\merchant\controller\AuthController;
use service\UtilService as Util;
use service\PHPTreeService as Phptree;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use app\merchant\model\store\StoreMerchantType as StoreMerchantTypeModel;
use app\merchant\model\store\StoreMerchant as StoreMerchantModel;
use app\merchant\model\system\SystemAttachment;

/**
 * 店铺管理
 * Class WechatNews
 * @package app\merchant\controller\wechat
 */
class StoreMerchant extends AuthController
{
    /**
     * 显示后台管理员添加的店铺
     * @return mixed
     */
    public function index($pid = 0)
    {
        $where = Util::getMore([
            ['name',''],
            ['cid','']
        ],$this->request);
        $pid = $this->request->param('pid');
        $this->assign('where',$where);
        $where['merchant'] = 0;//区分是管理员添加的店铺显示  0 还是 商户添加的店铺显示  1
        $catlist = StoreMerchantTypeModel::where('is_del',0)->select()->toArray();
        //获取分类列表
        if($catlist){
            $tree = Phptree::makeTreeForHtml($catlist);
            $this->assign(compact('tree'));
            if($pid){
                $pids = Util::getChildrenPid($tree,$pid);
                $where['cid'] = ltrim($pid.$pids);
            }
        }else{
            $tree = [];
            $this->assign(compact('tree'));
        }


        $this->assign('cate',StoreMerchantTypeModel::getTierList());
        $this->assign(StoreMerchantModel::getAll($where));
        return $this->fetch();
    }

    /**
     * 展示页面   添加和删除
     * @return mixed
     */
    public function create(){
        $id = input('id');
        $cid = input('cid');
        $news = array();
        $news['id'] = '';
        $news['store_name'] = '';
        $news['store_address'] = '';
        $news['type'] = '';
        $news['store_logo'] = '';
        $news['mobile'] = '';
        $news['password'] = '';
        $news['name'] = '';
        $news['card_no'] = '';
        $news['bank_no'] = '';
        $news['bank'] = '';
        $news['bank_name'] = '';
        $news['bank_address'] = '';
        $news['cid'] = array();
        if($id){
            $news = StoreMerchantModel::where('id',$id)->field('*')->find();
            if(!$news) return $this->failedNotice('数据不存在!');
            $news['cid'] = explode(',',$news['type']);
        }
        $all = array();
        $select =  0;
        if(!$cid)
            $cid = '';
        else {
            if($id){
                $all = StoreMerchantTypeModel::where('id',$cid)->where('hidden','neq',0)->column('id,title');
                $select = 1;
            }else{
                $all = StoreMerchantTypeModel::where('id',$cid)->column('id,title');
                $select = 1;
            }

        }
        if(empty($all)){
            $select =  0;
            $list = StoreMerchantTypeModel::getTierList();
            $all = [];
            foreach ($list as $menu){
                $all[$menu['id']] = $menu['html'].$menu['name'];
            }
        }
        $this->assign('all',$all);
        $this->assign('news',$news);
        $this->assign('cid',$cid);
        $this->assign('select',$select);
        return $this->fetch();
    }

    /**
     * 上传店铺图片
     * @return \think\response\Json
     */
    public function upload_image(){
        $res = Upload::Image($_POST['file'],'wechat/image/'.date('Ymd'));
        if(!is_array($res)) return Json::fail($res);
        SystemAttachment::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],5,$res['image_type'],$res['time']);
        return Json::successful('上传成功!',['url'=>$res['dir']]);
    }

    /**
     * 添加和修改店铺
     * @param Request $request
     * @return \think\response\Json
     */
    public function add_new(Request $request){
        $post  = $request->post();
        $data = Util::postMore([
            ['id',0],
            ['cid',[]],
            'store_name',
            'store_address',
            'store_logo',
            'mobile',
            'password',
            'name',
            'card_no',
            'bank_no',
            'bank',
            'bank_name',
            'bank_address',
            ['views',0],
            ['sort',0],
            ['status',1],],$request);
        $data['type'] = implode(',',$data['cid']);
        if($data['id']){
            $id = $data['id'];
            unset($data['id']);
            $res1 = StoreMerchantModel::edit($data,$id,'id');
            if($res1)
                $res = true;
            else
                $res =false;

            if($res)
                return Json::successful('修改店铺成功!',$id);
            else
                return Json::fail('修改店铺失败，您并没有修改什么!',$id);
        }else{
            $data['addtime'] = time();
            $data['admin_id'] = $this->adminId;
            $res1 = StoreMerchantModel::set($data);
            if($res1)
                $res = true;
            else
                $res =false;
            if($res)
                return Json::successful('添加店铺成功!',$res1->id);
            else
                return Json::successful('添加店铺失败!',$res1->id);
        }
    }

    /**
     * 删除店铺
     * @param $id
     * @return \think\response\Json
     */
    public function delete($id)
    {
        $res = StoreMerchantModel::del($id);
        if(!$res)
            return Json::fail('删除失败,请稍候再试!');
        else
            return Json::successful('删除成功!');
    }
    
}