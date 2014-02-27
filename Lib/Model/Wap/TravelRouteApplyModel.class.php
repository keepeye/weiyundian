<?php
class TravelRouteApplyModel extends Model {
	protected $_auto = array(
		array('addtime','time',1,'function'),//提交订单时自动记录时间
	);

}