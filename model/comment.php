<?php
class COMMENT{
    
    /* 发布评论 */
    static function submit_comment($_IS){
        if(
            !isset($_IS['post_id'])
            ||!isset($_IS['cookie'])
            ||!isset($_IS['content'])
            ||X::emptyEx($_IS['post_id'])
            ||X::emptyEx($_IS['cookie'])
            ||X::emptyEx($_IS['content'])
        ){
            echo '{"method":"submit_comment","status":"error","error":"values undefined"}';
            return 0;
        }
        
        $post_id = intval($_IS['post_id']);
        $content = addslashes($_IS['content']);
        
        $cookie = addslashes(trim($_IS['cookie']));
        
        if(API::verify_cookie($cookie)){
            
            $user_id = API::get_cookie_userid($cookie);
            $result = API::submit_comment($user_id, $post_id, $content);
            if($result == -1){
                echo '{"method":"submit_comment","status":"error","error":"not existed"}';
            } else {
                COIN::plus(API::get_cookie_userid($_IS['cookie']),"5"); //增加5积分
                echo '{"method":"submit_comment","status":"ok","post_id":'.$result.'}';
            }
            
        }else{
            echo '{"method":"submit_comment","status":"error","error":"verify failed"}';
        }
    }
    
    /* 获得文章下属评论 */
    static function get_comment($_IS){
        if(
            !isset($_IS['post_id'])
            ||X::emptyEx($_IS['post_id'])
        ){
            echo '{"method":"get_comment","status":"error","error":"post_id undefined"}';
            return 0;
        }
        
        $post_id = intval($_IS['post_id']);
        
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        
        /* 检测POST是否存在 */
        if(!MYSQL::exist($link,"SELECT * FROM posts WHERE post_id=$post_id")){
            MYSQL::close($link);
            echo '{"method":"get_comment","status":"error","error":"not existed"}';
            return;
        }
        
        /* 获得评论 */
        $i = 0;
        $comments_query = MYSQL::query($link,"SELECT * FROM comments WHERE post_id=$post_id order by comment_id desc");
        while($rows = MYSQL::fetch($comments_query)){
            $comments[$i] = array(
                "author" => API::get_id_username(intval($rows['user_id'])),
                "content" => X::br(htmlspecialchars($rows['content']))
            );
            $i++;
        }
        
        MYSQL::close($link);
        
        /* 不存在项的处理 */
        
        if(!isset($comments)){
            $comments = array();
        }
        
        /* JSON输出 */
        $json = array(
            "method" => "get_comment",
            "status" => "ok",
            "post_id" => $post_id,
            "comments" => $comments
        );
        echo json_encode($json);
    }
}
?>