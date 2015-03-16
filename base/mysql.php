<?php
class MYSQL{
    static function connect(){
        return mysqli_connect(constant("mysql_host"),constant("mysql_user"),constant("mysql_pass"));
    }
    
    static function selectDB($link,$DB){
        return mysqli_select_db($link,$DB);
    }
    
    static function close($link){
        return mysqli_close($link);
    }
    
    static function query($link,$sql)
    {
        mysqli_query($link,"set names utf8");
        return mysqli_query($link,$sql);
    }
    
    static function fetch($result)
    {
        $ret = mysqli_fetch_array($result, MYSQL_ASSOC);
        return $ret;
    }
    
    static function rows($result)
    {
        $ret = mysqli_num_rows($result);
        return $ret;
    }
    
    static function assoc($link,$sql)
    {
        $result = self::query($link,$sql);
        $ret = MYSQL::fetch($result);
        return $ret;
    }
    
    static function exist($link,$sql)
    {
        $result = self::query($link,$sql);
        return mysqli_num_rows($result);
    }
    
    static function lastId($link){
        return mysqli_insert_id($link);
    }
}
?>