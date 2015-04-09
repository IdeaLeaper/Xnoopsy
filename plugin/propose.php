<?php
class PROPOSE{
    static function sign($user_id, $pp_id, $name){
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        
        if(MYSQL::exist($link,"SELECT * FROM proposes WHERE pp_id=$pp_id")){
            if(MYSQL::exist($link,"SELECT * FROM sign WHERE pp_id=$pp_id and user_id=$user_id")){
                $return = -244;
            }else{
                MYSQL::query(
                    $link,
                    "INSERT INTO sign (user_id, pp_id, name) 
                    VALUES ($user_id, $pp_id, '$name')"
                );
                $return = $pp_id;
            }
        }else{
            $return = -1; //未找到
        }
        
        MYSQL::close($link);
        return $return;
    }
    
    static function create($user_id, $post_id, $title, $content){
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        
        if(MYSQL::exist($link,"SELECT * FROM posts WHERE post_id=$post_id")){
            /* 创建新PROPOSE */
            MYSQL::query(
                $link,
                "INSERT INTO proposes (user_id, post_id, title, content) 
                VALUES ($user_id, $post_id,'$title','$content')"
            );

            $return = MYSQL::lastID($link);
        } else {
            $return = -1;
        }
        
        MYSQL::close($link);
        return $return;
    }
}
?>