<?php
class MemberCardShopModel extends Model{
	protected $_validate = array(
		array('username','require','请填写用户名'),
		array('username','/^[a-zA-Z_0-9]{6,15}$/','用户名长度不合法或含有非法字符'),
		array('username','','用户名已存在',0,'unique'),
		array('password','require','请填写密码'),
		array('shopname','require','请填写店铺名'),
		array('contact_name','require','请填写店铺联系人'),
		array('contact_tel','require','请填写店铺联系电话'),
		array('address','require','请填写店铺地址'),
	);
	protected $_auto = array(
		array('addtime','time',1,'function'),
		array('password','md5',3,'function'),
	);
	
	//检测变量是否为空
	protected function cknull($data){
		return !empty($data);
	}
	
}