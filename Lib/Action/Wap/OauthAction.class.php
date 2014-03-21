<?php
/*
微信高级接口之网页授权
 */
class OauthAction extends Action{
	//第一步，获取用户code
	function getCode(){

		$token = I('token');//商户token
		$referer = $_SERVER['HTTP_REFERER'];//来路页面
		cookie("auth_referer",$referer,300);//用cookie临时存储来路url

		$appsec = M('DiymenSet')->where(array("token"=>$token))->find();
		$redirect_uri = C('site_url').'/'.U("Wap/Oauth/auth");//回调页面

		$redirect_uri = rawurlencode($redirect_uri);//urlencode处理
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appsec['appid']}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_base&state=123#wechat_redirect";
		redirect($url);
	}

	//第一步的回调地址，从这里使用code获取access，进而登录用户信息
	function auth(){
		$code = I('code');
		dump(cookie("auth_referer"));
	}
}