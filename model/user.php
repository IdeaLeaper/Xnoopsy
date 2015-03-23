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
        
        $username = addslashes(trim($_IS['username']));
        $password = addslashes(trim($_IS['password']));
        
        if(API::user_verify($username, $password)){
            echo '{"method":"get_cookie","status":"ok","cookie":"'.API::make_cookie($username, $password).'","user_id":'.API::get_username_id($username).'}';
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
        
        $cookie = addslashes(trim($_IS['cookie']));
        
        if(API::verify_cookie($cookie)){
            echo '{"method":"verify_cookie","status":"ok","user_id":'.API::get_cookie_userid($cookie).'}';
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
        
        $result = API::register($username, $password);
        if($result == -255){
            echo '{"method":"register","status":"error","error":"ip blocked"}';
        }else if($result == -244){
            echo '{"method":"register","status":"error","error":"user existed"}';
        }else {
            COIN::create_coin_column($result); //积分插件
            echo '{"method":"register","status":"ok","cookie":"'.API::make_cookie($username, $password).'","user_id":'.$result.'}';
        }
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
        $cookie = addslashes(trim($_IS['cookie']));
        
        if(API::verify_cookie($cookie)){
            $user_id = API::get_cookie_userid($cookie);
            $value = API::get_user_meta($user_id, $key);
            echo '{"method":"get_user_meta","status":"ok","key":"'.$key.'","value":"'.$value.'"}';
        }else{
            echo '{"method":"get_user_meta","status":"error","error":"verify failed"}';
        }
    }
    
}
?>