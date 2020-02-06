<?php
namespace app\ebapi\controller;


use service\JsonService;
use service\UtilService;
use think\Request;

class Pay extends AuthController
{
    const APPID = '00000000';
    const CUSID = '990440153996000';
    const APPKEY = '43df939f1e7f5c6909b3f4b63f893994';
    const APIURL = "https://vsp.allinpay.com/apiweb/unitorder";//生产环境
    const APIVERSION = '11';

    public function pay(){
        $data = UtilService::postMore([
            ['reqsn',''],
            ['sumPrice',1],
            ['paytype','A03'],
        ], $this->request);
        $params = array();
        $params["cusid"] = self::CUSID;
        $params["appid"] = self::APPID;
        $params["version"] = self::APIVERSION;
        $params["trxamt"] = "1";	//金额
        $params["reqsn"] = $data['reqsn'];//订单号（交易流水号）,自行生成。

        $params["paytype"] = $data['paytype'];  //支付的类型
        $params["randomstr"] = date("YmdHis").rand(10000,999999);//商户自行生成的随机字符串

        $params["body"] = "商品名称";
        $params["remark"] = "备注信息";
        $params["validtime"] = 5;		//订单有效时间，以分为单位，不填默认为5分钟

        $params["acct"] = "";
        $params["notify_url"] = "";

        $params["sub_appid"] = "";
        $params["goods_tag"] = "";
        $params["benefitdetail"] = "";
        $params["chnlstoreid"] = "";
        $params["subbranch"] = "";
        $params["extendparams"] = "";
        $params["cusip"] = "";
        $params["idno"] = "";
        $params["truename"] = "";
        $params["asinfo"] = "";
        $params['fqnum']='';
        $params['signtype']='';
        $params["sign"] = self::SignArray($params,self::APPKEY);//签名
        $paramsStr = self::ToUrlParams($params);
        $url = self::APIURL . '/pay';
        $rsp = self::request($url,$paramsStr);
        $rspArray=json_decode($rsp,true);
        if(self::validSign($rspArray)){
            return JsonService::success('ok',$rspArray);
        }else{
            return JsonService::fail('服务器异常');
        }
    }
    public function scanqrpay(){
        $params = array();
        $params["cusid"] = self::CUSID;
        $params["appid"] = self::APPID;
        $params["version"] = self::APIVERSION;
        $params["randomstr"] = date("YmdHis").rand(10000,999999);//
        $params["trxamt"] = "1";	//金额
        $params["reqsn"] = "485442841552544";//订单号（交易流水号）,自行生成。

        $params["body"] = "商品名称";
        $params["remark"] = "备注信息";
        $params["authcode"] = 28031143760583281611;		//扫描的支付码

        $params["limit_pay"] = "no_credit";

        $params['goods_tag']='';
        $params['benefitdetail']='';
        $params['chnlstoreid']='';
        $params['subbranch']='';
        $params['idno']='';
        $params['extendparams']='';
        $params['truename']='';
        $params['asinfo']='';
        $params['fqnum']='';
        $params['signtype']='';
        $params["sign"] = self::SignArray($params,self::APPKEY);//签名

        $paramsStr = self::ToUrlParams($params);
        $url = self::APIURL . "/scanqrpay";
        $rsp = self::request($url, $paramsStr);
        $rspArray = json_decode($rsp, true);
        dump($rspArray,true,'请求返回');
        if(self::validSign($rspArray)){
            echo "验签正确,进行业务处理";
        }
    }

    //当天交易用撤销
    function cancel(){
        $params = array();
        $params["cusid"] = self::CUSID;
        $params["appid"] = self::APPID;
        $params["version"] = self::APIVERSION;
        $params["trxamt"] = "1";
        $params["reqsn"] = "123456788";
        $params["oldreqsn"] = "123456";//原来订单号
        $params["randomstr"] = "1450432107647";//
        $params["sign"] = self::SignArray($params,self::APPKEY);//签名
        $paramsStr = self::ToUrlParams($params);
        $url = self::APIURL . "/cancel";
        $rsp = request($url, $paramsStr);
        echo "请求返回:".$rsp;
        echo "<br/>";
        $rspArray = json_decode($rsp, true);
        if(validSign($rspArray)){
            echo "验签正确,进行业务处理";
        }
    }

    //当天交易请用撤销,非当天交易才用此退货接口
    function refund(){
        $params = array();
        $params["cusid"] = self::CUSID;
        $params["appid"] = self::APPID;
        $params["version"] = self::APIVERSION;
        $params["trxamt"] = "1";
        $params["reqsn"] = "1234567889";
        $params["oldreqsn"] = "123456";//原来订单号
        $params["randomstr"] = "1450432107647";//
        $params["sign"] = self::SignArray($params,self::APPKEY);//签名
        $paramsStr = self::ToUrlParams($params);
        $url = self::APIURL . "/refund";
        $rsp = request($url, $paramsStr);
        echo "请求返回:".$rsp;
        echo "<br/>";
        $rspArray = json_decode($rsp, true);
        if(validSign($rspArray)){
            echo "验签正确,进行业务处理";
        }
    }

    function query(){
        $params = array();
        $params["cusid"] = self::CUSID;
        $params["appid"] = self::APPID;
        $params["version"] = self::APIVERSION;
        $params["reqsn"] = "123456";
        $params["randomstr"] = "1450432107647";//
        $params["sign"] = self::SignArray($params,self::APPKEY);//签名
        $paramsStr = self::ToUrlParams($params);
        $url = self::APIURL . "/query";
        $rsp = request($url, $paramsStr);
        echo "请求返回:".$rsp;
        echo "<br/>";
        $rspArray = json_decode($rsp, true);
        if(validSign($rspArray)){
            echo "验签正确,进行业务处理";
        }
    }

    //发送请求操作仅供参考,不为最佳实践
    function request($url,$params){
        $ch = curl_init();
        $this_header = array("content-type: application/x-www-form-urlencoded;charset=UTF-8");
        curl_setopt($ch,CURLOPT_HTTPHEADER,$this_header);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);//如果不加验证,就设false,商户自行处理
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        $output = curl_exec($ch);
        curl_close($ch);
        return  $output;
    }

    //验签
    function validSign($array){
        if("SUCCESS"==$array["retcode"]){
            $signRsp = strtolower($array["sign"]);
            $array["sign"] = "";
            $sign =  strtolower(self::SignArray($array, self::APPKEY));
            if($sign==$signRsp){
                return TRUE;
            }
            else {
                echo "验签失败:".$signRsp."--".$sign;
            }
        }
        else{
            echo $array["retmsg"];
        }

        return FALSE;
    }

    /**
     * 将参数数组签名
     */
    public function SignArray(array $array,$appkey){
        $array['key'] = $appkey;// 将key放到数组中一起进行排序和组装
        ksort($array);
        $blankStr = self::ToUrlParams($array);
        $sign = md5($blankStr);
        return $sign;
    }

    public function ToUrlParams(array $array)
    {
        $buff = "";
        foreach ($array as $k => $v)
        {
            if($v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

// /**
// 	 * 校验签名
// 	 * @param array 参数
// 	 * @param unknown_type appkey
// 	 */
// 	public function ValidSign(array $array,$appkey){
// 		$sign = $array['sign'];
// 		unset($array['sign']);
// 		$array['key'] = $appkey;
// 		$mySign = self::SignArray($array, $appkey);
// 		return strtolower($sign) == strtolower($mySign);
// 	}
}
