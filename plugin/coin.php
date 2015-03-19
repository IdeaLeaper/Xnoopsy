<?php
class COIN{
    /* 创建COIN列 */
    static function create_coin_column($user_id){
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        MYSQL::query(
            $link,
            "INSERT INTO users_meta (user_id, name, value) 
            VALUES (".$user_id.",'coin','100')"
        );
        MYSQL::close($link);
    }
    
    /* 获得COIN值(内部API) */
    static function get_coin($user_id){
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        $user_id = intval($user_id);
        $query = MYSQL::query($link,"SELECT * FROM users_meta WHERE user_id=$user_id and name='coin'");
        $result = MYSQL::fetch($query);
        $value = $result['value'];
        MYSQL::close($link);
        return intval($value);
    }
    
    /* 增加积分(内部API) */
    static function plus($user_id,$num){
        $num = intval($num);
        $user_id = intval($user_id);
        
        $value = self::get_coin($user_id);
        $calc_result = $value + $num;
        
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        
        MYSQL::query(
            $link,
            "UPDATE users_meta SET value=$calc_result WHERE user_id=$user_id"
        );
        
        MYSQL::close($link);
        return;
    }
}
?>