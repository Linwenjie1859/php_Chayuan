<?php

namespace app\core\util;
use app\core\sdk\CCPRestSDK;

class SendTemplateSMS
{

    //主帐号
    const accountSid = '8a216da86f9cc12f016feca773ec1f17';

    //主帐号Token
    const accountToken = '99208e8bf61346f092815f0c3a480def';

    //应用Id
    const appId = '8a216da86f9cc12f016feca7744e1f1e';

    //请求地址，格式如下，不需要写https://
    const serverIP = 'sandboxapp.cloopen.com';

    //请求端口
    const serverPort = '8883';

    //REST版本号
    const softVersion = '2013-12-26';


    /**
     * 发送模板短信
     * @param to 手机号码集合,用英文逗号分开
     * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
     * @param $tempId 模板Id
     */
    public static function sendTemplateSMS($to, $datas, $tempId)
    {
        // 初始化REST SDK
        $rest = new CCPRestSDK(self::serverIP, self::serverPort, self::softVersion);
        $rest->setAccount(self::accountSid, self::accountToken);
        $rest->setAppId(self::appId);

        // 发送模板短信
        echo "Sending TemplateSMS to $to <br/>";
        $result = $rest->sendTemplateSMS($to, $datas, $tempId);
        if ($result == NULL) {
            echo "result error!";
        }
        if ($result->statusCode != 0) {
            echo "error code :" . $result->statusCode . "<br>";
            echo "error msg :" . $result->statusMsg . "<br>";
            //TODO 添加错误处理逻辑
        } else {
            echo "Sendind TemplateSMS success!<br/>";
            // 获取返回信息
            $smsmessage = $result->TemplateSMS;
            echo "dateCreated:" . $smsmessage->dateCreated . "<br/>";
            echo "smsMessageSid:" . $smsmessage->smsMessageSid . "<br/>";
            //TODO 添加成功处理逻辑
        }
    }

}
