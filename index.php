<?php
/*
    Xnoopsy PHP
    可扩展的App后端系统
    版本 Alpha 1.0.4
*/

/* 引用必要文件 */
require("base/config.php");
require("base/mysql.php");
require("base/function.php");
require("base/api.php");

/* 引用插件函数库 */
require("plugin/coin.php");
require("plugin/propose.php");

/* 引用模型文件 */
require("model/user.php");
require("model/post.php");
require("model/comment.php");
require("model/cloud.php");
require("model/plugin.php");
require("model/propose.php");

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
        
    }else if($api=="get_propose"){
        
        PP::get_propose($arguments);
        
    }else if($api=="get_recent_proposes"){
        
        PP::get_recent_proposes($arguments);
        
    }else if($api=="create_propose"){
        
        PP::create_propose($arguments);
        
    }else if($api=="sign_propose"){
        
        PP::sign_propose($arguments);
        
    }else if($api=="get_sign_num"){
        
        PP::get_sign_num($arguments);
        
    }else if($api=="get_sign_status"){
        
        PP::get_sign_status($arguments);
        
    }else if($api=="get_coin"){
        
        PLUGIN::get_coin($arguments);
        
    } else {
        echo '{"method":"API","status":"error","error","Cannot find this API"}';
    }
} else {
    echo '{"license":"iDea Leaper Technology","version":"Alpha 1.0.4"}';
}
?>