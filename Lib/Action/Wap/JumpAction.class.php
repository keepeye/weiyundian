<?php
class JumpAction extends Action{
	//跳转到应用页面，将openid写入到cookie避免用户转发时链接中带有openid
	function jumpto(){
		$appurl = $_GET['appurl'];
		$openid = $_GET['openid'];//微信用户唯一id
		cookie("openid",$openid);
		cookie("wecha_id",$wecha_id);
		redirect($appurl);
	}
}