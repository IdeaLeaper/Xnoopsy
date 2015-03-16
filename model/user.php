<?php
class USER{
    
    /* 获得用户COOKIE */
    static function get_cookie($_IS){
        if(
            !isset($_IS['username'])
            ||!isset($_IS['password'])
            ||X::emptyEx($_IS['username'])
            ||X::emptyEx($_IS['password'])
        ){
            echo '{"method":"get_cookie","status":"error","error","values undefined"}';
            return;
        }
        
        if(API::user_verify($_IS)){
            echo '{"method":"get_cookie","status":"ok","cookie":"'.API::make_cookie($_IS).'"}';
        }else{
            echo '{"method":"get_cookie","status":"error","error":"verify failed"}';
        }
        
    }
    
    /* 验证用户COOKIE */
    static function verify_cookie($_IS){
        if(
            !isset($_IS['cookie'])
            ||X::emptyEx($_IS['cookie'])
        ){
            echo '{"method":"verify_cookie","status":"error","error","cookie undefined"}';
            return;
        }
        
        if(API::verify_cookie($_IS)){
            echo '{"method":"verify_cookie","status":"ok"}';
        }else{
            echo '{"method":"verify_cookie","status":"error","error":"verify failed"}';
        }
        
    }
    
    /* 用户注册 */
    static function register($_IS){
        if(
            !isset($_IS['username'])
            ||!isset($_IS['password'])
            ||X::emptyEx($_IS['username'])
            ||X::emptyEx($_IS['password'])
        ){
            echo '{"method":"register","status":"error","error","values undefined"}';
            return;
        }
        
        $username = addslashes(trim($_IS['username']));
        $password = addslashes(trim($_IS['password']));
        $ip = X::getIP();
        
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        
        /* 启用了一个IP只能注册4个账户的限制 */
        if(MYSQL::exist($link,"SELECT * FROM users WHERE ip='$ip'")<=3){
            /* 防止重名 */
            if(!MYSQL::exist($link,"SELECT * FROM users WHERE username='$username'")){
                /* 创建新用户 */
                MYSQL::query(
                    $link,
                    "INSERT INTO users (username, password, ip) 
                    VALUES ('$username','".X::salt($password)."','$ip')"
                );
                
                /* 创建新用户的额外字段 */
                
                MYSQL::query(
                    $link,
                    "INSERT INTO users_meta (user_id, name, value) 
                    VALUES (".MYSQL::lastID($link).",'level','0')"
                );
                
                /* 装载插件 */
                
                COIN::create_coin_column($link,MYSQL::lastID($link)); //积分插件
                    
                echo '{"method":"register","status":"ok"}';
            }else{
                echo '{"method":"register","status":"error","error":"user existed"}';
            }
        }else{
            echo '{"method":"register","status":"error","error":"ip blocked"}';
        }
        
        MYSQL::close($link);
    }
    
    /* 获取用户信息 */
    static function get_user_meta($_IS){
        if(
            !isset($_IS['cookie'])
            ||!isset($_IS['key'])
            ||X::emptyEx($_IS['cookie'])
            ||X::emptyEx($_IS['key'])
        ){
            echo '{"method":"get_user_meta","status":"error","error","values undefined"}';
            return;
        }
        
        $key = addslashes(trim($_IS['key']));
        
        if(API::verify_cookie($_IS)){
            $link = MYSQL::connect();
            MYSQL::selectDB($link,constant("mysql_db"));
            $user_id = API::get_cookie_userid($_IS['cookie']);
            $query = MYSQL::query($link,"SELECT * FROM users_meta WHERE user_id=$user_id and name='$key'");
			$result = MYSQL::fetch($query);
            $value = $result['value'];
            echo '{"method":"get_user_meta","status":"ok","key":"'.$key.'","value":"'.$value.'"}';
        }else{
            echo '{"method":"get_user_meta","status":"error","error":"verify failed"}';
        }
    }
}
?>