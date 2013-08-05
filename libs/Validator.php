<?php

/**
 * 用于验证数据正确性
 * @author TonyZhao
 * @since 2012-01-15, v1.0.0
 * @version $Id: Validator.php 2091 2013-06-05 03:32:17Z tony $
 */
class Validator
{
    

    //验证手机号码正确性
    public static function isPhoneNumber($phoneNumber, $countryCode=86)
    {
        switch ($countryCode) {
            case 86:
                if (1 === preg_match('/^1[3458][0-9][0-9]{8}$/', $phoneNumber)) {
                    return true;
                } else {
                    return false;
                }
                break;
            
            default:
                return false;                
                break;
        }
    

    }
    
    //验证IMSI是否合规
    public static function isIMSI($IMSI)
    {
		//大陆 460
        //香港 454
        //台湾 466
        //澳门 455
        //如果不全是数字，则false，只能是上述开头的
        if ( 1 === preg_match('/^(460|454|466|455)[0-9]{12}$/', $IMSI) ) {
            return true;
        } else {
            return false;
        }
    }
    
}
