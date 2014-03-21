<?php
/*
微信高级接口之网页授权
 */
class OauthAction extends Action{
	//第一步，获取用户code
	function getCode(){

		$token = I('token');//商户token
		$referer = I('referer','','htmlspecialchars_decode');//来路
		
		cookie("referer",$referer,300);//用cookie临时存储来路url

		$appsec = M('DiymenSet')->where(array("token"=>$token))->find();

		cookie("appid",$appsec['appid'],300);
		cookie("secret",$appsec['appsecret'],300);

		$redirect_uri = C('site_url').'/'.U("Wap/Oauth/auth");//回调页面

		$redirect_uri = rawurlencode($redirect_uri);//urlencode处理
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appsec['appid']}&redirect_uri={$redirect_uri}&response_type=code&scope=snsapi_userinfo&state=123#wechat_redirect";
		redirect($url);
	}

	//第一步的回调地址，从这里使用code获取access，进而登录用户信息
	function auth(){
		$code = I('code');
		$appid = cookie("appid");
		$secret = cookie("secret");
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$appid}&secret={$secret}&code={$code}&grant_type=authorization_code";
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$data =  curl_exec($ch);
		curl_close($ch);
		$data = json_decode($data,true);
		session("wecha_id",$data['openid'],$data['expires_in']);//写入用户session
		$referer = cookie("referer");
		$this->clearCookie();
		//redirect($referer);
	}

	//清空临时cookie
	function clearCookie(){
		cookie('referer',null);
		cookie('appid',null);
		cookie('secret',null);
	}
}