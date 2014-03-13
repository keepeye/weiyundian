<?php
//项目公共函数库
//约定：自定义函数为避免与框架和php自带函数命名冲突，统一采用驼峰式命名，在函数名前面加上 We，如WeParseUrl , WeRedirect -- 2013/11/25 成
/**
*深度过滤函数，默认htmlspecialchars无法处理多维数组 by 成
*/
function htmlspecialchars_deep($value){
	$value = is_array($value)?array_map('htmlspecialchars_deep',$value):htmlspecialchars($value);
	return $value;
}

/**
* wap首页分类url解析
* @param $url classify表中的url字段值
* @param $type classify表中的type字段值
* @param $params href中的附加参数，用于附加token，wecha_id等参数
* @return string
* @date 2013/11/25
* @author 成
*/
function WeParseUrl($url,$type,$params=array()){
	$href = "";//初始化href字符串
	if(!isset($params['wxref'])){
		$params['wxref'] = 'mp.weixin.qq.com';
	}
	switch($type){
		
		//站内链接
		case '1' :
			$href = U(htmlspecialchars_decode($url),$params);
			break;
		//远程链接
		case '2' : 
			$href = $url;
			break;
		//电话号码
		case '3':
			$href = "tel:".$url;
			break;
		//0表示空链接，直接转向lists动作
		case '0':
		default:
			$href = U('Wap/Index/lists',$params);
			break;
	}
	return $href;
}

/**
*获取当前时间格式化字符串
*/
function getDateTime(){
	return date('Y-m-d H:i:s',time());
}

/**
*会员卡模块更新会员卡等级
*/
function member_upviplevel($token,$wecha_id){
	$set_exchange = M('Member_card_exchange')->field('silver_card_score,gold_card_score,diamond_card_score')->where(array('token'=>$token))->find();//商家积分设置
	if(!$set_exchange){
		return false;
	}
	$get_card=M('member_card_create')->field('card_type')->where(array('wecha_id'=>$wecha_id,'token'=>$token))->find();//用户会员卡信息
	if(!$get_card) return false;
	$da = M('Userinfo')->field('total_score')->where(array('token'=>$token,'wecha_id'=>$wecha_id))->find();//用户信息
	if(!$da) return false;
	$card_type = 0;
	if($da['total_score'] >= $set_exchange["silver_card_score"])
	{
		$card_type = 1;
	}
	if($da['total_score'] >= $set_exchange["gold_card_score"]) 
	{
		$card_type = 2;
	}
	if($da['total_score'] >= $set_exchange["diamond_card_score"])
	{
		$card_type = 3;
	}
	if($get_card['card_type'] < $card_type){
		$mdata['card_type'] = $card_type;
		M('member_card_create')->where(array('wecha_id'=>$wecha_id,'token'=>$token))->save($mdata);
	}
}
/**
 * 获取输入参数 支持过滤和默认值
 * 使用方法:
 * <code>
 * I('id',0); 获取id参数 自动判断get或者post
 * I('post.name','','htmlspecialchars'); 获取$_POST['name']
 * I('get.'); 获取$_GET
 * </code>
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
 * @return mixed
 */
function I($name,$default='',$filter=null) {
    if(strpos($name,'.')) { // 指定参数来源
        list($method,$name) =   explode('.',$name,2);
    }else{ // 默认为自动判断
        $method =   'param';
    }
    switch(strtolower($method)) {
        case 'get'     :   $input =& $_GET;break;
        case 'post'    :   $input =& $_POST;break;
        case 'put'     :   parse_str(file_get_contents('php://input'), $input);break;
        case 'param'   :
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input  =  $_POST;
                    break;
                case 'PUT':
                    parse_str(file_get_contents('php://input'), $input);
                    break;
                default:
                    $input  =  $_GET;
            }
            break;
        case 'request' :   $input =& $_REQUEST;   break;
        case 'session' :   $input =& $_SESSION;   break;
        case 'cookie'  :   $input =& $_COOKIE;    break;
        case 'server'  :   $input =& $_SERVER;    break;
        case 'globals' :   $input =& $GLOBALS;    break;
        default:
            return NULL;
    }
    if(empty($name)) { // 获取全部变量
        $data       =   $input;
        array_walk_recursive($data,'filter_exp');
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
        if($filters) {
            $filters    =   explode(',',$filters);
            foreach($filters as $filter){
                $data   =   array_map_recursive($filter,$data); // 参数过滤
            }
        }
    }elseif(isset($input[$name])) { // 取值操作
        $data       =   $input[$name];
        is_array($data) && array_walk_recursive($data,'filter_exp');
        $filters    =   isset($filter)?$filter:C('DEFAULT_FILTER');
        if($filters) {
            $filters    =   explode(',',$filters);
            foreach($filters as $filter){
                if(function_exists($filter)) {
                    $data   =   is_array($data)?array_map_recursive($filter,$data):$filter($data); // 参数过滤
                }else{
                    $data   =   filter_var($data,is_int($filter)?$filter:filter_id($filter));
                    if(false === $data) {
                        return   isset($default)?$default:NULL;
                    }
                }
            }
        }
    }else{ // 变量默认值
        $data       =    isset($default)?$default:NULL;
    }
    return $data;
}


if(!function_exists("array_column")){
    /**
     * 返回数组的列
     * @param  array  $input     需要取出数组列的多维数组（或结果集）
     * @param  [type] $columnKey 需要返回值的列，它可以是索引数组的列索引，或者是关联数组的列的键
     * @param  [type] $indexKey  作为返回数组的索引/键的列，它可以是该列的整数索引，或者字符串键值。
     * @return [type]            [description]
     */
    function array_column(array $input, $columnKey, $indexKey = null) {
        $result = array();
    
        if (null === $indexKey) {
            if (null === $columnKey) {
                // trigger_error('What are you doing? Use array_values() instead!', E_USER_NOTICE);
                $result = array_values($input);
            }
            else {
                foreach ($input as $row) {
                    $result[] = $row[$columnKey];
                }
            }
        }
        else {
            if (null === $columnKey) {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row;
                }
            }
            else {
                foreach ($input as $row) {
                    $result[$row[$indexKey]] = $row[$columnKey];
                }
            }
        }
    
        return $result;
    }
}