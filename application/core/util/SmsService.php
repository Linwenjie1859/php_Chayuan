<?php
/**
 *
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/10/24
 */

namespace app\core\util;


class SmsService
{

    const usid = '32';
    const key = '2A78D3A52F124B2F493ED21D3BCB4F98';
    const signname = "茶园";

    //短信发送
    public static function sendSms($content,$mobile,$templateid){

        $paydata = array();
        $paydata['usid'] = self::usid;
        $paydata['templateid'] = $templateid;
        $paydata['mobile'] = $mobile;//用户名
        $paydata['signname'] = self::signname;
        $paydata['terminal_time'] = time();
        $paydata['key_sign'] = self::key_sign($paydata,self::key);
        $paydata['content'] = $content;
        $paydata = json_encode($paydata);
        eblog('sendsms=>in',$paydata,'sms');
        $url = "http://lhjsms.shcmall.cn/api.php/index/order_sms/sms";
        $httpstr = http($url, $paydata);
        eblog('sendsms=>out',$httpstr,'sms');
        $rs = json_decode(trim($httpstr, chr(239) . chr(187) . chr(191)), true);
        return $rs;
    }

    //签名获取
    public static function key_sign($paydata,$key){
        $str = "";
        ksort($paydata);
        foreach($paydata as $k=>$v){
            $str = $str.$k."=".$v.'&';
        }
        $str = substr($str,0,strlen($str)-1);
        //新增验签
        return md5($str.$key);
    }
}