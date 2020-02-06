<?php
namespace app\admin\controller\store;

use app\admin\controller\AuthController;
use service\FormBuilder as Form;
use service\JsonService;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use app\admin\model\store\StoreMerchantType as StoreMerchantTypeModel;
use think\Url;
use app\admin\model\system\SystemAttachment;

/**
 * 产品分类控制器
 * Class StoreCategory
 * @package app\admin\controller\system
 */
class StoreMerchantType extends AuthController
{

    /**
     * 分类管理delete
     * */
    public function index(){
        $where = Util::getMore([
            ['status',''],
            ['name',''],
        ],$this->request);
        $this->assign('where',$where);
        $this->assign(StoreMerchantTypeModel::systemPage($where));
        return $this->fetch();
    }

    /**

     * 添加分类管理

     * */

    public function create(){
        $f = array();
        $f[] = Form::input('name','分类名称');
//        $f[] = Form::input('intr','分类简介')->type('textarea');
        $f[] = Form::frameImageOne('image','分类图片',Url::build('admin/widget.images/index',array('fodder'=>'image')))->icon('image');
        $f[] = Form::number('sort','排序',0);
        $f[] = Form::radio('status','状态',1)->options([['value'=>1,'label'=>'显示'],['value'=>0,'label'=>'隐藏']]);
        $form = Form::make_post_form('添加分类',$f,Url::build('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');

    }

    /**
     * s上传图片
     * */
    public function upload(){
        $res = Upload::image('file','store_merchant');
        if(!is_array($res)) return Json::fail($res);
        SystemAttachment::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],5,$res['image_type'],$res['time']);
        return Json::successful('图片上传成功!',['name'=>$res['name'],'url'=>Upload::pathToUrl($res['thumb_path'])]);
    }

    /**

     * 保存分类管理

     * */

    public function save(Request $request){
        $data = Util::postMore([
            'name',
            ['image',[]],
            ['sort',0],
            'status',],$request);
        if(!$data['name']) return Json::fail('请输入分类名称');
        if(count($data['image']) != 1) return Json::fail('请选择分类图片，并且只能上传一张');
        if($data['sort'] < 0) return Json::fail('排序不能是负数');
        $data['add_time'] = time();
        $data['image'] = $data['image'][0];
        $res = StoreMerchantTypeModel::set($data);
        return Json::successful('添加分类成功!');
    }

    /**

     * 修改分类

     * */

    public function edit($id){
        if(!$id) return $this->failed('参数错误');
        $article = StoreMerchantTypeModel::get($id)->getData();
        if(!$article) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::input('name','分类名称',$article['name']);
        $f[] = Form::frameImageOne('image','分类图片',Url::build('admin/widget.images/index',array('fodder'=>'image')),$article['image'])->icon('image');
        $f[] = Form::number('sort','排序',0);
        $f[] = Form::radio('status','状态',$article['status'])->options([['value'=>1,'label'=>'显示'],['value'=>0,'label'=>'隐藏']]);
        $form = Form::make_post_form('编辑分类',$f,Url::build('update',array('id'=>$id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');

    }


    /**
     * 保存修改信息
     * @param Request $request
     * @param $id
     * @throws \think\exception\DbException
     */
    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'name',
            ['image',[]],
            ['sort',0],
            'status',],$request);
        if(!$data['name']) return Json::fail('请输入分类名称');
        if(count($data['image']) != 1) return Json::fail('请选择分类图片，并且只能上传一张');
        if($data['sort'] < 0) return Json::fail('排序不能是负数');
        $data['image'] = $data['image'][0];
        if(!StoreMerchantTypeModel::get($id)) return Json::fail('编辑的记录不存在!');
//        if(!ArticleModel::saveBatchCid($id,implode(',',$data['new_id']))) return Json::fail('文章列表添加失败');
//        unset($data['new_id']);
        StoreMerchantTypeModel::edit($data,$id);
        return Json::successful('修改成功!');
    }

    /**
     * 删除分类
     * */
    public function delete($id)
    {
        $res = StoreMerchantTypeModel::delMerchant($id);
        if(!$res)
            return Json::fail(StoreMerchantTypeModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }
}
