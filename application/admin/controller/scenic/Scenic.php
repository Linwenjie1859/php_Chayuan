<?php

namespace app\admin\controller\scenic;

use app\admin\controller\AuthController;
use app\admin\model\article\ArticleCategory as ArticleCategoryModel;
use app\admin\model\store\StoreCategory as CategoryModel;
use app\admin\model\store\StoreMerchant as MerchantModel;
use app\admin\model\store\StoreProduct as ProductModel;
use service\FormBuilder as Form;
use service\UtilService as Util;
use service\PHPTreeService as Phptree;
use service\JsonService as Json;
use service\UploadService as Upload;
use think\Db;
use think\Request;
use app\admin\model\scenic\ScenicCategory as ScenicCategoryModel;
use app\admin\model\scenic\Scenic as ScenicModel;
use app\admin\model\system\SystemAttachment;

use think\Url;

/**
 * 图文管理
 * Class WechatNews
 * @package app\admin\controller\wechat
 */
class Scenic extends AuthController
{
    /**
     * @describe    显示景点管理
     * 2020/3/5 10:48
     * @return
     * @author Linwenjie
     */
    public function index()
    {
        $where = Util::getMore([
            ['name',''],
            ['cid','']
        ],$this->request);
        $pid = $this->request->param('pid');
        $this->assign('where',$where);
        $catlist = ScenicCategoryModel::where('is_del',0)->select()->toArray();
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
        $this->assign(ScenicModel::getAll($where));
        return $this->fetch();
    }

    /**
     * @describe    添加景区页面
     * 2020/3/5 15:50
     * @return
     * @author Linwenjie
     */
    public function create()
    {
        $field = [
            Form::input('name', '景点名称')->col(Form::col(24)),
            Form::select('cid', '景区分类')->setOptions(function () {
                $list = ScenicCategoryModel::getTierList();
                $menus = [];
                foreach ($list as $menu) {
                    $menus[] = ['value' => $menu['id'], 'label' => $menu['html'] . $menu['title']];
                }
                return $menus;
            })->filterable(1)->multiple(0),
            Form::input('telephone', '联系电话')->col(Form::col(24)),
            Form::input('synopsis', '景点简介')->type('textarea'),
            Form::frameImageOne('image_input', '产品主图片(305*305px)', Url::build('admin/widget.images/index', array('fodder' => 'image_input')))->icon('image')->width('100%')->height('500px'),
            Form::frameImages('slider_image', '产品轮播图(640*640px)', Url::build('admin/widget.images/index', array('fodder' => 'slider_image')))->maxLength(5)->icon('images')->width('100%')->height('500px')->spin(0),


            Form::number('visit', '浏览次数')->min(0)->col(8),
            Form::number('sort', '排序')->col(8),
            Form::number('ticket_price', '门票价钱')->col(8),

            Form::radio('is_hot', '是否热门', 0)->options([['label' => '是', 'value' => 1], ['label' => '否', 'value' => 0]])->col(8),
            Form::radio('status', '是否正常', 0)->options([['label' => '是', 'value' => 1], ['label' => '否', 'value' => 0]])->col(8),
            Form::radio('hide', '是否隐藏', 0)->options([['label' => '是', 'value' => 1], ['label' => '否', 'value' => 0]])->col(8),
        ];
        $form = Form::make_post_form('添加景点', $field, Url::build('save'), 2);
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * @describe    保存景区信息
     * 2020/3/5 15:50
     * @return
     * @author Linwenjie
     */
    public function save(Request $request)
    {
        $data = Util::postMore([
            'name',
            'cid',
            'telephone',
            'synopsis',
            ['image_input', []],
            ['slider_image', []],
            ['visit', 0],
            ['sort', 0],
            ['ticket_price', 0],
            ['is_hot', 0],
            ['status', 0],
            ['hide', 0],
        ], $request);
        if (!$data['name']) return Json::fail('请输入景点名称');
        if (!$data['cid']) return Json::fail('请选择景点分类');
        if (!$data['telephone']) return Json::fail('请输入景点联系电话');
        if (!$data['synopsis']) return Json::fail('请选择景点简介');

        if (count($data['image_input']) < 1) return Json::fail('请上传产品图片');
        if (count($data['slider_image']) < 1) return Json::fail('请上传产品轮播图');

        if ($data['ticket_price'] == '' || $data['ticket_price'] < 0) return Json::fail('请输景区门票价格');
        $data['image_input'] = $data['image_input'][0];
        $data['slider_image'] = json_encode($data['slider_image']);
        $data['add_time'] = time();
        $data['description'] = '';
        $res = ScenicModel::set($data);
        if($res){
            return Json::successful('添加景区成功!');
        }else{
            return Json::fail('添加景区失败!');
        }

    }


    /**
     * 上传图文图片
     * @return \think\response\Json
     */
    public function upload_image(){
        $res = Upload::Image($_POST['file'],'wechat/image/'.date('Ymd'));
        if(!is_array($res)) return Json::fail($res);
        SystemAttachment::attachmentAdd($res['name'],$res['size'],$res['type'],$res['dir'],$res['thumb_path'],5,$res['image_type'],$res['time']);
        return Json::successful('上传成功!',['url'=>$res['dir']]);
    }

    /**
     * 添加和修改图文
     * @param Request $request
     * @return \think\response\Json
     */
    public function add_new(Request $request){
        $post  = $request->post();
        $data = Util::postMore([
            ['id',0],
            ['cid',[]],
            'title',
            'author',
            'image_input',
            'content',
            'synopsis',
            'share_title',
            'share_synopsis',
            ['visit',0],
            ['sort',0],
            'url',
            ['is_banner',0],
            ['is_hot',0],
            ['status',1],],$request);
        $data['cid'] = implode(',',$data['cid']);
        $content = $data['content'];
        unset($data['content']);
        if($data['id']){
            $id = $data['id'];
            unset($data['id']);
            ArticleModel::beginTrans();
            $res1 = ArticleModel::edit($data,$id,'id');
            $res2 = ArticleModel::setContent($id,$content);
            if($res1 && $res2)
                $res = true;
            else
                $res =false;
//            dump($res);
//            exit();
            ArticleModel::checkTrans($res);
            if($res)
                return Json::successful('修改图文成功!',$id);
            else
                return Json::fail('修改图文失败，您并没有修改什么!',$id);
        }else{
            $data['add_time'] = time();
            $data['admin_id'] = $this->adminId;
            ArticleModel::beginTrans();
            $res1 = ArticleModel::set($data);
            $res2 = false;
            if($res1)
                $res2 = ArticleModel::setContent($res1->id,$content);
            if($res1 && $res2)
                $res = true;
            else
                $res =false;
            ArticleModel::checkTrans($res);
            if($res)
                return Json::successful('添加图文成功!',$res1->id);
            else
                return Json::successful('添加图文失败!',$res1->id);
        }
    }

    /**
     * 删除图文
     * @param $id
     * @return \think\response\Json
     */
    public function delete($id)
    {
        $res = ArticleModel::del($id);
        if(!$res)
            return Json::fail('删除失败,请稍候再试!');
        else
            return Json::successful('删除成功!');
    }

    public function merchantIndex(){
        $where = Util::getMore([
            ['title','']
        ],$this->request);
        $this->assign('where',$where);
        $where['cid'] = input('cid');
        $where['merchant'] = 1;//区分是管理员添加的图文显示  0 还是 商户添加的图文显示  1
        $this->assign(ArticleModel::getAll($where));
        return $this->fetch();
    }
}