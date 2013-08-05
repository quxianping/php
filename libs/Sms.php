<?php
/**
 * 封装的短信发送网关
 * for emay
 * @author TonyZhao
 * @since 2013-01-14, v1.0.0
 * @version $Id: Sms.php 538 2013-02-01 09:38:18Z tony $
 */

//Load 亿美 短信网关接口文件

require_once(dirname(__FILE__).'/../vendors/emaysms/smsClient.php');
require_once(dirname(__FILE__).'/../config/main.php');
require_once(dirname(__FILE__).'/../libs/Log.php');

class Sms
{
    //以下是亿美专用配置
    private $connectTimeOut = 2;
    private $readTimeOut = 10;
    //亿美专用配置结束

    private static $smsClient;

    //构造函数
    public function Sms()
    {
        if (!isset(self::$smsClient)) {
            self::$smsClient = new Client(CONF::SMS_GATEWAY_URL, Conf::SMS_SERIAL_NUMBER, Conf::SMS_PASSWORD, Conf::SMS_SESSION_KEY, false, false, false, false, $this->connectTimeOut, $this->readTimeOut);
            self::$smsClient->setOutgoingEncoding("UTF-8");
        }
    }


    //短信发送
    public function send($phoneNumber, $content)
    {

        $statusCode = self::$smsClient->sendSMS(array($phoneNumber), $content, '', '', 'UTF-8', 5);

        Log::smsLog($phoneNumber, $statusCode);

        switch($statusCode) {
            //发送成功：
            case 0:
                return true;

            //发送失败：
            case 17:
            case 18:
            case 303:
            case 305:
            case 307:

            //不确定是否成功：
            case 997:
            case 998:
                return false;
        }
    }


}
