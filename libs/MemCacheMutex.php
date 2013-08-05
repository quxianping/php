<?php
/**
 * cache锁，防止多线程操作冲突
 *
 * @author vincent.qu
 * @since 2013-01-31
 * @version $Id: MemCacheMutex.php 2260 2013-06-18 10:57:58Z quxianping $
 */
class MemCacheMutex
{
    //锁key
    public static $MUTEX_KEY = "nest_mutex_key";
    
    public static $MUTEX_KEY_PREFIX = "nest_mutex_key_";

    //每次sleep时间，默认100ms
    public static $SLEEP_TIME = 1000000;

    //最大sleep次数
    public static $MAX_SLEEP_TIME = 10;
    
    //cache失效时间 10s
    public static $CACHE_EXPIRE = 10;

    public static function lock($cache)
    {
        $value = $cache->get(self::$MUTEX_KEY);
        $sleepCount = 0;
        while (true) {
            if (empty($value) || $sleepCount > 10) {
                $cache->set(self::$MUTEX_KEY, 1, false, self::$CACHE_EXPIRE);
                break;
            } else {
                usleep(self::$SLEEP_TIME);
                $sleepCount++;
            }
        }
    }

    public static function unlock($cache)
    {
        $value = $cache->set(self::$MUTEX_KEY, 0, false, self::$CACHE_EXPIRE);
    }
    
    public static function lockByKey($cache, $key)
    {
        $key = self::$MUTEX_KEY_PREFIX . $key ;
        $value = $cache->get($key);
        if (empty($value)) {
            $cache->set($key, 1, false, self::$CACHE_EXPIRE);
            return true;
        } else {
            return false;
        }
    }
    
    public static function unlockByKey($cache, $key)
    {
        $key = self::$MUTEX_KEY_PREFIX . $key ;
        $value = $cache->set($key, 0, false, self::$CACHE_EXPIRE);
    }
}
