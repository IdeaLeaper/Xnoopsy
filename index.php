<?php
/*
    Xnoopsy PHP
    一个简易的App后端系统
    版本 Alpha 0315
*/

/* 引用必要文件 */
require("base/config.php");
require("base/mysql.php");
require("base/function.php");
require("base/api.php");

/* 引用插件 */
require("plugin/coin.php");

/* 引用模型文件 */
require("model/user.php");
require("model/post.php");
require("model/comment.php");
require("model/cloud.php");
require("model/plugin.php");

/* 主接口 */
if(isset($_REQUEST['api'])){
    
    /* 导入查询参数表 */
    $arguments=$_REQUEST;
    $api=$arguments['api'];
    
    /* API动作处理 */
    if($api=="register"){
        
        USER::register($arguments);
        
    }else if($api=="get_cookie"){
        
        USER::get_cookie($arguments);
        
    }else if($api=="verify_cookie"){
        
        USER::verify_cookie($arguments);
        
    }else if($api=="get_user_meta"){
        
        USER::get_user_meta($arguments);
        
    }else if($api=="create_post"){
        
        POST::create_post($arguments);
        
    }else if($api=="edit_post"){
        
        POST::edit_post($arguments);
        
    }else if($api=="get_post"){
        
        POST::get_post($arguments);
        
    }else if($api=="get_recent_posts"){
        
        POST::get_recent_posts($arguments);
        
    }else if($api=="get_search_posts"){
        
        POST::get_search_posts($arguments);
        
    }else if($api=="get_tag_posts"){
        
        POST::get_tag_posts($arguments);
        
    }else if($api=="get_user_posts"){
        
        POST::get_user_posts($arguments);
        
    }else if($api=="submit_comment"){
        
        COMMENT::submit_comment($arguments);
        
    }else if($api=="get_comment"){
        
        COMMENT::get_comment($arguments);
        
    }else if($api=="upload_image"){
        
        CLOUD::upload_image($arguments);
        
    }else if($api=="get_coin"){
        
        PLUGIN::get_coin($arguments);
        
    } else {
        echo '{"method":"API","status":"error","error","Cannot find this API"}';
    }
} else {
    echo '{"license":"iDea Leaper Technology","version":"alpha 0315"}';
}
?>