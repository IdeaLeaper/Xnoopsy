<?php
class POST{
    
    /* 发布POST */
    static function create_post($_IS){
        if(
            !isset($_IS['title'])
            ||!isset($_IS['content'])
            ||!isset($_IS['cookie'])
            ||X::emptyEx($_IS['title'])
            ||X::emptyEx($_IS['content'])
            ||X::emptyEx($_IS['cookie'])
        ){
            echo '{"method":"create_post","status":"error","error","values undefined"}';
            return 0;
        }
        
        $title = addslashes(trim($_IS['title']));
        $content = addslashes($_IS['content']);
        if(isset($_IS['image'])&&!X::emptyEx($_IS['image'])){
            $image = addslashes(trim($_IS['image']));
        }else{
            $image = null;
        }
        
        if(isset($_IS['tags'])&&!X::emptyEx($_IS['tags'])){
            $tags = explode(",",addslashes(trim($_IS['tags'])));
        }
        
        if(API::verify_cookie($_IS)){
            $link = MYSQL::connect();
            MYSQL::selectDB($link,constant("mysql_db"));
            
            $user_id = API::get_cookie_userid($_IS['cookie']);
            
            /* 创建新POST */
            MYSQL::query(
                $link,
                "INSERT INTO posts (user_id, title, content, image) 
                VALUES ($user_id,'$title','$content','$image')"
            );
            
            $post_id=MYSQL::lastID($link);
            
            /* 创建内容备份 */
            MYSQL::query(
                $link,
                "INSERT INTO backup (post_id, content, image) 
                VALUES ($post_id,'$content','$image')"
            );
            
            
            /* 插入标签表 */
            if(isset($_IS['tags'])&&!X::emptyEx($_IS['tags'])){
                for($i=0;$i<=count($tags)-1;$i++){
                    MYSQL::query(
                        $link,
                        "INSERT INTO tags (post_id, name) 
                        VALUES ($post_id,'$tags[$i]')"
                    );
                }
            }
            
            MYSQL::close($link);
            
            /* 载入插件 */
            COIN::plus(API::get_cookie_userid($_IS['cookie']),"20"); //增加20积分
            
            echo '{"method":"create_post","status":"ok","post_id":'.$post_id.'}';
        }else{
            echo '{"method":"create_post","status":"error","error","verify failed"}';
        }
    }
    
    /* 获得单个POST */
    static function get_post($_IS){
        if(
            !isset($_IS['post_id'])
            ||X::emptyEx($_IS['post_id'])
        ){
            echo '{"method":"get_post","status":"error","error","post_id undefined"}';
            return 0;
        }
        
        $post_id = intval($_IS['post_id']);
        
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        
        /* 获得POST */
        $result = MYSQL::assoc($link,"SELECT * FROM posts WHERE post_id=$post_id");
        
        /* 判断POST是否存在 */
        if(!isset($result['post_id'])){
            MYSQL::close($link);
            echo '{"method":"get_post","status":"error","error","not existed"}';
            return;
        }
        
        /* 获得Tags */
        $i = 0;
        $tags_query = MYSQL::query($link,"SELECT * FROM tags WHERE post_id=$post_id");
        while($rows = MYSQL::fetch($tags_query)){
            $tags[$i] = htmlspecialchars($rows['name']);
            $i++;
        }
        
        MYSQL::close($link);
        
        /* 不存在项的处理 */
        if(!isset($tags)){
            $tags = array();
        }
        
        /* JSON输出 */
        $json = array(
            "method" => "get_posts",
            "status" => "ok",
            "post_id" => $post_id,
            "author" => API::get_id_username(intval($result['user_id'])),
            "title" => htmlspecialchars($result['title']),
            "content" => X::br(htmlspecialchars($result['content'])),
            "image" => $result['image'],
            "tags" => $tags
        );
        echo json_encode($json);
    }
    
    /* 获得最近posts */
    static function recent_posts($_IS){
        if(
            !isset($_IS['page'])
            ||X::emptyEx($_IS['page'])
        ){
            $page = 1;
        }else{
            $page = intval($_IS['page']);
        }
        
        /* 页数处理 */
        if($page == 0){$page = 1;}
        
        $pagesize = 10;
        
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        $result = MYSQL::query($link,"select * from posts");
        $numrows = MYSQL::rows($result);

        $pages = intval($numrows/$pagesize)+1;
        
        if($page>$pages){
            MYSQL::close($link);
            echo '{"method":"recent_posts","status":"error","error","pageover"}';
            return 0;
        }
        
        $offset = $pagesize*($page - 1);
        
        $result = MYSQL::query($link,"select * from posts order by post_id desc limit $offset,$pagesize");
        $i = 0;
        
        /* 组合数据 */
        while($rows = MYSQL::fetch($result)){
            $posts[$i] = array(
                "post_id" => intval($rows['post_id']),
                "title" => htmlspecialchars($rows['title']),
                "excerpt" => X::br(htmlspecialchars(mb_substr($rows['content'],0,30,"utf-8")))."...",
                "image" => $rows['image']
            );
            $i++;
        }
        
        MYSQL::close($link);
        
        
        /* 不存在项的处理 */
        if(!isset($posts)){
            $posts = array();
        }
        
        /* 完成JSON输出 */
        $json = array(
            "method" => "recent_posts",
            "status" => "ok",
            "count" => $numrows,
            "pages" => $pages,
            "pagenow" => $page,
            "posts" => $posts
        );
        echo json_encode($json);
    }
    
    /* 搜索posts */
    static function search_posts($_IS){
        if(
            !isset($_IS['search'])
            ||X::emptyEx($_IS['search'])
        ){
            echo '{"method":"create_post","status":"error","error","search undefined"}';
            return 0;
        }
        
        /* 搜索关键字处理 */
        $search_query = $_IS['search'];
        $search = "%";

        for($i=0;$i<=mb_strlen($search_query,"utf-8")-1;$i++){
            $search=$search.mb_substr($search_query,$i,1,"utf-8")."%";
        }
        
        $search = addslashes($search);
        
        /* 页数处理 */
        if(
            !isset($_IS['page'])
            ||X::emptyEx($_IS['page'])
        ){
            $page = 1;
        }else{
            $page = intval($_IS['page']);
        }
        
        if($page == 0){$page = 1;}
        
        $pagesize = 10;
        
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        $result = MYSQL::query($link,"select * from posts WHERE CONCAT(`title`,`content`) LIKE '%$search%'");
        $numrows = MYSQL::rows($result);

        $pages = intval($numrows/$pagesize)+1;
        
        if($page>$pages){
            MYSQL::close($link);
            echo '{"method":"search_posts","status":"error","error","pageover"}';
            return 0;
        }
        
        $offset = $pagesize*($page - 1);
        
        $result = MYSQL::query($link,"select * from posts WHERE CONCAT(`title`,`content`) LIKE '$search' order by post_id desc limit $offset,$pagesize");
        
        $i = 0;
        
        /* 组合数据 */
        while($rows = MYSQL::fetch($result)){
            $posts[$i] = array(
                "post_id" => intval($rows['post_id']),
                "title" => htmlspecialchars($rows['title']),
                "excerpt" => X::br(htmlspecialchars(mb_substr($rows['content'],0,30,"utf-8")))."...",
                "image" => $rows['image']
            );
            $i++;
        }
        
        MYSQL::close($link);
        
        /* 不存在项的处理 */
        if(!isset($posts)){
            $posts = array();
        }
        
        /* 完成JSON输出 */
        $json = array(
            "method" => "recent_posts",
            "status" => "ok",
            "count" => $numrows,
            "pages" => $pages,
            "pagenow" => $page,
            "posts" => $posts
        );
        echo json_encode($json);
    }
    
    /* 获得指定标签的posts */
    static function tag_posts($_IS){
        if(
            !isset($_IS['tag'])
            ||X::emptyEx($_IS['tag'])
        ){
            echo '{"method":"tag_post","status":"error","error","tag undefined"}';
            return 0;
        }
        
        /* Tag关键字处理 */
        $tag_query = $_IS['tag'];
        $tag = "%";

        for($i=0;$i<=mb_strlen($tag_query,"utf-8")-1;$i++){
            $tag=$tag.mb_substr($tag_query,$i,1,"utf-8")."%";
        }
        
        $tag = addslashes($tag);
        
        if(
            !isset($_IS['page'])
            ||X::emptyEx($_IS['page'])
        ){
            $page = 1;
        }else{
            $page = intval($_IS['page']);
        }
        
        /* 页数处理 */
        if($page == 0){$page = 1;}
        
        $pagesize = 10;
        
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        $result = MYSQL::query($link,"select * from tags where name LIKE '$tag'");
        $numrows = MYSQL::rows($result);

        $pages = intval($numrows/$pagesize)+1;
        
        if($page>$pages){
            MYSQL::close($link);
            echo '{"method":"tag_posts","status":"error","error","pageover"}';
            return 0;
        }
        
        $offset = $pagesize*($page - 1);
        $result = MYSQL::query($link,"select * from tags WHERE name LIKE '$tag' order by post_id desc limit $offset,$pagesize");
        
        $i = 0;
        
        /* 组合数据 */
        while($rows = MYSQL::fetch($result)){
            $post_id = $rows['post_id'];
            $post_query = MYSQL::query($link,"select * from posts where post_id=$post_id");
            $post = MYSQL::fetch($post_query);
            $posts[$i] = array(
                "post_id" => intval($post['post_id']),
                "title" => htmlspecialchars($post['title']),
                "excerpt" => X::br(htmlspecialchars(mb_substr($post['content'],0,30,"utf-8")))."...",
                "image" => $post['image']
            );
            $i++;
        }
        
        MYSQL::close($link);
        
        /* 不存在项的处理 */
        if(!isset($posts)){
            $posts = array();
        }
        
        /* 完成JSON输出 */
        $json = array(
            "method" => "recent_posts",
            "status" => "ok",
            "count" => $numrows,
            "pages" => $pages,
            "pagenow" => $page,
            "posts" => $posts
        );
        echo json_encode($json);
    }
    
    /* 编辑POST */
    static function edit_post($_IS){
        if(
            !isset($_IS['post_id'])
            ||!isset($_IS['content'])
            ||!isset($_IS['cookie'])
            ||X::emptyEx($_IS['post_id'])
            ||X::emptyEx($_IS['content'])
            ||X::emptyEx($_IS['cookie'])
        ){
            echo '{"method":"edit_post","status":"error","error","values undefined"}';
            return 0;
        }
        
        $post_id = intval($_IS['post_id']);
        $content = addslashes($_IS['content']);
        
        if(isset($_IS['tags'])&&!X::emptyEx($_IS['tags'])){
            $tags = explode(",",addslashes(trim($_IS['tags'])));
        }
        
        if(API::verify_cookie($_IS)){
            $link = MYSQL::connect();
            MYSQL::selectDB($link,constant("mysql_db"));
            

            
            /* 成功性判断 */
            $result = MYSQL::assoc($link,"SELECT * FROM posts WHERE post_id=$post_id");
            
            if(!$result['post_id']){
                MYSQL::close($link);
                echo '{"method":"edit_post","status":"error","error","not existed"}';
                return;
            }else{
                $image = $result['image'];
            }
            
            $user_id = API::get_cookie_userid($_IS['cookie']);
            $user_query = MYSQL::query($link,"SELECT * FROM users_meta WHERE user_id=$user_id and name='level'");
            $result_new = MYSQL::fetch($user_query);
            
            if(intval($result_new['value'])<1&&$user_id!=$result['user_id']){
                MYSQL::close($link);
                echo '{"method":"edit_post","status":"error","error","level failed"}';
                return;
            }
            
            /* 更新POST */
            MYSQL::query(
                $link,
                "UPDATE posts SET content='$content' WHERE post_id=$post_id"
            );
            
            /* 选择性更新图片 */
            if(isset($_IS['image'])&&!X::emptyEx($_IS['image'])){
                $image = addslashes(trim($_IS['image']));
                MYSQL::query(
                    $link,
                    "UPDATE posts SET image='$image' WHERE post_id=$post_id"
                );
            }

            /* 创建内容备份 */
            MYSQL::query(
                $link,
                "INSERT INTO backup (post_id, content, image) 
                VALUES ($post_id,'$content','$image')"
            );
            
            
            /* 重置标签表 */
            if(isset($_IS['tags'])&&!X::emptyEx($_IS['tags'])){
                MYSQL::query($link,"DELETE FROM tags WHERE post_id=$post_id");
                for($i=0;$i<=count($tags)-1;$i++){
                    MYSQL::query(
                        $link,
                        "INSERT INTO tags (post_id, name) 
                        VALUES ($post_id,'$tags[$i]')"
                    );
                }
            }
            
            MYSQL::close($link);
            
            echo '{"method":"edit_post","status":"ok","post_id":'.$post_id.'}';
        }else{
            echo '{"method":"edit_post","status":"error","error","verify failed"}';
        }
    }
}
?>