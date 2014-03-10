<?php
class CouponAction extends BaseAction{
	function _initialize(){
		parent::_initialize();
		//$this->error('该功能重构中...');
	}
	public function index(){
		$this->display();
	}
	
	//中奖后填写信息
	public function add(){
		 if($_POST['action'] ==  'add' ){
			$lid 				= $this->_post('lid');
			$wechaid 			= $this->_post('wechaid');
			$data['sn']			= $this->_post('sncode');
			$data['phone'] 		= $this->_post('tel');
			$data['prize']		= $this->_post('winprize');
			$data['wecha_name'] = $this->_post('wxname');
			$data['time']		= time(); 
			$data['islottery']	= 1;
			$data['usenums']	= 1;			

			$rollback = M('Lottery_record')->where(array('lid'=> $lid,
				'wecha_id'=>$wechaid))->save($data);
			
			echo'{"success":1,"msg":"恭喜！尊敬的<font color=red>'.$data['wecha_name'].'</font>请您保持手机通畅！你的领奖序号:<font color=red>'.$data['sn'].'</font>"}';
			exit;	
		}
	}
}
	
?>
