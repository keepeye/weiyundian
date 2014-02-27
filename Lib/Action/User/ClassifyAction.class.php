<?php
/**
 *语音回复
**/
class ClassifyAction extends UserAction{
	function __construct(){
		parent::__construct();
		$token = session('token');
		//获取公司信息
		$company = M('Company')->where(array('token'=>$token))->find();
		$this->assign("company",$company);
		//dump($company);
	}
	
	public function index(){
		$db=D('Classify');
		$where['token']=session('token');
		$count=$db->where($where)->count();
		$page=new Page($count,25);
		$info=$db->where($where)->order('sorts desc')->limit($page->firstRow.','.$page->listRows)->select();
		$this->assign('page',$page->show());
		$this->assign('info',$info);
		$this->display();
	}
	
	public function add(){
		$this->display();
	}
	
	public function edit(){
		$id=$this->_get('id','intval');
		$info=M('Classify')->find($id);
		$this->assign('info',$info);
		$this->display();
	}
	
	public function del(){
		$where['id']=$this->_get('id','intval');
		$where['uid']=session('uid');
		if(D(MODULE_NAME)->where($where)->delete()){
			$this->success('操作成功',U(MODULE_NAME.'/index'));
		}else{
			$this->error('操作失败',U(MODULE_NAME.'/index'));
		}
	}
	public function insert(){
		//如果url为空，强制将type设置为0
		if(!isset($_POST['url']) || empty($_POST['url'])){
			$_POST['type'] = '0';
		}
		$this->all_insert();
	}
	
	public function upsave(){
		//如果url为空，强制将type设置为0
		if(!isset($_POST['url']) || empty($_POST['url'])){
			$_POST['type'] = '0';
		}
		$this->all_save();
	}
	
	public function itemlist(){
		$type=$this->_get("type")."_list";
		$datas=array();
		$datas=$this->$type();
		if(empty($datas)){
			return $this->ajaxReturn(array("error"=>1,"msg"=>"您尚设置过该模块"));
		}
		return $this->ajaxReturn(array("error"=>0,"msg"=>"","data"=>$datas));
	}
	
	private function news_list(){
		$classid=$this->_get("classid");
		if(!$classid) return array(array("url"=>"/index.php?g=Wap&m=Index&a=lists&token=".session("token"),"name"=>"图文列表"));
		$where['uid']=session('uid');
		$where['token']=session('token');
		$where["classid"]=$classid;
		$db=D('Img');
		$data=$db->where($where)->order('createtime DESC')->limit(10)->select();
		$datas=array(array("url"=>"/index.php?g=Wap&m=Index&a=lists&token=".session("token")."&classid=".$classid,"name"=>"图文列表"));
		foreach ($data as $d){
			array_push($datas,array("url"=>"/index.php?g=Wap&m=Index&a=content&token=".session("token")."&id=".$d["id"],"name"=>$d["title"]));
		}
		return $datas;
	}
	
	private function photos_list(){
		$where['token']=session('token');
		$db=M('Photo');
		$data = $db->where($where)->order('create_time DESC')->limit(10)->select();	
		$datas=array(array("url"=>"/index.php?g=Wap&m=Photo&a=index&token=".session("token"),"name"=>"相册列表"));
		foreach ($data as $d){
			array_push($datas,array("url"=>"/index.php?g=Wap&m=Photo&a=plist&token=".session("token")."&id=".$d["id"],"name"=>$d["title"]));
		}
		return $datas;
	}
	
	private function hosts_list(){
		$where['token']=session('token');
		$db=M('Host');
        $data = $db->where($where)->order('creattime DESC')->limit(10)->select(); 
        
		$datas=array();
		foreach ($data as $d){
			array_push($datas,array("url"=>"/index.php?g=Wap&m=Host&a=index&token=".session("token")."&hid=".$d["id"],"name"=>$d["title"]));
		}
		return $datas;
	}
	
	private function selfforms_list(){
		$where['token']=session('token');
		$db=M('Selfform');
		$data=$db->where($where)->order('time DESC')->limit(10)->select();
		$datas=array();
		foreach ($data as $d){
			array_push($datas,array("url"=>"/index.php?g=Wap&m=Selfform&a=index&token=".session("token")."&id=".$d["id"],"name"=>$d["name"]));
		}
		return $datas;
	}
	
	private function lotterys_list(){
		$where['type']=1;
		$where['token']=session('token');
		$db=M('Lottery');
		$data=$db->where($where)->order('createtime DESC')->limit(10)->select();
		$datas=array();
		foreach ($data as $d){
			array_push($datas,array("url"=>"/index.php?g=Wap&m=Lottery&a=index&token=".session("token")."&id=".$d["id"],"name"=>$d["title"]));
		}
		return $datas;
	}
	
	private function coupons_list(){
		$where['type']=3;
		$where['token']=session('token');
		$db=M('Lottery');
		$data=$db->where($where)->order('createtime DESC')->limit(10)->select();
		$datas=array();
		foreach ($data as $d){
			array_push($datas,array("url"=>"/index.php?g=Wap&m=Coupon&a=index&token=".session("token")."&id=".$d["id"],"name"=>$d["title"]));
		}
		return $datas;
	}
	
	private function guajiangs_list(){
		$where['type']=2;
		$where['token']=session('token');
		$db=M('Lottery');
		$data=$db->where($where)->order('createtime DESC')->limit(10)->select();
		$datas=array();
		foreach ($data as $d){
			array_push($datas,array("url"=>"/index.php?g=Wap&m=Guajiang&a=index&token=".session("token")."&id=".$d["id"],"name"=>$d["title"]));
		}
		return $datas;
	}
	
	private function product_list(){
		$datas=array(
			array("url"=>"/index.php?g=Wap&m=Product&a=products&token=".session("token"),"name"=>"首页"),
			array("url"=>"/index.php?g=Wap&m=Product&a=cats&token=".session("token"),"name"=>"分类页")
		);
		return $datas;
	}
	
	private function mermber_card_list(){
		$datas=array(array("url"=>"/index.php?g=Wap&m=Card&a=get_card&token=".session("token"),"name"=>"会员卡"));
		return $datas;
	}
	
	private function travel_list(){
		$datas=array(array("url"=>"/index.php?g=Wap&m=Travel&a=index&token=".session("token"),"name"=>"微旅行首页"));
		return $datas;
	}
	
	private function house_list(){
		$datas=array(
				array("url"=>"/index.php?g=Wap&m=House&a=index&token=".session("token"),"name"=>"楼盘简介"),
				array("url"=>"/index.php?g=Wap&m=House&a=room&token=".session("token"),"name"=>"户型列表"),
				array("url"=>"/index.php?g=Wap&m=House&a=review&token=".session("token"),"name"=>"房友印象")
		);
		return $datas;
	}
	
	private function car_list(){
		$datas = array(
			array("url"=>"/index.php?g=Wap&m=Car&a=index&token=".session("token"),"name"=>"4S首页")
		);
		return $datas;
	}
}
?>
