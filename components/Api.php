<?php

/**
 * nest - components - 对外http请求的处理组件
 * 
 * =======================================
 * Copyright 2011-2012 meifuzhi.com
 * =======================================
 *
 * @since 2013-06-24
 * @author vincent.qu@meifuzhi.com
 * @version $Id$
 *
 **/

include_once(dirname(__FILE__).'/../../core/libs/Log.php');
include_once(dirname(__FILE__).'/../../core/models/Auth.php');

class Api
{
    public static function checkAccess()
    {
    	if (empty($_REQUEST['kid']) || empty($_REQUEST['siid'])) {
    		header('HTTP/1.1 403 Forbidden');
    		header('status: 403 Forbidden');
    		echo '403';
    		exit(0);
    	}
    	$kid = trim($_REQUEST['kid']);
    	$cryptIMSI = trim($_REQUEST['siid']);
    	$appVersion = empty($_REQUEST['app_ver']) ? 'unknown' : trim($_REQUEST['app_ver']) ;
    	Log::file(TAG." kid: {$kid} imsi: {$cryptIMSI} app_ver: {$appVersion}");
    	$authRes = Auth::verifyUser($kid, $cryptIMSI);
    	if ($authRes === false) {
    		header('HTTP/1.1 403 Forbidden');
    		header('status: 403 Forbidden');
    		echo '403';
    		exit(0);
    	}
    	return $kid;
    }
    
}