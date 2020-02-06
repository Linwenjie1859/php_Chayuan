<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\merchant\model\system;


use traits\ModelTrait;
use basic\ModelBasic;
use behavior\admin\SystemBehavior;
use service\HookService;
use think\Session;

/**
 * Class SystemAdmin
 * @package app\merchant\model\system
 */
class SystemAdmin extends ModelBasic
{
    use ModelTrait;

    protected $insert = ['add_time'];

    public static function setAddTimeAttr($value)
    {
        return time();
    }

    public static function setRolesAttr($value)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }


    /**
     * @param $field
     * @return false|\PDOStatement|string|\think\Collection
     */
    public static function getOrdAdmin($field = 'real_name,id',$level = 0){
        return self::where('level','>=',$level)->field($field)->select();
    }

    public static function getTopAdmin($field = 'real_name,id')
    {
        return self::where('level',0)->field($field)->select();
    }

    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where){
        $model = new self;
        if($where['name'] != ''){
            $model = $model->where('account|real_name','LIKE',"%$where[name]%");
        }
        if($where['roles'] != '')
            $model = $model->where("CONCAT(',',roles,',')  LIKE '%,$where[roles],%'");
        $model = $model->where('level','=',$where['level'])->where('is_del',0);
        return self::page($model,function($admin,$key){
            $admin->roles = SystemRole::where('id','IN',$admin->roles)->column('role_name');
        },$where);
    }
}