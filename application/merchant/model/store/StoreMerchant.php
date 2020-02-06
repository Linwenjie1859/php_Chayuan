<?php

/**

 *

 * @author: xaboy<365615158@qq.com>

 * @day: 2017/11/02

 */

namespace app\merchant\model\store;

use app\merchant\model\system\SystemAdmin;
use traits\ModelTrait;
use basic\ModelBasic;
use think\Db;
use service\HookService;
use think\Session;

/**
 * 店铺管理 Model
 * Class WechatNews
 * @package app\merchant\model\wechat
 */
class StoreMerchant extends ModelBasic {

    use ModelTrait;

    /**
     * 用户登陆
     * @param string $account 账号
     * @param string $pwd 密码
     * @param string $verify 验证码
     * @return bool 登陆成功失败
     */
    public static function login($mobile,$password)
    {
        $adminInfo = self::get(compact('mobile'));
        if(!$adminInfo) return self::setErrorInfo('登陆的账号不存在!');
        if($adminInfo['password'] != $password) return self::setErrorInfo('账号或密码错误，请重新输入');
        if(!$adminInfo['status']) return self::setErrorInfo('该账号已被关闭!');
        self::setLoginInfo($adminInfo);
//        HookService::afterListen('system_admin_login',$adminInfo,null,false,SystemBehavior::class);
        return true;
    }

    /**
     *  保存当前登陆用户信息
     */
    public static function setLoginInfo($adminInfo)
    {
        Session::set('adminId',$adminInfo['id']);
        Session::set('adminInfo',$adminInfo);
    }

    /**
     * 清空当前登陆用户信息
     */
    public static function clearLoginInfo()
    {
        Session::delete('adminInfo');
        Session::delete('adminId');
        Session::clear();
    }

    /**
     * 检查用户登陆状态
     * @return bool
     */
    public static function hasActiveAdmin()
    {
        return Session::has('adminId') && Session::has('adminInfo');
    }

    /**
     * 获得登陆用户信息
     * @return mixed
     */
    public static function activeAdminInfoOrFail()
    {
        $adminInfo = Session::get('adminInfo');
        if(!$adminInfo)  exception('请登陆');
        if(!$adminInfo['status']) exception('该账号已被关闭!');
        return $adminInfo;
    }

    /**
     * 获得登陆用户Id 如果没有直接抛出错误
     * @return mixed
     */
    public static function activeAdminIdOrFail()
    {
        $adminId = Session::get('adminId');
        if(!$adminId) exception('访问用户为登陆登陆!');
        return $adminId;
    }

    /**
     * @return array
     */
    public static function activeAdminAuthOrFail()
    {
        $adminInfo = self::activeAdminInfoOrFail();
        return $adminInfo->level === 0 ? SystemRole::getAllAuth() : SystemRole::rolesByAuth($adminInfo->roles);
    }

    /**
     * 获得有效管理员信息
     * @param $id
     * @return static
     */
    public static function getValidAdminInfoOrFail($id)
    {
        $adminInfo = self::get($id);
        if(!$adminInfo) exception('用户不能存在!');
        if(!$adminInfo['status']) exception('该账号已被关闭!');
        return $adminInfo;
    }

    /**
     * 获取配置分类
     * @param array $where
     * @return array
     */
    public static function getAll($where = array()){
        $model = new self;
        if($where['name'] !== '') $model = $model->where('name','LIKE',"%$where[name]%");
        if($where['cid'] !== '') $model = $model->where('type','in',$where['cid']);
        $model = $model->where('status',1);
        return self::page($model,function($item){
            $item['admin_name'] = '总后台管理员---》'.SystemAdmin::where('id',$item['admin_id'])->value('real_name');
            $item['catename'] = Db::name('StoreMerchantType')->where('id',$item['type'])->value('name');
        },$where);
    }

    /**
     * 删除店铺
     * @param $id
     * @return bool
     */
    public static function del($id){
        return self::edit(['status'=>0],$id,'id');
    }

    /**
     * 获取指定字段的值
     * @return array
     */
    public static function getNews()
    {
        return self::where('status',1)->where('hide',0)->order('id desc')->column('id,name');
    }

    /**
     * 给表中的字符串类型追加值
     * 删除所有有当前分类的id之后重新添加
     * @param $cid
     * @param $id
     * @return bool
     */
    public static function saveBatchCid($cid,$id){
        $res_all = self::where('cid','LIKE',"%$cid%")->select();//获取所有有当前分类的店铺
        foreach ($res_all as $k=>$v){
            $cid_arr = explode(',',$v['cid']);
            if(in_array($cid,$cid_arr)){
                $key = array_search($cid, $cid_arr);
                array_splice($cid_arr, $key, 1);
            }
            if(empty($cid_arr)) {
                $data['cid'] = 0;
                self::edit($data,$v['id']);
            }else{
                $data['cid'] = implode(',',$cid_arr);
                self::edit($data,$v['id']);
            }
        }
        $res = self::where('id','IN',$id)->select();
        foreach ($res as $k=>$v){
            if(!in_array($cid,explode(',',$v['cid']))){
                if(!$v['cid']){
                    $data['cid'] = $cid;
                }else{
                    $data['cid'] = $v['cid'].','.$cid;
                }
                self::edit($data,$v['id']);
            }
        }
        return true;
    }

    public static function setContent($id,$content){
        $count = Db::name('ArticleContent')->where('nid',$id)->count();
        $data['nid'] = $id;
        $data['content'] = $content;
//        dump($data);
        if($count){
            $res = Db::name('ArticleContent')->where('nid',$id)->setField('content',$content);
            if($res !== false) $res = true;
        }
        else
            $res = Db::name('ArticleContent')->insert($data);
//        echo Db::getLastSql();
//        exit();
        return $res;
    }

    public static function merchantPage($where = array()){
        $model = new self;
        if($where['name'] !== '') $model = $model->where('name','LIKE',"%$where[name]%");
        if($where['cid'] !== '') $model = $model->where('cid','LIKE',"%$where[cid]%");
        $model = $model
            ->where('status',1)
            ->where('hide',0)
            ->where('admin_id',$where['admin_id'])
            ->where('mer_id',$where['mer_id']);
        return self::page($model,function($item){
            $item['content'] = Db::name('ArticleContent')->where('nid',$item['id'])->value('content');
        },$where);
    }

    /**
     * 获取指定文章列表  店铺管理使用
     * @param string $id
     * @param string $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getArticleList($id = '',$field = 'name,author,image_input,synopsis,id'){
        $list = self::where('id','IN',$id)->field($field)->select();
        foreach ($list as $k=>$v){
            $list[$k]['content'] = Db::name('ArticleContent')->where('nid',$v['id'])->value('content');
        }
        return $list;
    }
}