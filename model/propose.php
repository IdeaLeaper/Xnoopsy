<?php
class PP{
    static function sign_propose($_IS){
        if(
            !isset($_IS['pp_id'])
            ||!isset($_IS['cookie'])
            ||X::emptyEx($_IS['pp_id'])
            ||X::emptyEx($_IS['cookie'])
        ){
            echo '{"method":"sign_propose","status":"error","error":"values undefined"}';
            return 0;
        }
        
        $pp_id = intval($_IS['pp_id']);
        
        $cookie = addslashes(trim($_IS['cookie']));
        
        if(API::verify_cookie($cookie)){
            
            $user_id = API::get_cookie_userid($cookie);
            $name = API::get_cookie_username($cookie);
            $result = PROPOSE::sign($user_id, $pp_id, $name);
            if($result == -1){
                echo '{"method":"sign_propose","status":"error","error":"not existed"}';
            } else if($result == -244){
                echo '{"method":"sign_propose","status":"error","error":"existed"}';
            } else {
                echo '{"method":"sign_propose","status":"ok","pp_id":'.$result.'}';
            }
            
        }else{
            echo '{"method":"sign_propose","status":"error","error":"verify failed"}';
        }
    }
    
    static function create_propose($_IS){
        if(
            !isset($_IS['title'])
            ||!isset($_IS['content'])
            ||!isset($_IS['cookie'])
            ||!isset($_IS['post_id'])
            ||X::emptyEx($_IS['title'])
            ||X::emptyEx($_IS['content'])
            ||X::emptyEx($_IS['cookie'])
            ||X::emptyEx($_IS['post_id'])
        ){
            echo '{"method":"create_propose","status":"error","error":"values undefined"}';
            return;
        }
        
        $title = addslashes(trim($_IS['title']));
        $content = addslashes($_IS['content']);
        $post_id = intval($_IS['post_id']);
        $cookie = addslashes(trim($_IS['cookie']));
        
        if(API::verify_cookie($cookie)){
            
            $user_id = API::get_cookie_userid($cookie);
            $pp_id = PROPOSE::create($user_id, $post_id, $title, $content);
            
            if($pp_id == -1){
                echo '{"method":"create_propose","status":"error","error":"not existed"}';
            }else{
                /* 载入插件 */
                COIN::plus(API::get_cookie_userid($cookie), "10"); //增加20积分
                echo '{"method":"create_propose","status":"ok","pp_id":'.$pp_id.'}';
            }
        }else{
            echo '{"method":"create_propose","status":"error","error":"verify failed"}';
        }
    }
    
    static function get_propose($_IS){
        if(
            !isset($_IS['pp_id'])
            ||X::emptyEx($_IS['pp_id'])
        ){
            echo '{"method":"get_propose","status":"error","error":"pp_id undefined"}';
            return 0;
        }
        
        $pp_id = intval($_IS['pp_id']);
        
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        
        /* 获得 */
        $result = MYSQL::assoc($link,"SELECT * FROM proposes WHERE pp_id=$pp_id");
        
        /* 判断是否存在 */
        if(!isset($result['pp_id'])){
            MYSQL::close($link);
            echo '{"method":"get_propose","status":"error","error":"not existed"}';
            return;
        }
        
        MYSQL::close($link);
        
        /* JSON输出 */
        $json = array(
            "method" => "get_propose",
            "status" => "ok",
            "pp_id" => $pp_id,
            "user_id" => intval($result['user_id']),
            "author" => API::get_id_username(intval($result['user_id'])),
            "title" => htmlspecialchars($result['title']),
            "content" => X::br(htmlspecialchars($result['content']))
        );
        echo json_encode($json);
    }
    
    /* 获得最近posts */
    static function get_recent_proposes($_IS){
        if(
            !isset($_IS['page'])
            ||X::emptyEx($_IS['page'])
            
        ){
            $page = 1;
        }else{
            $page = intval($_IS['page']);
        }
        
        if(!isset($_IS['post_id'])||X::emptyEx($_IS['post_id'])){
            echo '{"method":"get_recent_proposes","status":"error","error":"post_id undefined"}';
            return;
        }
        
        $post_id = intval($_IS['post_id']);
        
        /* 页数处理 */
        if($page == 0){$page = 1;}
        
        $pagesize = 10;
        
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        $result = MYSQL::query($link,"select * from proposes where post_id=$post_id");
        $numrows = MYSQL::rows($result);

        $pages = intval($numrows/$pagesize)+1;
        
        if($page>$pages){
            MYSQL::close($link);
            echo '{"method":"get_recent_propose","status":"error","error":"pageover"}';
            return 0;
        }
        
        $offset = $pagesize*($page - 1);
        
        $result = MYSQL::query($link,"select * from proposes where post_id=$post_id order by pp_id desc limit $offset,$pagesize");
        $i = 0;
        
        /* 组合数据 */
        while($rows = MYSQL::fetch($result)){
            $proposes[$i] = array(
                "pp_id" => intval($rows['pp_id']),
                "title" => htmlspecialchars($rows['title'])
            );
            $i++;
        }
        
        MYSQL::close($link);
        
        
        /* 不存在项的处理 */
        if(!isset($proposes)){
            $proposes = array();
        }
        
        /* 完成JSON输出 */
        $json = array(
            "method" => "get_recent_propose",
            "status" => "ok",
            "pages" => $pages,
            "pagenow" => $page,
            "proposes" => $proposes
        );
        echo json_encode($json);
    }
    
    static function get_sign_num($_IS){
        if(!isset($_IS['pp_id'])||X::emptyEx($_IS['pp_id'])){
            echo '{"method":"get_sign_num","status":"error","error":"pp_id undefined"}';
            return;
        }
        
        $pp_id = intval($_IS['pp_id']);
        
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
		
		$sign_num = MYSQL::rows(MYSQL::query($link,"select * from sign where pp_id=$pp_id"));
		echo '{"method":"get_sign_num","status":"ok","num":'.intval($sign_num).'}';
		
        MYSQL::close($link);
        
        
        
    }
}
?>