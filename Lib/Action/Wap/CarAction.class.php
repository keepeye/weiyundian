<?php
class CarAction extends BaseAction {
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
	//wap首页
	function index(){
		$this->display();
	}
	
	//品牌列表
	function brandList(){
		$list = M('car_brand')->where(array('token'=>$this->token))->order("sort ASC")->select();//读取列表
		$this->assign('list',$list);
		$this->display();
	}
	
	//系列列表
	function seriesList(){
		$brand_id = $this->_get('bid','intval');//品牌id
		$brandInfo = $this->_getBrandById($brand_id);//品牌信息
		if(!$brandInfo){
			$this->error('品牌不存在');
		}
		$this->assign('brandInfo',$brandInfo);
		$where = array(
			'token'=>$this->token,
			'brand_id'=>$brand_id,
		);
		$list = M('car_series')->where($where)->order("sort ASC")->select();//读取列表
		$this->assign('list',$list);
		$this->display();
	}
	//型号列表
	function versionList(){
		$series_id = $this->_get('sid','intval');//系列id
		$seriesInfo = $this->_getSeriesById($series_id);//系列信息
		if(!$seriesInfo){
			$this->error('系列不存在');
		}
		$this->assign('seriesInfo',$seriesInfo);
		$brandInfo = $this->_getBrandById($seriesInfo['brand_id']);//品牌信息
		if(!$brandInfo){
			$this->error('品牌不存在');
		}
		$this->assign('brandInfo',$brandInfo);
		$where = array(
			'token'=>$this->token,
			'series_id'=>$series_id,
		);
		$list = M('car_version')->field('id,name,litpic,price')->where($where)->select();//读取列表
		$this->assign('list',$list);
		$this->display();
	}
	//型号详细
	function versionDetail(){
		$id = $this->_get('id','intval');//型号id
		if(!$id){
			$this->error('非法访问');
		}
		$info = $this->_getVersionById($id);
		if(empty($info)){
			$this->error('型号不存在');
		}
		//相册
		if(!empty($info['photo_id'])){
			$info['photo_url'] = U('Photo/plist',array('token'=>$this->token,'id'=>$info['photo_id']));
		}
		
		$this->assign('info',$info);
		$this->display();
	}
	
	//预约服务
	function reserve(){
		if(!IS_POST){
			//读取车型
			$where = array(
				'a.token' => $this->token
			);
			$versionList = M('car_version')->field("a.name as version_name,b.name as series_name,c.name as brand_name")->alias('a')->join("__CAR_SERIES__ AS b ON b.id = a.series_id AND b.token = a.token")->join("__CAR_BRAND__ AS c ON c.id = b.brand_id AND c.token = a.token")->where($where)->select();
			
			$this->assign('versionList',$versionList);
			$this->display();
		}else{
			$M = M('car_reserve');
			if($M->create()){
				if($M->add()){
					$this->success("您的申请已收到，请等待工作人员电话联系");
				}else{
					$this->error("数据库添加记录错误");
				}
			}else{
				$this->error("数据创建错误");
			}
		}
	
	}
	
	//二手置换
	function renew(){
		if(!IS_POST){
			$this->display();
		}else{
			$M = M('car_renew');
			if($M->create()){
				if($M->add()){
					$this->success("您的申请已收到，请等待工作人员电话联系");
				}else{
					$this->error("数据库添加记录错误");
				}
			}else{
				$this->error("数据创建错误");
			}
		}
	}
	
	//保险
	function baoxian(){
		if(!IS_POST){
			$this->display();
		}else{
			$M = M('car_baoxian');
			$nowdate = date('Y-m-d H:i:s',time());
			$_auto = array(
				array('adddate',$nowdate)
			);
			$M->setProperty('_auto',$_auto);
			if($M->create()){
				if($M->add()){
					$this->success("您的申请已收到，请等待工作人员电话联系");
				}else{
					$this->error("数据库添加记录错误");
				}
			}else{
				$this->error("数据创建错误");
			}
		}
	}
	
	//手册列表
	function shouceList(){
		$where = array(
			'token'=>$this->token,
		);
		$list = M('car_shouce')->where($where)->select();//读取列表
		$this->assign('list',$list);
		$this->display();
	}
	//手册详细
	function shouceDetail(){
		$id = $this->_get('id','intval');//型号id
		if(!$id){
			$this->error('非法访问');
		}
		$info = M('car_shouce')->where(array('token'=>$this->token,'id'=>$id))->find();
		if(empty($info)){
			$this->error('手册不存在');
		}
		
		$this->assign('info',$info);
		$this->display();
	}
	
	function daohang(){
		$company = $this->company;
		if(!$company['latitude'] && !$company['longitude']){
			$this->error('商家未设置地图标记,无法导航');
		}
		$url = "http://api.map.baidu.com/marker?location=".$company['latitude'].",".$company['longitude']."&title=".rawurlencode($company['name'])."&content=".rawurlencode($company['address'])."&output=html";
		redirect($url);
	}
	//根据id获取品牌信息
	private function _getBrandById($id){
		return M('car_brand')->where(array('token'=>$this->token,'id'=>$id))->find();
	}
	
	//根据ID获取系列信息
	private function _getSeriesById($id){
		return M('car_series')->where(array('token'=>$this->token,'id'=>$id))->find();
	}
	
	private function _getVersionById($id){
		return M('car_version')->field("a.*,b.name as seriesName,b.motor,b.gearbox,b.level")->alias("a")->join("__CAR_SERIES__ as b on b.id = a.series_id")->where(array('a.token'=>$this->token,'a.id'=>$id))->find();
	}
}