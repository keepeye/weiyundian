<?php
class TravelRouteModel extends Model{
	protected $_validate = array(
		array('title','cknull','请填写线路标题',1,'callback',1),
		array('type','cknull','请选择线路类型',1,'callback',1),
		array('start_city','cknull','请填写出发地点',1,'callback',1),
		array('start_date','cknull','请设置出发日期',1,'callback',1),
		array('actions','cknull','请设置行动类型',1,'callback',1),
	);
	protected $_auto = array(
		array('start_date','serialize',Model::MODEL_BOTH,'function'),
		array('actions','serialize',Model::MODEL_BOTH,'function'),
		array('imgurls','serialize',Model::MODEL_BOTH,'function'),
		array('flag','parseFlag',Model::MODEL_BOTH,'callback'),
	);
	
	//检测变量是否为空
	protected function cknull($data){
		return !empty($data);
	}
	//flag属性处理
	protected function parseFlag($data){
		if(!is_array($data)) return "";
		return implode(",",$data);
	}
}