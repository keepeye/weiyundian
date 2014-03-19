<?php
class CouponAction extends UserAction{
	
	function _initialize(){
		parent::_initialize();
		
		
	}
	//优惠券活动列表
	public function index(){
		//读取活动列表
		$list=M('Coupon')->where(array('token'=>session('token')))->select();
		$this->assign('list',$list);
		$this->display();
	}

	//添加修改活动信息
	public function set(){
		$CouponM = D('Coupon');//实例化模型
		$id = I('request.id','0','intval');//获取主键id
		$isNew = $id > 0?false:true;//判断是否添加操作
		if(!$isNew){
			$coupon = $CouponM->where(array('id'=>$id,'token'=>$this->token))->find();//更新模式下检查活动是否存在
			if(!$coupon){
				$this->error("活动不存在");
			}
			$coupon['keyword'] = M('Keyword')->where(array("pid"=>$id,"module"=>"Coupon","token"=>$this->token))->getField("keyword");//获取关键词
			
		}
		//判断是否提交表单
		if(!IS_POST){
			$this->assign("coupon",$coupon);
			$this->display();//显示视图
		}else{
			unset($_POST['given_num']);//禁止修改已派发优惠券数量
			$_POST['token'] = $this->token;//将token添加到表单数据中，避免丢失
			$_POST['id'] = $id;//同上
			if($CouponM->create()){
				$re = $isNew?$CouponM->add():$CouponM->save();
				if($re !== false){
					$pid = $isNew?$re:$id;//获取主键
					if($pid > 0){
						//更新关键词
						D('Keyword')->setKeyword(I("post.keyword"),$pid,$this->token,"Coupon",1);
					}
					$this->success("保存成功");
				}else{
					$this->error("保存数据失败:".$CouponM->getLastSql());
				}
			}else{
				$this->error($CouponM->getError());
			}
		}


	}

	//设置活动状态，开启/关闭
	function setStatus(){
		$status = I('status','0');//获取状态码
		if($status != 0) $status='1';
		$id = I('id',0);//活动id
		M('Coupon')->where(array('token'=>$this->token,'id'=>$id))->data(array('status'=>$status))->save();
		$this->success('设置完成');
	}

	//删除活动
	function del(){
		$id = I('id',0,'intval');
		if(!$id){
			$this->error('非法id');
		}
		//删除coupon记录
		if(M('Coupon')->where(array('id'=>$id,'token'=>$this->token))->delete()){
			//删除关键词记录
			D('Keyword')->deleteRecord($id,$this->token,"Coupon");
			//删除cupon_record记录
			M('CouponRecord')->where(array("pid"=>$id))->delete();
		}
		$this->success('操作完成');
	}
	
	//发放记录
	function recordList(){
		$pid = I('pid',0,'intval');//活动id
		//检测活动是否属于当前登录商户
		$coupon = M('Coupon')->where(array('token'=>$this->token,'id'=>$pid))->find();
		if(!$coupon){
			$this->error('活动不存在');
		}
		//读取记录列表
		$map = array(
			'pid'=>$pid
			);
		$recordcount=M('CouponRecord')->where($map)->count();//中奖总数
		//分页
		$count      = $recordcount;
		$Page       = new Page($count,20);
		$pagestr       = $Page->show();
		$record=M('CouponRecord')->where($map)->order('`time` desc')->limit($Page->firstRow.','.$Page->listRows)->select();//中奖列表
		$this->assign('pagestr',$pagestr);

		$this->assign("coupon",$coupon);
		$this->assign("record",$record);
		$this->display();
	}
	//使用优惠券
	function useCoupon(){
		$id = I('id',0,'intval');//coupon_record主键
		$record = M('CouponRecord')->where(array('id'=>$id))->find();//获取记录信息
		if(!$record){
			$this->error('记录不存在');
		}
		$coupon = M('Coupon')->field('id')->where(array("token"=>$this->token,"id"=>$record['pid']))->find();
		if(!$coupon){
			$this->error('该记录所属活动不存在');
		}
		
		//更新记录状态
		M('CouponRecord')->where(array('id'=>$id))->data(array("used_time"=>time()))->save();
		$this->success("操作完成");
	}

	//删除指定优惠券记录
	function delRecord(){
		$id = I('id',0,'intval');//coupon_record主键
		$record = M('CouponRecord')->where(array('id'=>$id))->find();//获取记录信息
		if(!$record){
			$this->error('记录不存在');
		}
		$coupon = M('Coupon')->field('id')->where(array("token"=>$this->token,"id"=>$record['pid']))->find();
		if(!$coupon){
			$this->error('该记录所属活动不存在');
		}
		//更新活动given_num
		M('Coupon')->where(array("token"=>$this->token,"id"=>$record['pid']))->setDec("given_num");
		//删除记录
		M('CouponRecord')->where(array('id'=>$id))->delete();
		$this->success("操作完成");
	}
}	
