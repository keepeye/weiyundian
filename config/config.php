<?php
/**
 *项目公共配置
 *@package PiGCms
 *@author PiGCms
 **/
return array(
	'TMPL_STRIP_SPACE'      => 	false,
	'TMPL_CACHE_ON'=>true,
	//'APP_DEBUG'   =>  true,
	'LOG_RECORD'=>true,
    'LOG_RECORD_LEVEL'       =>  array('EMERG','ALERT','CRIT','ERR','WARN','NOTIC','INFO','DEBUG','SQL'),
	'LOAD_EXT_CONFIG' 		=> 'db,info,email,safe,upfile,cache,route,app,alipay',		
	'APP_AUTOLOAD_PATH'     =>'@.ORG',
	'OUTPUT_ENCODE'         =>  false, 			//页面压缩输出
	'PAGE_NUM'				=> 15,
	/*Cookie配置*/
	'COOKIE_PATH'           => '/',     		// Cookie路径
    'COOKIE_PREFIX'         => '',      		// Cookie前缀 避免冲突
	/*定义模版标签*/
	'TMPL_L_DELIM'   		=>'{pigcms:',			//模板引擎普通标签开始标记
	'TMPL_R_DELIM'			=>'}',				//模板引擎普通标签结束标记
	'baidu_map_api'=>'CB39f8f69f732c5499f4dfb0ca98d07e',
);
