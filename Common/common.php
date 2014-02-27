<?php
//项目公共函数库
//约定：自定义函数为避免与框架和php自带函数命名冲突，统一采用驼峰式命名，在函数名前面加上 We，如WeParseUrl , WeRedirect -- 2013/11/25 成
/**
深度过滤函数，默认htmlspecialchars无法处理多维数组 by 成
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
获取当前时间格式化字符串
*/
function getDateTime(){
	return date('Y-m-d H:i:s',time());
}

/**
会员卡模块更新会员卡等级
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