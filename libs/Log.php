<?php
/**
 * 日志类
 * TODO 先山寨地写一个，后续有时间需要完善：
 * (1)file_put_contents效率肯定不咋地，高并发下不了解
 * (2)合理的方式是内存暂存，最后flush到硬盘
 * (3)支持多Log文件
 * (4)支持访问者信息的记录
 *
 * @author vincent.qu
 * @since 2013-01-08
 * @version $Id: Log.php 2260 2013-06-18 10:57:58Z quxianping $
 */

include_once(dirname(__FILE__).'/../config/main.php');

class Log
{
    public static function file($string)
    {
        $file = Conf::LOG_PATH;
        $log = date('Y-m-d H:i:s').' '.$string."\n";
        file_put_contents($file, $log, FILE_APPEND);
    }
    
    
    public static function smsLog($phoneNumber, $status)
    {
        $file = Conf::SMS_LOG_PATH;
        $dt = date('Y-m-d H:i:s');
        $content = sprintf("[%s] Send:%s Status:%s\n", $dt, $phoneNumber, $status);
        file_put_contents($file, $content, FILE_APPEND);
    }
}
