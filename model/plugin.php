<?php
class PLUGIN{
    
    /* 获得积分数量 */
    static function get_coin($_IS){
        if(
            !isset($_IS['cookie'])
            ||X::emptyEx($_IS['cookie'])
        ){
            echo '{"method":"get_coin","status":"error","error":"cookie undefined"}';
            return 0;
        }
        
        $cookie = addslashes(trim($_IS['cookie']));
        
        echo '{"method":"get_coin","status":"ok","coin":'.COIN::get_coin(API::get_cookie_userid($cookie)).'}';
    }
}
?>