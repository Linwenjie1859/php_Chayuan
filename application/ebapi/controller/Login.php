<?php

namespace app\ebapi\controller;


use app\core\util\SmsService;
use app\core\util\SendTemplateSMS;
use app\ebapi\model\user\User;
use think\Cache;
use think\Controller;
use think\Request;
use service\JsonService;
use service\UtilService;
use app\core\util\MiniProgramService;
use app\core\util\TokenService;
use app\ebapi\model\user\WechatUser;
use app\core\logic\Login as CoreLogin;

class Login extends Controller
{

    /*
     * 执行登录
     * */
    public function _empty($name)
    {
        CoreLogin::login_ing($name);
    }

    /**
     * 获取用户信息
     * @param Request $request
     * @return \think\response\Json
     */
    public function index(Request $request){
        //待完善
        $data = UtilService::postMore([
            ['spid',0],
            ['code',''],
            ['iv',''],
            ['encryptedData',''],
            ['cache_key',''],
        ],$request);//获取前台传的code
        if(!Cache::has('eb_api_code_'.$data['cache_key'])) return JsonService::status('410','获取会话密匙失败');
        $data['session_key']=Cache::get('eb_api_code_'.$data['cache_key']);
        try{
            //解密获取用户信息
            $userInfo = $this->decryptCode($data['session_key'], $data['iv'], $data['encryptedData']);
        }catch (\Exception $e){
            if($e->getCode()=='-41003') return JsonService::status('410','获取会话密匙失败');
        }
        if(!isset($userInfo['openId'])) return JsonService::fail('openid获取失败');
        if(!isset($userInfo['unionId']))  $userInfo['unionId'] = '';
        $userInfo['session_key'] = $data['session_key'];
        $userInfo['spid'] = $data['spid'];
        $userInfo['code'] = $data['code'];
        $dataOauthInfo = WechatUser::routineOauth($userInfo);
        $userInfo['uid'] = $dataOauthInfo['uid'];
        $userInfo['page'] = $dataOauthInfo['page'];
        $userInfo['token'] = TokenService::getToken($userInfo['uid'],$userInfo['openId']);
        if($userInfo['token']===false) return JsonService::fail('获取用户访问token失败!');
        $userInfo['status'] = WechatUser::isUserStatus($userInfo['uid']);
        return JsonService::successful($userInfo);
    }

    /**
     * 手机号注册
     * @param Request $request
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register_by_mobile(Request $request){
        $userInfo = UtilService::postMore([
            ['phone',''],
            ['pwd',''],
            ['code',''],
        ],$request);//获取前台传的code
        if(!$userInfo['phone']) return JsonService::fail('请输入手机号');
        if(!$userInfo['pwd']) return JsonService::fail('请输入密码');
        if(!$userInfo['code']) return JsonService::fail('请输入手机验证码');

        if(cache("register".$userInfo['phone']) != $userInfo['code']){
            return JsonService::fail('验证码错误！');
        }

        $user = User::where(["phone"=>$userInfo['phone']])->find(); //where找不到
        if($user){
            return JsonService::fail('手机号已注册！');
        }
        $userInfo['pwd'] = md5($userInfo['pwd']);
        $userInfo['account'] = 'fy'.$userInfo['phone'].time();
        $dataOauthInfo = User::set($userInfo);
        $userInfo['uid'] = $dataOauthInfo['uid'];
        $userInfo['token'] = TokenService::getToken($userInfo['uid'],$userInfo['phone']);
        if($userInfo['token']===false) return JsonService::fail('获取用户访问token失败!');
        $userInfo['status'] = WechatUser::isUserStatus($userInfo['uid']);
        return JsonService::successful($userInfo);
    }

    /**
     * App登录
     * @param Request $request
     */
    public function login_by_mobile_pwd(Request $request){
        $userInfo = UtilService::postMore([
            ['phone',''],
            ['pwd',''],
        ],$request);//获取前台传的code
        if(!$userInfo['phone']) return JsonService::fail('请输入手机号');
        if(!$userInfo['pwd']) return JsonService::fail('请输入验证码');

        $user = User::where(["phone"=>$userInfo['phone']])->find();
        if(!$user){
            return JsonService::fail('手机号不存在！');
        }
        if(md5($userInfo['pwd']) != $user['pwd']){
            return JsonService::fail('密码错误！');
        }

        $userInfo['uid'] = $user['uid'];
        $userInfo['token'] = TokenService::getToken($user['uid'],$userInfo['phone']);
        if($userInfo['token']===false) return JsonService::fail('获取用户访问token失败!');
        $userInfo['status'] = WechatUser::isUserStatus($userInfo['uid']);
        return JsonService::successful($userInfo);
    }

    /**
     * App登录
     * @param Request $request
     */
    public function login_by_mobile_code(Request $request){
        $userInfo = UtilService::postMore([
            ['phone',''],
            ['code',''],
        ],$request);//获取前台传的code
        if(!$userInfo['phone']) return JsonService::fail('请输入手机号');
        if(!$userInfo['pwd']) return JsonService::fail('请输入验证码');

        $user = User::where(["phone"=>$userInfo['phone']])->find();
        if(!$user){
            return JsonService::fail('手机号不存在！');
        }

        if(cache("login".$userInfo['phone']) != $userInfo['code']){
            return JsonService::fail('验证码错误！');
        }

        $userInfo['uid'] = $user['uid'];
        $userInfo['token'] = TokenService::getToken($user['uid'],$userInfo['phone']);
        if($userInfo['token']===false) return JsonService::fail('获取用户访问token失败!');
        $userInfo['status'] = WechatUser::isUserStatus($userInfo['uid']);
        return JsonService::successful($userInfo);
    }

    /**
     * 获取登录验证码
     * @param Request $request
     */
    public function get_register_code(Request $request){
        $userInfo = UtilService::postMore([
            ['phone',''],
        ],$request);//获取前台传的code
        if(!$userInfo['phone']) return JsonService::fail('请输入手机号');

        $code = rand(100000,999999);
        SendTemplateSMS::sendTemplateSMS($userInfo['phone'],array($code,3),1);

        cache("register".$userInfo['phone'],$code,300);
        return JsonService::successful('发送成功！');
    }

    /**
     * 获取登录验证码
     * @param Request $request
     */
    public function get_login_code(Request $request){
        $userInfo = UtilService::postMore([
            ['phone',''],
        ],$request);//获取前台传的code
        if(!$userInfo['phone']) return JsonService::fail('请输入手机号');

        $code = rand(100000,999999);
        SmsService::sendSms(['code'=>$code],$userInfo['phone'],'32');

        cache("login".$userInfo['phone'],$code,300);

        return JsonService::successful('发送成功！');
    }

    /**
     * App登录
     * @param Request $request
     */
    public function login_by_app(Request $request){
        $userInfo = UtilService::postMore([
            ['nickName',0],
            ['gender',''],
            ['language',''],
            ['city',''],
            ['province',''],
            ['country',''],
            ['avatarUrl',''],
            ['openId',''],
            ['session_key',''],
            ['unionId',''],
            ['user_type',''],
            ['code',''],
            ['spid',''],
        ],$request);//获取前台传的code
        if(!isset($userInfo['openId'])) return JsonService::fail('openid获取失败');
        $dataOauthInfo = WechatUser::routineOauth($userInfo);
        $userInfo['uid'] = $dataOauthInfo['uid'];
        $userInfo['token'] = TokenService::getToken($userInfo['uid'],$userInfo['openId']);
        if($userInfo['token']===false) return JsonService::fail('获取用户访问token失败!');
        $userInfo['status'] = WechatUser::isUserStatus($userInfo['uid']);
        return JsonService::successful($userInfo);
    }

    /**
     * 根据前台传code  获取 openid 和  session_key //会话密匙
     * @param string $code
     * @return array|mixed
     */
    public function setCode(Request $request){
        list($code) = UtilService::postMore([['code', '']], $request, true);//获取前台传的code
        if ($code == '') return JsonService::fail('');
        try{
            $userInfo = MiniProgramService::getUserInfo($code);
        }catch (\Exception $e){
            return JsonService::fail('获取session_key失败，请检查您的配置！',['line'=>$e->getLine(),'message'=>$e->getMessage()]);
        }
        $cache_key = md5(time().$code);
        if (isset($userInfo['session_key'])){
            Cache::set('eb_api_code_'.$cache_key, $userInfo['session_key'], 86400);
            return JsonService::successful(['cache_key'=>$cache_key]);
        }else
            return JsonService::fail('获取会话密匙失败');
    }

    /**
     * 解密数据
     * @param string $code
     * @return array|mixed
     */
    public function decryptCode($session = '', $iv = '', $encryptData = '')
    {
        if (!$session) return JsonService::fail('session参数错误');
        if (!$iv) return JsonService::fail('iv参数错误');
        if (!$encryptData) return JsonService::fail('encryptData参数错误');
        return MiniProgramService::encryptor($session, $iv, $encryptData);
    }

}