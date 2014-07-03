<?php
class WechaUserModel extends Model {
	/**
	 * 增加、减少用户积分
	 * @param  string  $token  商户token
	 * @param  string  $wecha_id 微信用户唯一标识
	 * @param  integer $num      增加的积分数量，负数为减少
	 * @return bool            
	 */
	function changeScore($token,$wecha_id,$num=0){
		$this->where(array("token"=>$token,"wecha_id"=>$wecha_id));
		if($num < 0){
			$re = $this->setDec("score",abs($num));
		}elseif($num > 0){
			$re = $this->setInc("score",abs($num));
		}else{
			$re = false;
		}
		return $re;
	}
}