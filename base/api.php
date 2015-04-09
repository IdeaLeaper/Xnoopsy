<?php
class API{
    
    /* 验证用户 */
    static function user_verify($username, $password){
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
    static function make_cookie($username, $password){
        $cookie_format = $username."|".$password;
        $cookie = X::encode($cookie_format);
        return $cookie;
    }
    
    /* 验证用户COOKIE */
    static function verify_cookie($cookie){
        $cookie_format = X::decode($cookie);
        $result = explode("|",$cookie_format,2);
        if(count($result)==1){
            return false;
        }
        if(self::user_verify($result[0], $result[1])){
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
    
    /* 获得用户信息 */
    static function get_user_meta($user_id, $key){
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        $query = MYSQL::query($link,"SELECT * FROM users_meta WHERE user_id=$user_id and name='$key'");$result = MYSQL::fetch($query);
        $value = $result['value'];
        return $value;
    }
    
    /* 创建用户 */
    static function register($username, $password){
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        
        /* 防止重名 */
        if(!MYSQL::exist($link,"SELECT * FROM users WHERE username='$username'")){
            /* 创建新用户 */
            MYSQL::query(
                $link,
                "INSERT INTO users (username, password) 
                VALUES ('$username','".X::salt($password)."')"
            );
		
			$user_id = MYSQL::lastID($link);
            
            /* 创建新用户的额外字段 */
            
            MYSQL::query(
                $link,
                "INSERT INTO users_meta (user_id, name, value) 
                VALUES (".$user_id.",'level','0')"
            );
            
            $return = $user_id;
        }else{
            $return = -244; //重复
        }
        
        MYSQL::close($link);
        return $return;
    }
    
    /* 发布评论 */
    static function submit_comment($user_id, $post_id, $content){
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        
        if(MYSQL::exist($link,"SELECT * FROM posts WHERE post_id=$post_id")){
            /* 创建新POST */
            MYSQL::query(
                $link,
                "INSERT INTO comments (user_id, post_id, content) 
                VALUES ($user_id, $post_id, '$content')"
            );
            $return = $post_id;
        }else{
            $return = -1; //未找到
        }
        
        MYSQL::close($link);
        return $return;
    }
    
    /* 发布POST */
    static function create_post($user_id, $title, $content, $image, $tags = array()){
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        
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
        if(count($tags)){
            for($i=0;$i<=count($tags)-1;$i++){
                MYSQL::query(
                    $link,
                    "INSERT INTO tags (post_id, name) 
                    VALUES ($post_id,'$tags[$i]')"
                );
            }
        }
        
        MYSQL::close($link);
        return $post_id;
    }
    
    /* 编辑POST */
    static function edit_post($user_id, $post_id, $content, $image_in = null, $tags = array()){
        $link = MYSQL::connect();
        MYSQL::selectDB($link,constant("mysql_db"));
        
        
        /* 成功性判断 */
        $result = MYSQL::assoc($link,"SELECT * FROM posts WHERE post_id=$post_id");
        
        if(!$result['post_id']){
            MYSQL::close($link);
            return -1; //未找到
        }else{
            $image = $result['image'];
        }
        
        $user_query = MYSQL::query($link,"SELECT * FROM users_meta WHERE user_id=$user_id and name='level'");
        $result_new = MYSQL::fetch($user_query);
        
        if(intval($result_new['value'])<1&&$user_id!=$result['user_id']){
            MYSQL::close($link);
            return -254; //权限错误
        }
        
        /* 更新POST */
        MYSQL::query(
            $link,
            "UPDATE posts SET content='$content' WHERE post_id=$post_id"
        );
        
        /* 选择性更新图片 */
        if($image_in){
            $image = $image_in;
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
        if(count($tags)){
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
        return $post_id;
    }
    
    /* 获取上传凭证 */
    static function get_upload_token(){
        $bucket = 'udia';
        $AccessKey = 'nOt76L7fmswbaMYfdtxvkGj4licWzqDq2cFYogG7';
        $SecretKey = 'sqxbYsPYxv5f1KcTMvDPsKkiMxGWIat0MLo5gMZp';
        $deadline = time()+172800;
        $policy_json = array(
            "scope" => $bucket,
            "deadline" => intval($deadline),
            "returnBody" => '{"method":"upload_image","status":"ok","key":$(key),"url":"http://7xi6fz.com1.z0.glb.clouddn.com/"}'
        );
        $putPolicy = json_encode($policy_json);
        $encodedPutPolicy = X::urlsafe_base64_encode($putPolicy);
        $sign = X::hmac_sha1($encodedPutPolicy, $SecretKey);
        $encodedSign = X::urlsafe_base64_encode($sign);
        $uploadToken = $AccessKey.':'.$encodedSign.':'.$encodedPutPolicy;
        return $uploadToken;
    }
}
?>