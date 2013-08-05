<?php
/**
 * meifuzhi - libs - 图像处理类
 * 该class用来封装所有图像处理方法(Imagick)
 * =======================================
 * Copyright 2011-2012 meifuzhi.com
 * =======================================
 *
 * @since 2012-05-21
 * @author guowantao@meifuzhi.com
 * @version $Id: Image.php 2261 2013-06-18 10:58:33Z quxianping $
 *
 **/

//整个class都必须采用静态调用的方法
class Image
{
    //附加大水印的最小图片长宽
    const BIG_WATERMARK_SIZE = 320;
    //附加小水印的最小图片长宽
    const SMALL_WATERMARK_SIZE = 160;
    //压缩图片质量
    const IMAGE_COMPRESSION_QUALITY = 75;
    
    /**
     * 构造函数，不允许实例化
     * @author tony@meifuzhi.com
     * @since 2012-05-21
     */    
    private function __construct() {}


     /**
     * 生成图片缩略图
     * @param string $srcFile 图片源文件
     * @param string $destFile 生成缩略图文件
     * @param int $newWidth 生成缩略图宽度
     * @param int $newHeight 生成缩略图高度
     * @param bool $watermask 是否加水印
     * @param bool $trim 是否创建一个高级剪裁
     * @param bool $keepRatio 是否保持原始高宽比例
     * @return bool true 成功生成缩略图 / false 缩略图生成失败
     * @author guowantao@meifuzhi.com
     * @since 2012-05-25
     */    
    public static function imageThumbnail($srcFile, $destFile, $newWidth, $newHeight, $watermask=true, $trim=false, $keepRatio=false)
    {
        if ($newWidth <= 0 || $newHeight <= 0 || !file_exists($srcFile)) {
            return false;
        }
        $src = new Imagick($srcFile);
        
        $src->setImageBackgroundColor('white');
        $imageFormat = strtolower($src->getImageFormat());
        if ($imageFormat != 'jpeg' && $imageFormat != 'gif' && $imageFormat != 'png' && $imageFormat != 'jpg') {
            return false;
        }

        //确定使用对应尺寸的水印图片
        $srcPage = $src->getImagePage();
        $srcWidth = $srcPage['width'];
        $srcHeight = $srcPage['height'];
        if ($keepRatio) {
            $ratioWidth = $newWidth / $srcWidth ;
            $ratioHeight = $newHeight / $srcWidth ;
            if ($ratioWidth < $ratioHeight) {
                $newWidth = $ratioHeight * $srcWidth ;
            } else {
                $newHeight = $ratioWidth * $srcHeight ;
            }
        }
        $rateWidth = $newWidth/$srcWidth;
        $rateHeight = $newHeight/$srcHeight;
        $rate = (!$trim && $rateWidth < $rateHeight) || ($trim && $rateWidth > $rateHeight) ? $rateWidth : $rateHeight;
        $rate = $rate > 1 ? 1 : $rate;
        $thumbWidth = round($srcWidth * $rate);
        $thumbHeight = round($srcHeight * $rate);
    
        if ($thumbWidth >= 300 && $thumbHeight >= 300) {
            $watermaskfile = "";
        } elseif($thumbWidth >= 100 && $thumbHeight >= 100) {
            $watermaskfile = "";
        } else {
            $watermask = false;
            $watermaskfile = "";
        }

        if ($watermask) {
            $water = new Imagick($watermaskfile);
            $waterPage = $water->getImagePage();
            $waterWidth = $waterPage['width'];
            $waterHeight = $waterPage['height'];
        }

        //如果是 jpg jpeg png
        if ($imageFormat != 'gif') {
            $dest = $src;
            if (!$trim) {
                $dest->thumbnailImage($newWidth, $newHeight, true, true);
            } else {
                $dest->cropthumbnailImage($newWidth, $newHeight);
            }

            if ($watermask) {
                $dest->compositeImage($water, Imagick::COMPOSITE_OVER, $dest->getImageWidth()-$waterWidth, $dest->getImageHeight()-$waterHeight);
            }
            $sign = $dest->writeImage($destFile);
            $dest->clear();

        //如果是gif需要一帧一帧的处理
        } else {
            $dest = new Imagick();  
            $images = $src->coalesceImages();  
            foreach ($images as $frame) {  
                $tmp = new Imagick();  
                $tmp->readImageBlob($frame);
                if (!$trim) {
                    $tmp->thumbnailImage($newWidth, $newHeight, true, true);
                } else {
                    $tmp->cropthumbnailImage($newWidth, $newHeight);
                }
                if ($watermask){
                    $tmp->compositeImage($water, Imagick::COMPOSITE_OVER, $tmp->getImageWidth()-$waterWidth, $tmp->getImageHeight()-$waterHeight);
                }
                $dest->addImage($tmp);
                $dest->setImagePage($tmp->getImageWidth(), $tmp->getImageHeight(), 0, 0);
                $dest->setImageDelay($frame->getImageDelay());
                $dest->setImageDispose($frame->getImageDispose());
            }

            $dest->coalesceImages();
            $sign = $dest->writeImages($destFile, true);
            $dest->clear();
        }
        return $sign;
    }

    public static function updateAvatars($srcFile, $destFile, $newWidth, $newHeight, $watermask=true, $trim=false)
    {
        $avatar = new Imagick();
        $avatar->readImage($srcFile);
        $avatar->setImageFormat('jpeg');
        $avatar->setImageCompression(Imagick::COMPRESSION_JPEG);
        $srcQuality = $avatar->getimagecompressionquality();
        $destQuality = $srcQuality > self::IMAGE_COMPRESSION_QUALITY ? self::IMAGE_COMPRESSION_QUALITY : $srcQuality ;
        $avatar->setImageCompressionQuality($destQuality);
        if (!$trim) {
            $avatar->thumbnailImage($newWidth, $newHeight, true);
        } else {
            $avatar->cropthumbnailImage($newWidth, $newHeight);
        }
        $sign = $avatar->writeImage($destFile);
        $avatar->clear();
        return $sign;
    }


     /**
     * 图片格式转换
     * @param string $srcFile 图片源文件
     * @param string $destFile 转化图片文件
     * @return bool true 成功生成转化图 / false 转化图生成失败
     * @author guowantao@meifuzhi.com
     * @since 2012-05-31
     */    
    public static function imageConvert($srcFile,$destFile)
    {
        $image = new Imagick();
        $image->readImage($srcFile);
        $image->setImageFormat('jpeg');
        $sign = $image->writeImage($destFile);
        $image->destroy();
        return $sign;
    }

     /**
     * 返回图片格式
     * @param string $srcFile 图片文件
     * @return string $imageType 图片文件格式
     * @author guowantao@meifuzhi.com
     * @since 2012-05-31
     */    
    public static function getImageType($srcFile)
    {
        $image = new Imagick($srcFile);
        $imageType = $image->getImageFormat();
        $image->destroy();
        return $imageType;

    }

    /**
     * 查看日志详情
     * @since 2012-07-06
     * @author vincent.qu@meifuzhi.com
     * @lastupdate 2012-07-06 vincent.qu
     */
    public static function saveUpLoad()
    {
        //原始文件名，表单名固定，不可配置
        $oriName = htmlspecialchars(trim($_POST['fileName']) ,ENT_QUOTES );
        //上传图片框中的描述表单名称，
        $title = htmlspecialchars(trim($_POST['pictitle']) ,ENT_QUOTES );
        //文件句柄
        $file = $_FILES['upfile'];
        $suffix = strrchr($file['name'], '.');
        //格式验证
        $current_type = strtolower($suffix);
        if ( !in_array($current_type, Yii::app()->params['uploadImageFileType']) || false === getimagesize($file['tmp_name']) ) {
            throw new Exception("不允许的图片格式");
        }
        //大小验证
        $file_size = 1024 * Yii::app()->params['uploadImageFileSize'];
        if ($file['size'] > $file_size) {
            throw new Exception("图片大小超出限制");
        }
        //保存图片
        $tmp_file = $file['name'];
        $path = self::getUploadImagePath();
        $filePath = $path . $suffix;
        $result = move_uploaded_file($file['tmp_name'], $filePath);
        if (false === $result) {
            throw new Exception("未知错误");
        }
        Log::file('tmp_file:'.$file['name'].' file_path:'.$filePath);
        return $filePath;
    }
    
    /**
     * 随机获取上传图片的存储路径
     * @since 2012-07-09
     * @author vincent.qu@meifuzhi.com
     * @lastupdate 2012-07-09 vincent.qu
     */
    public static function getUploadImagePath()
    {
        $arr = explode(' ', microtime());
        $microtime = time() . 100000000 * $arr[0];
        $fileName = md5($microtime);
        $path = Yii::app()->params['uploadImagePath'].rand(1,1000).'/';
        if ( !file_exists($path) ) {
            mkdir($path, 0777);
        }
        return $path . $fileName;
    }
    
    /**
     * 根据图片文件在服务器上的路径，获取外界对应可访问的url
     * @since 2012-07-10
     * @author vincent.qu@meifuzhi.com
     * @lastupdate 2012-07-10 vincent.qu
     */
    public static function getUrlByPath($filePath)
    {
        $arr = explode('/', $filePath);
        $cnt = count($arr);
        if ($cnt < 2) {
            return null;
        }
        $url = Yii::app()->params['imageServerUrl'] . '/diary/' . $arr[$cnt-2] . '/' . $arr[$cnt-1];
        return $url;
    }
    
    /**
     * 为图片添加水印
     *
     * @param string $srcFile 原图片地址
     * @return bool true 生成水印成功（当前方法会直接覆盖原图）
     *              false 失败（原文件不存在,水印图片不存在,原文件图像对象建立失败，水印文件图像对象建立失败，加水印后的新图片保存失败，图片太小不必添加水印）
     * @since 2012-07-20
     * @author vincent.qu@meifuzhi.com
     * @lastupdate 2012-07-20 vincent.qu
     */
    public static function imageWaterMark($srcFile)
    {
        if (is_file($srcFile)) {
            $image = new Imagick($srcFile);
            $image->setImageFormat('jpeg');
            $image->setImageCompression(Imagick::COMPRESSION_JPEG);
            $srcQuality = $image->getimagecompressionquality();
            $destQuality = $srcQuality > self::IMAGE_COMPRESSION_QUALITY ? self::IMAGE_COMPRESSION_QUALITY : $srcQuality ;
            $image->setImageCompressionQuality($destQuality);
            $arr = explode('.', $srcFile);
            array_pop($arr);
            $destFile = join('.', $arr).'.jpg';
            $image->writeimage($destFile);
            Log::file('dest_file:'.$destFile);
        } else {
            return false;
        }

        if (is_file($destFile)) {
            $image = new Imagick($destFile);
            $srcWidth = $image->getimagewidth();
            $srcHeight = $image->getimageheight();
        } else {
            return false;
        }
        //判断图片大小是否符合加水印规则
        if ($srcWidth < self::SMALL_WATERMARK_SIZE || $srcHeight < self::SMALL_WATERMARK_SIZE) {
            return false;
        } else if ($srcWidth < self::BIG_WATERMARK_SIZE || $srcHeight < self::BIG_WATERMARK_SIZE) {
            $waterFile = Yii::app()->params['smallWaterMarkPath'] ;
        } else {
            $waterFile = Yii::app()->params['bigWaterMarkPath'] ;
        }
        
        //获取水印图片对象
        if (is_file($waterFile)) {
            $water = new Imagick($waterFile);
            $waterWidth = $water->getimagewidth();
            $waterHeight = $water->getimageheight();
        } else {
            return false;
        }
        $image->compositeimage($water, Imagick::COMPOSITE_OVER, $srcWidth-$waterWidth, $srcHeight-$waterHeight);
        $image->writeimage($destFile);
        return $destFile;
    }
}
