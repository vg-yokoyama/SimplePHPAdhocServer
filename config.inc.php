<?php
/*
 * DB Configuration
 */
define("HOST_URL" , "http://example.com/adhoc/");

define("DB_NAME" , "phpadhoc");
define("DB_HOST" , "localhost");
define("DB_USER" , "username");
define("DB_PASS" , "password");

define("DEBUG_MODE" , 0); // 1にすると画面上にエラーを表示します。

date_default_timezone_set("Asia/Tokyo");
mb_internal_encoding("utf-8");
if(DEBUG_MODE){
    error_reporting( E_ALL^E_NOTICE );
    ini_set( 'display_errors', 'on' );
}else{
    error_reporting(0);
    ini_set( 'display_errors', 'off' );
}
ini_set('include_path',ini_get('include_path').':'.dirname(__FILE__)."/lib");

/**
 * shared connection func
 */
$_conn;
$_connection_error;
function getConnection(){
    global $_conn,$_connection_error;
    if(!$_conn){
        try {
            $_conn = new PDO('mysql:host='.DB_HOST.';port=3306;dbname='.DB_NAME, DB_USER, DB_PASS);
            $_conn->setAttribute(PDO::ATTR_PERSISTENT, true);
            $_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $_conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            $_conn->query('SET CHARACTER SET UTF8');
        } catch (PDOException $exc) {
             $_connection_error = $exc->getMessage();
            return false;
        }
    }
    return $_conn;
}

?>
