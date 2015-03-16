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

class API{
    
    /* 验证用户 */
    static function user_verify($_IS){
        $username = addslashes(trim($_IS['username']));
        $password = addslashes(trim($_IS['password']));
        
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        if(
            MYSQL::exist(
                $link,
                "SELECT * FROM users 
                WHERE username='$username' AND password='".X::salt($password)."'"
            )
        ){
            return true;
        }else{
            return false;
        }
        
        MYSQL::close($link);
    }
    
    /* 生成用户COOKIE */
    static function make_cookie($_IS){
        $username = addslashes(trim($_IS['username']));
        $password = addslashes(trim($_IS['password']));
        $cookie_format = $username."|".$password;
        $cookie = X::encode($cookie_format);
        return $cookie;
    }
    
    /* 验证用户COOKIE */
    static function verify_cookie($_IS){
        $cookie = addslashes(trim($_IS['cookie']));
        $cookie_format = X::decode($cookie);
        $result = explode("|",$cookie_format,2);
        if(count($result)==1){
            return false;
        }
        $arguments = array(
            "username" => $result[0],
            "password" => $result[1]
        );
        if(self::user_verify($arguments)){
            return true;
        }else{
            return false;
        }
    }
    
    /* 获得Cookie对应用户名 */
    static function get_cookie_username($cookie){
        $cookie_format = X::decode($cookie);
        $result = explode("|",$cookie_format,2);
        return $result[0];
    }
    
    /* 获得Cookie对应用户ID */
    static function get_cookie_userid($cookie){
        $cookie_format = X::decode($cookie);
        $res = explode("|",$cookie_format,2);
        $username = addslashes(trim($res[0]));
        
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        $result = MYSQL::assoc($link,"SELECT * FROM users WHERE username='$username'");
        MYSQL::close($link);
        
        return $result['user_id'];
    }
    
    /* 获得用户名对应的用户ID */
    static function get_username_id($username){
        $username = addslashes(trim($username));
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        $result = MYSQL::assoc($link,"SELECT * FROM users WHERE username='$username'");
        MYSQL::close($link);
        return $result['user_id'];
    }
    
    /* 获得用户ID对应的用户名 */
    static function get_id_username($id){
        $id = intval($id);
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        $result = MYSQL::assoc($link,"SELECT * FROM users WHERE user_id='$id'");
        MYSQL::close($link);
        return $result['username'];
    }
}
?>