<?php
class X{
    
    /* 为密码加盐 */
    static function salt($str){
        return md5($str."-->Xnoopsy");
    }
    
    /* 获得客户端IP */
    static function getIP()
    {
        if (!empty($_SERVER["HTTP_CLIENT_IP"])){
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}elseif(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}else{
			$ip = $_SERVER["REMOTE_ADDR"];
		}
        
        if(filter_var($ip,FILTER_VALIDATE_IP)){
            return $ip;
        }else{
            return "0.0.0.0";
        }
    }
    
    /* 特制的Base64编码 */
    static function encode($string){
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
    
    /* 特制的Base64解码 */
    static function decode($string){
        $data = str_replace(array('-','_'),array('+','/'),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }
    
    /* 优化empty函数 */
    static function emptyEx($string){
        if(trim($string)==""){
            return true;
        }else{
            return false;
        }
    }
    
    static function br($string){
        $ret = str_replace("\r\n","<br/>",$string);
        $ret = str_replace("\n","<br/>",$ret);
        return $ret;
    }
    
    static function urlsafe_base64_encode($string) {
        $data = base64_encode($string);
        $data = str_replace(array('+','/'),array('-','_'),$data);
        return $data;
    }
    
    static function hmac_sha1($str, $key)
    {
        $signature = "";
        if (function_exists('hash_hmac'))
        {
            $signature = hash_hmac("sha1", $str, $key, true);
        }
        else
        {
            $blocksize	= 64;
            $hashfunc	= 'sha1';
            if (strlen($key) > $blocksize)
            {
                $key = pack('H*', $hashfunc($key));
            }
            $key	= str_pad($key,$blocksize,chr(0x00));
            $ipad	= str_repeat(chr(0x36),$blocksize);
            $opad	= str_repeat(chr(0x5c),$blocksize);
            $hmac 	= pack(
                'H*',$hashfunc(
                    ($key^$opad).pack(
                        'H*',$hashfunc(
                            ($key^$ipad).$str
                        )
                    )
                )
            );
            $signature = $hmac;
        }
        
        return $signature;
    }
}
?>