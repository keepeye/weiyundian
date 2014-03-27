<?php
class DiymenAction extends UserAction{
	function __construct(){
		parent::__construct();
		$token = session('token');
		//获取公司信息
		$company = M('Company')->where(array('token'=>$token))->find();
		$this->assign("company",$company);
		//dump($company);
		//keyword表关联模块列表
		$this->assign('kmodules',$this->getKewordModules());
	}
	//自定义菜单配置
	public function index(){
		$data=M('Diymen_set')->where(array('token'=>$_SESSION['token']))->find();
		if(IS_POST){
			$_POST['token']=$_SESSION['token'];
			if($data==false){
				$this->all_insert('Diymen_set');
			}else{
				$_POST['id']=$data['id'];
				$this->all_save('Diymen_set');
			}
		}else{
			$this->assign('diymen',$data);
			$class=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>0))->order('sort ASC')->select();//dump($class);
			foreach($class as $key=>$vo){
				$c=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>$vo['id']))->order('sort ASC')->select();
				$class[$key]['class']=$c;
			}
			$this->assign('class',$class);
			$this->display();
		}
	}


	public function  class_add(){
		if(IS_POST){
			$keyword = $this->_post('keyword');
			$url = $this->_post('url');
			if(!$keyword && !$url){
				$this->error('关键词和外链url至少设置一项');
			}
			
			if(!$this->_post('pid')){
				if(strlen($this->_post('title')) > 12){
					$this->error('一级菜单标题不得超过4个汉字长度');
				}
			}else{
				if(strlen($this->_post('title')) > 21){
					$this->error('二级菜单标题不得超过7个汉字长度');
				}
			}
			$this->all_insert('Diymen_class','/class_add');
		}else{
			$class=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>0))->order('sort desc')->select();
			$this->assign('class',$class);
			//$keywords=M("Keyword")->where(array("token"=>session('token')))->select();
			//$this->assign("keywords",$keywords);
			$this->display();
		}
	}
	public function  class_del(){
		$class=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>$this->_get('id')))->order('sort desc')->find();
		//echo M('Diymen_class')->getLastSql();exit;
		if($class==false){
			$back=M('Diymen_class')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->delete();
			if($back==true){
				$this->success('删除成功');
			}else{
				$this->error('删除失败');
			}
		}else{
			$this->error('请删除该分类下的子分类');
		}


	}
	public function  class_edit(){
		if(IS_POST){
			$_POST['id']=$this->_get('id');
			$keyword = $this->_post('keyword');
			$url = $this->_post('url');
			if(!$keyword && !$url){
				$this->error('关键词和外链url至少设置一项');
			}
			if(!$this->_post('pid')){
				if(strlen($this->_post('title')) > 12){
					$this->error('一级菜单标题不得超过4个汉字长度');
				}
			}else{
				if(strlen($this->_post('title')) > 21){
					$this->error('二级菜单标题不得超过7个汉字长度');
				}
			}
			$this->all_save('Diymen_class','/index');
		}else{
			$data=M('Diymen_class')->where(array('token'=>session('token'),'id'=>$this->_get('id')))->find();
			if($data==false){
				$this->error('您所操作的数据对象不存在！');
			}else{
				$class=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>0))->order('sort desc')->select();//dump($class);
				$this->assign('class',$class);
				$this->assign('show',$data);
				//$keywords=M("Keyword")->where(array("token"=>session('token')))->select();
				//$this->assign("keywords",$keywords);
			}
			$this->display();
		}
	}
	
	public function itemlist(){
		$type=$this->_get("type")."_list";
		$datas=array();
		$datas=$this->$type();
		return $this->ajaxReturn(array("error"=>0,"msg"=>"","data"=>$datas));
	}
	
	private function news_list(){
		$where['uid']=session('uid');
		$where['token']=session('token');
		$db=D('Img');
		$data=$db->where($where)->order('createtime DESC')->limit(10)->select();
		$datas=array();
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
	
	private function travel_list(){
		$datas=array(array("url"=>"/index.php?g=Wap&m=Travel&a=index&token=".session("token"),"name"=>"微旅行首页"));
		return $datas;
	}
	
	private function house_list(){
		$datas=array(
				array("url"=>"/index.php?g=Wap&m=House&a=index&token=".session("token"),"name"=>"楼盘简介"),
				array("url"=>"/index.php?g=Wap&m=House&a=room&token=".session("token"),"name"=>"户型列表")
		);
		return $datas;
	}
	
	public function  class_send(){
		if(IS_GET){
			$api=M('Diymen_set')->where(array('token'=>session('token')))->find();
			//dump($api);
			$url_get='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$api['appid'].'&secret='.$api['appsecret'];
			$json=json_decode($this->curlGet($url_get));

			if($api['appid']==false||$api['appsecret']==false){$this->error('必须先填写【AppId】【 AppSecret】');exit;}
			$data = '{"button":[';

			$class=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>0))->limit(3)->order('sort ASC')->select();//dump($class);
			$kcount=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>0))->limit(3)->count();
			$k=1;
			foreach($class as $key=>$vo){
				//主菜单

				$data.='{"name":"'.$vo['title'].'",';
				$c=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>$vo['id']))->limit(5)->order('sort ASC')->select();
				$count=M('Diymen_class')->where(array('token'=>session('token'),'pid'=>$vo['id']))->limit(5)->count();
				//子菜单
				if($c!=false){
					$data.='"sub_button":[';
				}else{
					$temp_url=htmlspecialchars_decode($vo['url']);
					if($temp_url){
						if(strpos($temp_url,"http://") !== 0 && strpos($temp_url,"tel:") !== 0){
							$temp_url = C('site_url').'/'.ltrim($temp_url,'/');
						}
					}
					if($vo['url']){ 
						$data.='"type":"view","name":"'.$vo['title'].'","url":"'.$temp_url.'"';
					}else{
						$data.='"type":"click","name":"'.$vo['title'].'","key":"'.$vo['keyword'].'"';
					}
					//$data.='"type":"click","key":"'.$vo['title'].'"';
				}
				$i=1;
				foreach($c as $voo){
					$temp_url=htmlspecialchars_decode($voo['url']);
					if($temp_url){
						if(strpos($temp_url,"http://") !== 0 && strpos($temp_url,"tel:") !== 0){
							$temp_url = C('site_url').'/'.ltrim($temp_url,'/');
						}
					}
					if($i==$count){
						if($voo['url']){ 
							$data.='{"type":"view","name":"'.$voo['title'].'","url":"'.$temp_url.'"}';
						}else{
							$data.='{"type":"click","name":"'.$voo['title'].'","key":"'.$voo['keyword'].'"}';
						}
					}else{
						if($voo['url']){
							$data.='{"type":"view","name":"'.$voo['title'].'","url":"'.$temp_url.'"},';
						}else{
							$data.='{"type":"click","name":"'.$voo['title'].'","key":"'.$voo['keyword'].'"},';
						}
					}
					$i++;
				}
				if($c!=false){
					$data.=']';
				}

				if($k==$kcount){
					$data.='}';
				}else{
					$data.='},';
				}
				$k++;
			}
			$data.=']}';

			file_get_contents('https://api.weixin.qq.com/cgi-bin/menu/delete?access_token='.$json->access_token);

			$url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$json->access_token;
			$re = $this->api_notice_increment($url,$data);
			if($re!="ok"){
				$this->error('操作失败:'.$re);
			}else{
				$this->success('操作成功');
			}
			exit;
		}else{
			$this->error('非法操作');
		}
	}
	function api_notice_increment($url, $data){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$tmpInfo = curl_exec($ch);
		
		if (curl_errno($ch)) {
			return "curl请求失败";
		}else{
			$re=json_decode($tmpInfo);
			if(isset($re['errmsg'])){
				return $re['errmsg'];
			}
			return "ok";
		}
	}
	function curlGet($url){
		$ch = curl_init();
		$header = "Accept-Charset: utf-8";
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$temp = curl_exec($ch);
		return $temp;
	}
	
	//按模块拉取关键词列表
	function ajaxKeywordList(){
		$module = $_POST['mname'];//获取模块关键词
		$list = M('Keyword')->field('keyword')->where(array('module'=>$module,'token'=>session('token')))->group('keyword')->select();
		if($list !== false){
			$this->ajaxReturn(array('status'=>'1','data'=>$list));
		}else{
			$this->error("数据读取错误");
		}
	}

}
	?>
