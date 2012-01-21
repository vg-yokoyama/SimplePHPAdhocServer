<?php
/*
 * DB Configuration
 */
date_default_timezone_set("Asia/Tokyo");
mb_internal_encoding("utf-8");

error_reporting( E_ALL );
ini_set( 'display_errors', 'on' );
define("HOST_URL" , "http://example.com/adhoc/");
ini_set('include_path',ini_get('include_path').':'.dirname(__FILE__)."/lib");

define("DB_NAME" , "phpadhoc");
define("DB_HOST" , "localhost");
define("DB_USER" , "username");
define("DB_PASS" , "password");

/**
 * shared connection func
 */
$_conn;
function getConnection(){
    global $_conn;
    if(!$_conn){
        $_conn = new PDO('mysql:host='.DB_HOST.';port=3306;dbname='.DB_NAME, DB_USER, DB_PASS);
        $_conn->setAttribute(PDO::ATTR_PERSISTENT, true);
        $_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $_conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
        $_conn->query('SET CHARACTER SET UTF8');
    }
    return $_conn;
}

?>
