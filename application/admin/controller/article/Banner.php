<?php

namespace app\admin\controller\article;

use app\admin\controller\AuthController;
use app\admin\model\system\SystemAttachment;
use service\FormBuilder as Form;
use service\UtilService as Util;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Request;
use think\Url;
use app\admin\model\article\Banner as BannerModel;


/**
 * 轮播图管理  控制器
 * */
class Banner extends AuthController

{

    /**
     * 轮播图管理
     * */
    public function index(){
        $where = Util::getMore([
            ['type',''],
            ['status',''],
            ['title',''],
        ],$this->request);
        $this->assign('where',$where);
        $this->assign(BannerModel::systemPage($where));
        return $this->fetch();
    }

    /**

     * 添加轮播图管理

     * */

    public function create(){
        $f = array();
        $f[] = Form::select('type','轮播图类型')->setOptions(function(){

            $menus[] = ['value'=>1,'label'=>'首页轮播图'];
            $menus[] = ['value'=>2,'label'=>'惠农产品轮播图'];
            return $menus;
        })->filterable(1);
        $f[] = Form::input('title','轮播图名称');
        $f[] = Form::input('intr','轮播图简介')->type('textarea');

        $f[] = Form::frameImageOne('image','轮播图图片',Url::build('admin/widget.images/index',array('fodder'=>'image')))->icon('image');
        $f[] = Form::number('sort','排序',0);
        $f[] = Form::radio('status','状态',1)->options([['value'=>1,'label'=>'显示'],['value'=>0,'label'=>'隐藏']]);
        $form = Form::make_post_form('添加轮播图',$f,Url::build('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');

    }

    /**
     * s上传图片
     * */
    public function upload(){
        $res = Upload::image('file','article');
        if(!is_array($res)) return Json::fail($res);
        SystemAttachment::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],5,$res['image_type'],$res['time']);
        return Json::successful('图片上传成功!',['name'=>$res['name'],'url'=>Upload::pathToUrl($res['thumb_path'])]);
    }

    /**

     * 保存轮播图管理

     * */

    public function save(Request $request){
        $data = Util::postMore([
            'title',
            'type',
            'intr',
            ['new_id',[]],
            ['image',[]],
            ['sort',0],
            'status',],$request);
        if(!$data['title']) return Json::fail('请输入轮播图名称');
        if(count($data['image']) != 1) return Json::fail('请选择轮播图图片，并且只能上传一张');
        if($data['sort'] < 0) return Json::fail('排序不能是负数');
        $data['add_time'] = time();
        $data['image'] = $data['image'][0];
        $new_id = $data['new_id'];
        unset($data['new_id']);
        $res = BannerModel::set($data);
        if(!ArticleModel::saveBatchCid($res['id'],implode(',',$new_id))) return Json::fail('文章列表添加失败');
        return Json::successful('添加轮播图成功!');
    }

    /**

     * 修改轮播图

     * */

    public function edit($id){
        if(!$id) return $this->failed('参数错误');
        $article = BannerModel::get($id)->getData();
        if(!$article) return Json::fail('数据不存在!');
        $f = array();
        $f[] = Form::select('type','父级id',(string)$article['type'])->setOptions(function(){
            $menus[] = ['value'=>1,'label'=>'首页轮播图'];
            $menus[] = ['value'=>2,'label'=>'惠农产品轮播图'];
            return $menus;
        })->filterable(1);
        $f[] = Form::input('title','轮播图名称',$article['title']);
        $f[] = Form::input('intr','轮播图简介',$article['intr'])->type('textarea');

        $f[] = Form::frameImageOne('image','轮播图图片',Url::build('admin/widget.images/index',array('fodder'=>'image')),$article['image'])->icon('image');
        $f[] = Form::number('sort','排序',0);
        $f[] = Form::radio('status','状态',$article['status'])->options([['value'=>1,'label'=>'显示'],['value'=>0,'label'=>'隐藏']]);
        $form = Form::make_post_form('编辑轮播图',$f,Url::build('update',array('id'=>$id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');

    }



    public function update(Request $request, $id)
    {
        $data = Util::postMore([
            'type',
            'title',
            'intr',
            ['image',[]],
            ['sort',0],
            'status',],$request);
        if(!$data['title']) return Json::fail('请输入轮播图名称');
        if(count($data['image']) != 1) return Json::fail('请选择轮播图图片，并且只能上传一张');
        if($data['sort'] < 0) return Json::fail('排序不能是负数');
        $data['image'] = $data['image'][0];
        if(!BannerModel::get($id)) return Json::fail('编辑的记录不存在!');

        BannerModel::edit($data,$id);
        return Json::successful('修改成功!');
    }

    /**
     * 删除轮播图
     * */
    public function delete($id)
    {
        $res = BannerModel::delArticleCategory($id);
        if(!$res)
            return Json::fail(BannerModel::getErrorInfo('删除失败,请稍候再试!'));
        else
            return Json::successful('删除成功!');
    }


}

