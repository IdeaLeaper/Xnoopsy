<?php
class CLOUD{
    static function upload_image($_IS){
        if(
            !isset($_IS['base'])
            ||X::emptyEx($_IS['base'])
        ){
            echo '{"method":"get_coin","status":"error","error":"base64 undefined"}';
            return 0;
        }
        
        $base64_string = trim($_IS['base']);
        $base64_result = substr($base64_string, strpos($base64_string,",")+1);
        
        $decode_image = base64_decode($base64_result);
        $file = "./tmp/".time().".jpg";
        file_put_contents($file, $decode_image);
        
        $fi = new finfo(FILEINFO_MIME_TYPE); 
        $mime_type = $fi->file($file); 
        if($mime_type!="image/jpeg"){
            unlink($file);
            echo '{"method":"upload_image","status":"error","error":"type"}';
            return;
        }
        
        $path = "@".realpath($file); //要上传的文件  
        $fields = array(
            "file" => $path,
            "token" => API::get_upload_token()
        );
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL,"http://upload.qiniu.com/");    
        curl_setopt($ch, CURLOPT_POST, 1 );  
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        
        $json = curl_exec ($ch);
        curl_close ($ch);
        
        unlink($file);
        echo $json;
    }
}
?>