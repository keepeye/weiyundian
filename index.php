<?php
header("Content-type: text/html; charset=utf-8");
 if (get_magic_quotes_gpc()) {
	function stripslashes_deep($value){
		$value = is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value);
 		return $value;
 	}
  $_POST = array_map('stripslashes_deep', $_POST);
  $_GET = array_map('stripslashes_deep', $_GET);
  $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
 }
if(isset($_GET['debug'])){
	 define('APP_DEBUG',true);
}

 define('APP_NAME', 'viicms');
 define('CONF_PATH','./config/');
 define('RUNTIME_PATH','./logs/');
 define('TMPL_PATH','./tpl/');
 define('APP_PATH','./');
 define('CORE','./Core/');
 require(CORE.'/viicms.php');
