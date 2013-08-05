<?php
/**
 * meifuzhi - libs - 加密解密类
 * 该class用来封装所有和加密解密有关的函数
 * =======================================
 * Copyright 2011-2012 meifuzhi.com
 * =======================================
 *
 * @since 2012-06-15
 * @author tony@meifuzhi.com
 * @version $Id: Crypt.php 2260 2013-06-18 10:57:58Z quxianping $
 *
 **/

include_once (dirname ( __FILE__ ) . '/../config/main.php');
include_once (dirname ( __FILE__ ) . '/../config/db.php');

// 整个class都必须采用静态调用的方法
class Crypt {
    /**
     * 大素数
     * 求短url时用到
     *
     * @todo 大素数表不全，对于小id:1-13，目前无法以
     * @var array
     */
    private static $LARGE_PRIME_NUMBER = array (
            '5635465853',
            '193707721',
            '99996719',
            '6814139',
            '239641',
            '2797',
            '79' 
    );
    
    /**
     * 大素数个数
     *
     * @var int
     */
    const LARGE_PRIME_NUMBER_CNT = 7;
    
    /**
     * base36字母表
     * 求短url时用到
     *
     * @var array
     */
    private static $BASE_36 = array (
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'q',
            'r',
            's',
            't',
            'u',
            'v',
            'w',
            'x',
            'y',
            'z',
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9' 
    );
    
    private static $BASE_62 = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'q',
            'r',
            's',
            't',
            'u',
            'v',
            'w',
            'x',
            'y',
            'z',
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9'
            );
    
    /**
     * base36字符集的字符个数
     */
    const BASE_CHAR_CNT = 36;
    
    /**
     * 短url编码最大值
     */
    const MAX_ID = 1000000000;
    /**
     * 36^7编码最大int型
     *
     * @var int
     */
    const MAX_INT = 78364164096;
    const MAX_NORMAL_INT = 100000000;
    /**
     * shortUrl编码后的string长度为8
     *
     * @var int
     */
    const STR_LEN = 8;
    
    /**
     * 构造函数，不允许实例化
     */
    private function __construct() {
    }
    
    /**
     * 通用加密方法，全站最常用的方法
     *
     * @param str $e
     *            待加密的字串
     * @return str 加密后的字串
     * @author tony@meifuzhi.com
     * @since 2012-06-15
     */
    public static function encrypt($e) {
        $key = Conf::CRYPT_KEY;
        return self::_encode ( mcrypt_encrypt ( MCRYPT_DES, $key, $e, MCRYPT_MODE_ECB ) );
    }
    
    /**
     * 通用解密方法，全站最常用的方法
     *
     * @param str $d
     *            待解密的字串
     * @return str 返回解密后的字串
     * @author tony@meifuzhi.com
     * @since 2012-06-15
     */
    public static function decrypt($d) {
        $key = Conf::CRYPT_KEY;
        return mcrypt_decrypt ( MCRYPT_DES, $key, self::_decode ( $d ), MCRYPT_MODE_ECB );
    }
    
    /**
     * 通用编码方法，配合通用加密方法使用
     * 根据单个字符的ascii直接转化成16进制使用
     *
     * @param str $d
     *            待编码的字串
     * @return str 返回编码后的字串
     * @author tony@meifuzhi.com
     * @since 2012-06-15
     */
    private static function _encode($s) {
        $len = strlen ( $s );
        $newstr = '';
        for($i = 0; $i < $len; $i ++) {
            $newstr .= str_pad ( dechex ( ord ( $s [$i] ) ), 2, '0', STR_PAD_LEFT );
        }
        
        return $newstr;
    }
    
    /**
     * 通用解码方法，配合通用解密方法使用
     *
     * @param str $d
     *            待解码的字串
     * @return str 返回解码后的字串
     * @author tony@meifuzhi.com
     * @since 2012-06-15
     */
    private static function _decode($s) {
        $strArr = str_split ( $s, 2 );
        
        $newstr = '';
        foreach ( $strArr as $hexStr ) {
            $newstr .= chr ( hexdec ( $hexStr ) );
        }
        
        return $newstr;
    }
    
    /**
     * 将int型id加密生成一个8位的短url
     * 注意：id最大值有要求，不能超过这个最大值，目前设定最大值为10亿
     *
     * @param int $id
     *            待编码的id
     * @return str 返回编码后的字串
     * @author tony@meifuzhi.com
     * @since 2012-06-15
     */
    public static function enShortUrl($id) {
        $id = intval ( $id );
        if (empty ( $id )) {
            return false;
        }
        if ($id > self::MAX_ID) {
            return false;
        }
        // 求最大数与id的商，要确定使用哪个素数进行加密
        $t = intval ( self::MAX_INT / $id );
        foreach ( self::$LARGE_PRIME_NUMBER as $index => $primeNumber ) {
            if ($primeNumber < $t) {
                break;
            }
        }
        
        // 将原id与大素数相乘
        $hex = $id * $primeNumber;
        // 这个是结果字符串
        $ret = '';
        
        while ( $hex > 0 ) {
            // 每一次这个数与base36字符集个数取模
            if (is_int ( $hex )) { // 整型
                $mod = $hex % self::BASE_CHAR_CNT;
            } else { // float类型，主要考虑到32位系统
                $mod = fmod ( $hex, self::BASE_CHAR_CNT );
            }
            $ret .= self::$BASE_36 [$mod];
            // 然后取商，进行下一次计算
            $hex = intval ( $hex / self::BASE_CHAR_CNT );
        }
        
        // 最后一位用系数填补，表示用的是哪一个素数进行的运算
        $index = $index + rand ( 0, intval ( self::BASE_CHAR_CNT / self::LARGE_PRIME_NUMBER_CNT ) - 1 ) * self::LARGE_PRIME_NUMBER_CNT;
        $lastChar = self::$BASE_36 [$index];
        $ret .= $lastChar;
        
        return $ret;
    }
    
    /**
     * 将int型id加密生成一个8位的短url
     * 注意：id最大值有要求，不能超过这个最大值，目前设定最大值为10亿
     *
     * @param str $str
     *            待解码的字符串
     * @return int $id 返回解码后的数字id
     * @author vincent.qu@meifuzhi.com
     * @since 2012-07-30
     */
    public static function deShortUrl($str) {
        
        $len = strlen ( $str );
        if (self::STR_LEN !== $len) {
            return false;
        }
        $str = strtolower ( $str );
        
        // BASE_36字符集数组 key/value 交换
        $base36 = array_flip ( self::$BASE_36 );
        
        // 获取url中最后一位字母，用来寻找使用的大素数
        $lastChar = substr ( $str, $len - 1, 1 );
        $index = $base36 [$lastChar] % self::LARGE_PRIME_NUMBER_CNT;
        $primeNumber = self::$LARGE_PRIME_NUMBER [$index];
        
        // 逐个字符反解数字计算
        $ret = 0;
        for($i = $len - 2; $i >= 0; $i --) {
            $char = substr ( $str, $i, 1 );
            $index = $base36 [$char];
            $ret += pow ( 36, $i ) * $index;
        }
        
        $ret = $ret / $primeNumber;
        return $ret;
    }
    
    /**
     * 最大支持12位数字，最小支持2位数字
     *
     * @param unknown_type $id            
     */
    public static function encryptInt($id) {
        $id = intval ( $id );
        if (empty ( $id ) || $id > self::MAX_ID * 10) {
            return false;
        }
        $t = intval ( self::MAX_INT / $id );
        foreach ( self::$LARGE_PRIME_NUMBER as $index => $primeNumber ) {
            if ($primeNumber < $t) {
                break;
            }
        }
        $res = $id * $primeNumber . $index;
        return $res;
    }
    
    /**
     * 最大支持12位数字，最小支持2位数字
     *
     * @param unknown_type $id            
     */
    public static function decryptInt($id) {
        $id = intval ( $id );
        // 获取url中最后一位字母，用来寻找使用的大素数
        $index = substr ( $id, - 1 );
        $primeNumber = self::$LARGE_PRIME_NUMBER [$index];
        
        // 除去校验标记位
        $ret = intval ( $id / 10 );
        
        if ($ret % $primeNumber != 0) {
            return null;
        }
        $ret = $ret / $primeNumber;
        return $ret;
    }
    
    /**
     * 将一个整形id转为一个5位url，字符集为62，大小写+数字
     * @param unknown_type $id
     */
    public static function enShortUrl5($id)
    {
        $db = getPdo();
        $sql = "SELECT `short_url` FROM `msg_short_url` WHERE `original_id`=:ID LIMIT 1";
        $command = $db->prepare($sql);
        $command->bindValue(':ID', $id, PDO::PARAM_INT);
        $command->execute();
        $url = $command->fetchColumn();
        if (!empty($url)) {
            return $url;
        }
        $needRand = true;
        while ($needRand) {
            $rand = rand(0, 916132831);
            $sql = "SELECT `id` FROM `msg_short_url` WHERE `id`=:ID LIMIT 1";
            $command = $db->prepare($sql);
            $command->bindValue(':ID', $rand, PDO::PARAM_INT);
            $command->execute();
            $longId = $command->fetchColumn();
            if (empty($longId)) {
                $needRand = false;
            }
        }
        $n = 5;
        $url = '';
        $tmp = $rand;
        while ($n>0) {
            $singleNumber = $tmp % 62 ;
            $url = self::$BASE_62 [$singleNumber] . $url;
            $tmp = ($tmp - $singleNumber) / 62 ;
            $n--;
        }
        $sql = "INSERT INTO `msg_short_url` (`id`, `short_url`, `original_id`) VALUES (:ID, :URL, :OID)";
        $command = $db->prepare($sql);
        $command->bindValue(':ID', $rand, PDO::PARAM_INT);
        $command->bindValue(':URL', $url, PDO::PARAM_STR);
        $command->bindValue(':OID', $id, PDO::PARAM_INT);
        $command->execute();
        return $url;
    }
    
    /**
     * 5位短url转回
     * @param unknown_type $cryptId
     */
    public static function deShortUrl5($cryptId)
    {
        $db = getPdo();
        $sql = "SELECT `original_id` FROM `msg_short_url` WHERE `short_url`=:URL LIMIT 1";
        $command = $db->prepare($sql);
        $command->bindValue(':URL', $cryptId, PDO::PARAM_STR);
        $command->execute();
        $id = $command->fetchColumn();
        return $id;
    }

}
