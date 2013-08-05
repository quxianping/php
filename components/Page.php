<?php

/**
 * meifuzhi - components - 分页组件
 * 
 * =======================================
 * Copyright 2011-2012 meifuzhi.com
 * =======================================
 *
 * @since 2012-07-06
 * @author vincent.qu@meifuzhi.com
 * @version $Id: Page.php 2261 2013-06-18 10:58:33Z quxianping $
 *
 **/

class Page
{
    const DEFAULT_CUR_PAGE = 1;
    const DEFAULT_PER_PAGE = 15;
    
    const TINY_PER_PAGE = 5;
    const SMALL_PER_PAGE = 10;
    const MIDDLE_PER_PAGE = 25;
    const BIG_PER_PAGE = 100;
    const HUGE_PER_PAGE = 1000;
    
    /**
     * 获取页面列表起止信息，主要为SQL使用
     * @param int $perPage 每页条目数
     * @return array array($offset, $limit)
     * @since 2012-07-06
     * @author vincent.qu@meifuzhi.com
     * @lastupdate 2012-07-23 vincent.qu
     */
    public static function getOffsetLimitByPageInfo($perPage = self::DEFAULT_PER_PAGE)
    {
        if (empty($_REQUEST['page']) && empty($_REQUEST['curPage'])) {
            $curPage = self::DEFAULT_CUR_PAGE;
        } else {
            $curPage = empty($_REQUEST['page']) ? intval($_REQUEST['curPage']) : intval($_REQUEST['page']) ;
        }
        $perPage = intval($perPage);
        $offset = ($curPage-1) * $perPage ;
        $limit = $perPage; 
        return array($offset, $limit);
    }
    
    /**
     * 组合page信息，返回给前端的响应数据
     * @param int $records 总记录数
     * @param int $perPage 每页条目数
     * @return array $pageInfo 页面信息数组
     * @since 2012-07-10
     * @author vincent.qu@meifuzhi.com
     * @lastupdate 2012-07-23 vincent.qu
     */
    public static function getResponsePageInfo($records, $perPage = self::DEFAULT_PER_PAGE)
    {
        if (empty($_REQUEST['page']) && empty($_REQUEST['curPage'])) {
            $curPage = self::DEFAULT_CUR_PAGE;
        } else {
            $curPage = empty($_REQUEST['page']) ? intval($_REQUEST['curPage']) : intval($_REQUEST['page']) ;
        }
        $perPage = intval($perPage);
        $total = intval(ceil($records / $perPage));
        //这里很多数据是重复的，主要是为了兼容新旧两个版本的key不一致问题
        $ret = array(
                'curPage' => $curPage,
                'perPage' => $perPage,
                'records' => $records,
                'total' => $total,
                'page' => $curPage, 
                'totalPages' => $total, 
                'totalItems' => $records,
                );
        return $ret;
    }
    
}