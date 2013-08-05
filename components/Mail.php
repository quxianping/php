<?php
/**
 * meifuzhi - libs - 邮件处理类
 * 该class用来封装所有邮件处理方法(PHPMailer)
 * =======================================
 * Copyright 2011-2012 meifuzhi.com
 * =======================================
 *
 * @since 2012-06-04
 * @author tony@meifuzhi.com
 * @version $Id: Mail.php 568 2012-07-17 09:25:45Z tony $
 *
 **/
include_once(dirname(__FILE__).'/../libs/PHPMailer.php');
include_once(dirname(__FILE__).'/../libs/PHPMailerSMTP.php');

//整个class都必须采用静态调用的方法
class Mail
{
	
	//@TODO 用smtp借用qqmail发邮件的方式需要改变，最好有自己的邮件服务器
	private static $accounts = array('service'=> array(
	                                            'server'=>'smtp.exmail.qq.com',
	                                            'port'=>25,
	                                            'usr'=>'service@meifuzhi.com',
	                                            'pwd'=>'qZ22cE43tB56mUuJ',
                                                'fromname'=>'美肤志(meifuzhi.com)',
	                                           )
	                         );
	
	
	/**
	 * 构造函数，不允许实例化
	 * @author tony@meifuzhi.com
	 * @since 2012-05-21
	 */	
	private function __construct() {}


	/**
	 * 发送邮箱验证邮件的函数
	 * @param str $to 收件人
	 * @param str $verifyUrl 验证邮箱的地址链接
	 * @return bool 邮件发送是否成功
	 * @author tony@meifuzhi.com
	 * @since 2012-06-07
	 */	
	public static function sendVerifyMail($to, $verifyUrl)
	{
		$subject = "美肤志注册验证邮件";
		$body = "美肤志(meifuzhi.com)欢迎您的加入，请点击下面的链接完成邮箱验证（若无法点击，请复制到浏览器中打开）：\n\r {$verifyUrl} \n\r\n\r本链接的有效时间为72小时，若超过时间，请登录网站后重新发送验证邮件";
		
		
		$ret = self::sendMail($to, $subject, $body);
		
		if ($ret === true ) return true;
		else return false;
	}


	/**
	 * 发送密码重置邮件的函数
	 * @param str $to 收件人
	 * @param str $resetUrl 重置密码的地址链接
	 * @return bool 邮件发送是否成功
	 * @author tony@meifuzhi.com
	 * @since 2012-06-11
	 */	
	public static function sendResetPasswordMail($to, $resetUrl)
	{
		$subject = "美肤志密码重置邮件";
		$body = "感谢您加入美肤志(meifuzhi.com)。\n\r您收到本邮件是因为选择了重置美肤志密码，若您未发起过该操作，请直接忽略本邮件。\n\r继续重置密码，请点击下面的链接（若无法点击，请复制到浏览器中打开）：\n\r {$resetUrl} \n\r\n\r\n\r\n\r本链接的有效时间为24小时，若超过时间，请重新发起密码重置。";		
		
		$ret = self::sendMail($to, $subject, $body);
		
		if ($ret === true ) return true;
		else return false;
	}


	 /**
	 * 发送邮件的基础函数
	 * @param mixed $to 若为str，则为收件人地址；若为array，则第一个元素为收件人地址，第二个元素为收件人显示名称
	 * @param str $subject 标题
	 * @param str $mailbody 邮件正文内容
	 * @param bool $isHtml 是否为HTML格式，默认为plain/text
	 * @param str $account 使用的发件人账号，默认为'service'
	 * @return bool 邮件发送是否成功
	 * @author tony@meifuzhi.com
	 * @since 2012-06-06 高考
	 */		
	public static function sendMail($to, $subject, $mailbody, $isHtml=false, $account='service')
	{
		// the true param means it will throw exceptions on errors, which we need to catch
		$mail = new PHPMailer(true); 
		// telling the class to use SMTP
		$mail->IsSMTP(); 
		
		try {
			//$mail->SMTPDebug  = 2; // enables SMTP debug information (for testing)
			$mail->SMTPAuth   = true;
			$mail->Host       = self::$accounts[$account]['server'];
			$mail->Port       = self::$accounts[$account]['port'];
			$mail->Username   = self::$accounts[$account]['usr'];
			$mail->Password   = self::$accounts[$account]['pwd'];
			$mail->CharSet    = "UTF-8";
			$mail->IsHTML($isHtml);
			$mail->SetFrom(self::$accounts[$account]['usr'], self::$accounts[$account]['fromname']);
			if (is_array($to)) {
				$mail->AddAddress($to[0], $to[1]);
			} else {
				$mail->AddAddress($to);
			}
			$mail->Subject = $subject;
			$mail->Body = $mailbody;
			$mail->Send();
			return true;
		} catch (phpmailerException $e) {
			return $e->errorMessage();
		} catch (Exception $e) {
			return $e->getMessage();
		}
	}
}
