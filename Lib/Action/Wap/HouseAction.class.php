<?php
class HouseAction extends BaseAction{
	public $contact;
	public $token;
	public $wecha_id;
	function __construct(){
		parent::__construct();
		
		$this->token = $_GET['token']?$_GET['token']:$_POST['token'];
		if(!isset($this->token)){
			$this->error('TOKEN非法');
		}
		$this->assign('token',$this->token);
		$this->wecha_id	= $_GET['wecha_id']?$_GET['wecha_id']:$_POST['wecha_id'];
		if (!$this->wecha_id){
			$this->wecha_id='';
		}
		$this->assign('wecha_id',$this->wecha_id);
		//公司信息
		$company_db=M('company');
		$this->company=$company_db->where(array('token'=>$this->token,'isbranch'=>0))->find();
		$this->assign('company',$this->company);
	}
	//楼盘设置
	public function index(){
	$token      = $this->_get('token'); 
		$house=M('House')->where(array('token'=>$token))->find();
	
			$this->assign('house',$house);
			$this->display();
		
	}
	//户型设置
    public function room(){
	    $token = $this->_get('token'); 
		$house=M('House')->where(array('token'=>$token))->find();
        $db=D('HouseRoom');
		$where['token']= $token;
		$rooms=$db->where($where)->order('`order` desc')->select();//读取所有户型
		$cateList = M('house_category')->field('id,name')->where(array('token'=>$token))->select();//读取所有子楼盘分类
		$roomList = array();
		foreach($rooms as $v){
			$roomList[$v['category_id']]['roomList'][] = $v;
		}
		foreach($cateList as $vv){
			$roomList[$vv['id']]['catename'] = $vv['name'];
		}
		
		$this->assign('house',$house);
		$this->assign('token',$token);
		$this->assign('roomList',$roomList);
		$this->display();
    }   
    public function room360(){

	    $token = $this->_get('token'); 
	    $room_id = $this->_get('id'); 
		$this->assign('token',$token);
		$this->assign('id',$room_id);
		$this->display();
    }
     public function room360_data(){
	    $room_id = $this->_get('id'); 
		$plist=M('house_room360')->where(array('room_id'=>$room_id))->select();
		$this->assign('plist',$plist);
		$this->display('room360_data', 'utf-8', 'text/xml');
    }
	//印象点评
	public function review(){
		$this->display();
	}
	//印象json
	function impressJSON(){
		$list = M('house_impress')->where(array('token'=>$this->token,'display'=>'1'))->select();
		$data = array();
		$sum = 0;
		foreach($list as $v){
			$data['top'][]=array(
				'content'=>$v['title'],
				'count'=>$v['comment'],
				'id'=>$v['id']
			);
			$sum+=$v['comment'];
		}
		$data['sum']=$sum;
		$data['msg']='ok';
		$data['ret']=0;
		$data['user']=array('content'=>"","count"=>0,"id"=>0);
		$jsonData = json_encode($data);
		echo "reviewResult(".$jsonData.");";
	}
	//点评json
	function dianpingJSON(){
		$list = M('house_dianping')->where(array('token'=>$this->token))->select();
		$data = array();
		foreach($list as $v){
			$data[]=array(
				"name" => $v['name'],
				"title" => $v['position'],
				"photo" => $v['pic'],
				"intro" => $v['intro'],
				"reviewTitle"=>$v['title'],
				'reviewDesc'=>$v['dianping']
			);
		}
		$dataJSON = "renderProList(".json_encode($data).");";
		echo $dataJSON;
	
	}
}

