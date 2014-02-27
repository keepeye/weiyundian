<?php
/*
微汽车
by 成先兆
*/
class CarAction extends UserAction {
	function __construct(){
		parent::__construct();
		$this->token = session('token');
		if(!$this->token){
			$this->error('非法访问');
		}
		$this->assign("token",$this->token);
	}
	//汽车品牌列表
	function brandList(){
		$list = M('CarBrand')->where(array('token'=>$this->token))->order('sort ASC')->select();
		$this->assign('list',$list);
		$this->display();
	}
	//添加、修改汽车品牌
	function brandSet(){
		if(!IS_POST){
			$id = $this->_get('id');
			if(!empty($id)){
				$info = M('CarBrand')->where(array('id'=>$id,'token'=>$this->token))->find();
				if(empty($info)){
					$this->error('该品牌不存在');
				}
				$this->assign('info',$info);
			}
			$this->display();
		}else{
			$id = $this->_post('id');
			$M = M('CarBrand');
			//设置自动验证
			$validate = array(
				array('name','require','品牌名必须！'), // 仅仅需要进行验证码的验证
				array('www','url','请填写正确的官网网址')
			);
			$M->setProperty("_validate",$validate);
			//设置自动完成
			$auto = array ( 
				array('addtime','time',1,'function'), // 对password字段在新增的时候使md5函数处理
				array('token',$this->token,1),//商户token
			);
			$M-> setProperty("_auto",$auto);

			if($M->create()){
				if($id){
					$re = $M->save();
					$errText = '数据保存失败';
				}else{
					$re = $M->add();
					$errText = '数据添加失败';
				}
				if($re !== false){
					$this->success('ok',U('brandList'));
				}else{
					$this->error($errText);
				}
			}else{
				$this->error($M->getError());
			}
		}
	}
	//删除汽车品牌
	function brandDel(){
		$id = $this->_get('id');
		if(!$id){
			$this->error("未指定id");
		}
		M()->startTrans();//启动事务
		$re = M('CarBrand')->where(array('token'=>$this->token,'id'=>$id))->delete();
		if($re === false){
			M()->rollback();//回滚事务
			$this->error('删除品牌失败，数据库错误');
		}
		//删除车系表关联数据
		$re = M('CarSeries')->where(array('brand_id'=>$id,'token'=>$this->token))->delete();
		if($re === false){
			M()->rollback();//回滚事务
			$this->error('删除关联车系失败，数据库错误');
		}
		M()->commit();//提交事务
		$this->redirect('brandList');
	}
	
	//车系列表
	function seriesList(){
		$M = M('CarSeries');
		$total = $M->where(array('token'=>$this->token))->count();//列表总数
		import('@.ORG.Page');//引入Page类
		$Page = new Page($total,20);
		$pagestr = $Page->show();
		
		$list = $M->alias('a')->field('a.*,b.name as brand_name')->join("__CAR_BRAND__ b on a.brand_id=b.id")->where(array('a.token'=>$this->token))->order('a.sort ASC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('pagestr',$pagestr);
		$this->assign('list',$list);
		$this->display();
	}
	//添加,修改车系
	function seriesSet(){
		
		if(!IS_POST){
			//读取品牌列表
			$brandList = M('CarBrand')->where(array('token'=>$this->token))->select();
			if(empty($brandList)){
				$this->error('您尚未添加汽车品牌,请先添加品牌。');
			}
			$this->assign('brandList',$brandList);
			
			$id = $this->_get('id');
			if(!empty($id)){
				$info = M('CarSeries')->where(array('id'=>$id,'token'=>$this->token))->find();
				if(empty($info)){
					$this->error('该品牌不存在');
				}
				$this->assign('info',$info);
			}
			$this->display();
		}else{
			$id = $this->_post('id');
			//验证品牌是否合法
			$brand_id = $this->_post('brand_id');
			$brand = M('CarBrand')->field('id')->where(array('token'=>$this->token,'id'=>$brand_id))->find();
			if(empty($brand)){
				$this->error("汽车品牌非法");
			}
			
			
			$M = M('CarSeries');
			//设置自动验证
			$validate = array(
				array('name','require','车系名必须！'), // 仅仅需要进行验证码的验证
			);
			$M->setProperty("_validate",$validate);
			//设置自动完成
			$auto = array ( 
				array('token',$this->token,1),//商户token
			);
			$M-> setProperty("_auto",$auto);

			if($M->create()){
				if($id){
					$re = $M->save();
					$errText = '数据保存失败';
				}else{
					$re = $M->add();
					$errText = '数据添加失败';
				}
				if($re !== false){
					$this->success('ok',U('seriesList'));
				}else{
					$this->error($errText);
				}
			}else{
				$this->error($M->getError());
			}
		}
	}
	//删除车系
	function seriesDel(){
		$id = $this->_get('id');
		if(!$id){
			$this->error("未指定id");
		}
		M()->startTrans();//启动事务
		$re = M('CarSeries')->where(array('token'=>$this->token,'id'=>$id))->delete();
		if($re === false){
			M()->rollback();//回滚
			$this->error('删除车系失败');
		}
		$re = M('car_version')->where(array('token'=>$this->token,'series_id'=>$id))->delete();
		if($re === false){
			M()->rollback();//回滚
			$this->error('删除相关车型失败');
		}
		M()->commit();//提交事务
		$this->redirect('seriesList');
	}
	//车系列表
	function versionList(){
		$M = M('car_version');
		$total = $M->where(array('token'=>$this->token))->count();//列表总数
		import('@.ORG.Page');//引入Page类
		$Page = new Page($total,20);
		$pagestr = $Page->show();
		
		$list = $M->alias('a')->field('a.*,c.name as brand_name,b.name as series_name')->join("__CAR_SERIES__ b on a.series_id = b.id")->join("__CAR_BRAND__ c on b.brand_id=c.id")->where(array('a.token'=>$this->token))->order('a.id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('pagestr',$pagestr);
		$this->assign('list',$list);
		$this->display();
	}
	//添加、修改车型
	function versionSet(){
		if(!IS_POST){
			//读取车系列表
			$seriesList = M('car_series')->where(array('token'=>$this->token))->select();
			
			$this->assign('seriesList',$seriesList);
			
			//读取相册列表
			$photoList = M('photo')->field("id,title")->where(array('token'=>$this->token))->limit("0,50")->select();
			$this->assign('photoList',$photoList);
			
			$id = $this->_get('id');
			if(!empty($id)){
				$info = M('CarVersion')->where(array('id'=>$id,'token'=>$this->token))->find();
				if(empty($info)){
					$this->error('该型号不存在');
				}
				$this->assign('info',$info);
			}
			$this->display();
		}else{
			$id = $this->_post('id');
			//验证系列是否合法
			$series_id = $this->_post('series_id');
			$series = M('car_series')->field('id')->where(array('token'=>$this->token,'id'=>$series_id))->find();
			if(empty($series)){
				$this->error("汽车系列非法");
			}
			
			//验证相册是否合法
			$photo_id = $this->_post('photo_id');
			if(!empty($photo_id)){
				$photo = M('photo')->field('id')->where(array('token'=>$this->token,'id'=>$photo_id))->find();
				if(!$photo){
					$this->error("指定的相册不存在");
				}
			}
			$M = M('car_version');
			//设置自动验证
			$validate = array(
				array('name','require','车名必须！'), // 仅仅需要进行验证码的验证
			);
			$M->setProperty("_validate",$validate);
			//设置自动完成
			$auto = array ( 
				array('token',$this->token,1),//商户token
			);
			$M-> setProperty("_auto",$auto);

			if($M->create()){
				if($id){
					$re = $M->save();
					$errText = '数据保存失败';
				}else{
					$re = $M->add();
					$errText = '数据添加失败';
				}
				if($re !== false){
					$this->success('ok',U('versionList'));
				}else{
					$this->error($errText);
				}
			}else{
				$this->error($M->getError());
			}
		}
	}
	
	//删除车型
	function versionDel(){
		$id = $this->_get('id');
		if(!$id){
			$this->error("未指定id");
		}
		M()->startTrans();//启动事务
		
		$re = M('car_version')->where(array('token'=>$this->token,'id'=>$id))->delete();
		if($re === false){
			M()->rollback();//回滚
			$this->error('删除车型失败');
		}
		M()->commit();//提交事务
		$this->redirect('versionList');
	}
	
	
	
	//预约服务列表
	function reserveList(){
		$M = M('car_reserve');
		$where = array('token'=>$this->token);
		//若指定类型
		if($pretype = $this->_get('pretype')){
			$where['pretype'] = $pretype;
		}
		//
		$total = $M->where($where)->count();//列表总数
		import('@.ORG.Page');//引入Page类
		$Page = new Page($total,20);
		$pagestr = $Page->show();
		
		$list = $M->where($where)->limit($Page->firstRow.','.$Page->listRows)->order("status ASC,predate ASC")->select();
		$this->assign('pagestr',$pagestr);
		$this->assign('list',$list);
		$this->display();
	}
	//保养服务标记为受理
	function reserveMark(){
		$id = $this->_post('id','intval');//获取申请id
		$where = array(
			'token'=>$this->token,
			'id' => $id
		);
		M('car_reserve')->where($where)->data(array('status'=>'1'))->save();
		$this->success("ok");
	}
	
	//二手车置换申请列表
	function renewList(){
		$M = M('car_renew');
		$total = $M->where(array('token'=>$this->token))->count();//列表总数
		import('@.ORG.Page');//引入Page类
		$Page = new Page($total,20);
		$pagestr = $Page->show();
		
		$list = $M->where(array('token'=>$this->token))->limit($Page->firstRow.','.$Page->listRows)->order("status ASC,id ASC")->select();
		$this->assign('pagestr',$pagestr);
		$this->assign('list',$list);
		$this->display();
	}
	//查看详情
	function renewDetail(){
		$id = $this->_get('id','intval');
		$where = array(
			'token'=>$this->token,
			'id' => $id
		);
		$info = M('car_renew')->where($where)->find();
		if(!$info){
			$this->error('不存在的申请');
		}else{
			$this->assign('info',$info);
			$this->display();
		}
	}
	//置换申请标记为已处理
	function renewMark(){
		$id = $this->_post('id','intval');//获取申请id
		$where = array(
			'token'=>$this->token,
			'id' => $id
		);
		M('car_renew')->where($where)->data(array('status'=>'1'))->save();
		$this->success("ok",U('renewList'));
	}
	//保险申请列表
	function baoxianList(){
		$M = M('car_baoxian');
		$total = $M->where(array('token'=>$this->token))->count();//列表总数
		import('@.ORG.Page');//引入Page类
		$Page = new Page($total,20);
		$pagestr = $Page->show();
		
		$list = $M->where(array('token'=>$this->token))->limit($Page->firstRow.','.$Page->listRows)->order("status ASC,id ASC")->select();
		$this->assign('pagestr',$pagestr);
		$this->assign('list',$list);
		$this->display();
	}
	//查看保险详情
	function baoxianDetail(){
		$id = $this->_get('id','intval');
		$where = array(
			'token'=>$this->token,
			'id' => $id
		);
		$info = M('car_baoxian')->where($where)->find();
		if(!$info){
			$this->error('不存在的申请');
		}else{
			$this->assign('info',$info);
			$this->display();
		}
	}
	//置换保险申请标记为已处理
	function baoxianMark(){
		$id = $this->_post('id','intval');//获取申请id
		$where = array(
			'token'=>$this->token,
			'id' => $id
		);
		M('car_baoxian')->where($where)->data(array('status'=>'1'))->save();
		$this->success("ok",U('baoxianList'));
	}
	
	//汽车手册列表
	function shouceList(){
		$list = M('CarShouce')->where(array('token'=>$this->token))->select();
		$this->assign('list',$list);
		$this->display();
	}
	//添加、修改手册
	function shouceSet(){
		if(!IS_POST){
			$id = $this->_get('id');
			if(!empty($id)){
				$info = M('car_shouce')->where(array('id'=>$id,'token'=>$this->token))->find();
				if(empty($info)){
					$this->error('该文章不存在');
				}
				$this->assign('info',$info);
			}
			$this->display();
		}else{
			$id = $this->_post('id');
			$M = M('car_shouce');
			//设置自动验证
			$validate = array(
				array('title','require','标题不能为空！'), 
				array('content','require','手册内容不能为空！'),
			);
			$M->setProperty("_validate",$validate);
			//设置自动完成
			$auto = array ( 
				array('token',$this->token,1),//商户token
			);
			$M-> setProperty("_auto",$auto);

			if($M->create()){
				if($id){
					$re = $M->save();
					$errText = '数据保存失败';
				}else{
					$re = $M->add();
					$errText = '数据添加失败';
				}
				if($re !== false){
					$this->success('ok',U('shouceList'));
				}else{
					$this->error($errText);
				}
			}else{
				$this->error($M->getError());
			}
		}
	}
	//删除手册文章
	function shouceDel(){
		$id = $this->_get('id');
		if(!$id){
			$this->error("未指定id");
		}
		
		M('car_shouce')->where(array('token'=>$this->token,'id'=>$id))->delete();
		
		$this->redirect('shouceList');
	}
	//设置
	function settings(){
		$token = session('token');
		
		if(!IS_POST){
			
			//回复信息
			$reply = M('ReplyInfo')->where(array('token'=>$token,'infotype'=>'Car'))->find();
			$this->assign('reply',$reply);
			
			$this->display();
		}else{
			
			
			
			//处理自动回复信息
			$reply_data = $_POST['reply'];
			$M1 = M('ReplyInfo');
			if(is_array($reply_data)){
				$re = $M1->field('id')->where("token='{$token}' and infotype='Car'")->find();//先查询是否有记录，用于后面判断是add还是save
				$reply_data['infotype'] = 'Car';//设置回复类型为Car
				$reply_data['keyword'] = '汽车';
				$reply_data['token'] = $token;
				if($re){
					//save
					$M1->where(array('token'=>$token,'infotype'=>'Car'))->data($reply_data)->save();
					$this->success('保存成功');
				}else{
					C('TOKEN_ON',false);
					$_POST = $reply_data;
					$this->all_insert('ReplyInfo','/settings?token='.$token);
				}
			}else{
				$this->success('保存成功');
			}
			
			
		}
	}
	
}