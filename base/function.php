<?php
class X{
    
    /* 为密码加盐 */
    static function salt($str){
        return md5($str."-->Xnoopsy");
    }
    
    /* 获得客户端IP */
    static function getIP()
    {
        if(getenv("REMOTE_ADDR")){
            $ip = getenv("REMOTE_ADDR");
        }
        
        if(filter_var($ip,FILTER_VALIDATE_IP)){
            return $ip;
        }else{
            return "0.0.0.0";
        }
    }
    
    /* URL安全的Base64编码 */
    static function encode($string){
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_',''),$data);
        return $data;
    }
    
    /* URL安全的Base64解码 */
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
    
}
?>