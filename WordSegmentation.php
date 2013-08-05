<?php
/**
 * meifuzhi - 搜索分词库
 * 使用scws扩展进行分词
 * =======================================
 * Copyright 2011-2012 meifuzhi.com
 * =======================================
 *
 * @since 2012-08-10
 * @author vincent.qu@meifuzhi.com
 * @version $Id: WordSegmentation.php 1105 2012-08-10 09:55:29Z quxianping $
 *
 **/

class WordSegmentation
{
	public static function getResult($string)
	{
		//使用scws分词扩展进行搜索分词
		$scws = scws_new();
		$scws->add_dict(YiiBase::getPathOfAlias('libs.scws.dict').'/dict.utf8.xdb');
		$scws->add_dict(YiiBase::getPathOfAlias('libs.scws.dict').'/dict_cosmetics.txt', SCWS_XDICT_TXT);
		$scws->add_dict(YiiBase::getPathOfAlias('libs.scws.dict').'/dict_special.txt', SCWS_XDICT_TXT);
		$scws->set_charset('utf8');
		// 这里没有调用 set_dict 和 set_rule 系统会自动试调用 ini 中指定路径下的词典和规则文件
		$scws->send_text($string);
		$ret = '';
		while ($tmp = $scws->get_result()) {
			foreach ($tmp as $one) {
				$ret .= $one['word'].' ';
			}
		}
		$scws->close();
		return $ret;
	}
}
