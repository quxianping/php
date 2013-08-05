<?php

class Des {
    private $key; // DES加密key
    private $iv; // 偏移量
    
    public function __construct($key, $iv = 0) {
        $this->key = $key;
        if ($iv == 0) {
            $this->iv = "19491001";
        } else {
            $this->iv = $iv;
        }
    }
    
    // 加密
    public function encrypt($str, $isUrlSafeNoPadding = false) {
        $size = mcrypt_get_block_size ( MCRYPT_DES, MCRYPT_MODE_CBC );
        
        $str = $this->pkcs5Pad ( $str, $size );
        
        $data = mcrypt_cbc ( MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv );
        if ($isUrlSafeNoPadding) {
            return rtrim ( strtr ( base64_encode ( $data ), '+/', '-_' ), '=' );
        } else {
            return base64_encode ( $data );
        }
    }
    
    // Base64 UrlSafe & NoPadding
    public function encryptUrlSafeNoPadding($str) {
        return $this->encrypt ( $str, true );
    }
    
    // 解密
    public function decrypt($str) {
        $str = base64_decode ( $str );
        $str = mcrypt_cbc ( MCRYPT_DES, $this->key, $str, MCRYPT_DECRYPT, $this->iv );
        $str = $this->pkcs5Unpad ( $str );
        return $str;
    }
    
    private function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }
    
    private function pkcs5Unpad($text) {
        $pad = ord ( $text {strlen ( $text ) - 1} );
        if ($pad > strlen ( $text ))
            return false;
        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
            return false;
        return substr ( $text, 0, - 1 * $pad );
    }
}
