<?php
class WechaLogModel extends Model {
	function addLog($token,$wecha_id="",$type="",$content=""){
		$this->data(array(
			"token"=>$token,
			"wecha_id"=>$wecha_id,
			"type"=>$type,
			"content"=>$content,
			"time"=>time()
		))->add();
	}
}