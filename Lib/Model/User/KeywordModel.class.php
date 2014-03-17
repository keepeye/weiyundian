<?php
class KeywordModel extends Model{
	/**
	 * 自动设置图文关键词
	 * @param mixed $keyword 关键词列表，单个字符串或者数组
	 * @param int $pid 关联模型表的主键值
	 * @param string $token   商户微信token
	 * @param string $module  模型名
	 * @param  int $len 限制关键词数量
	 */
	function setKeyword($keyword,$pid,$token,$module,$len=3){
		//先删除相关记录
		$this->deleteRecord($pid,$token,$module);
		//重新添加
		if(is_string($keyword)){
			$this->addKeyword($keyword,$pid,$token,$module);
		}else{
			//如果传入的关键词变量是一个数组，则先截取长度再遍历add
			foreach(array_slice($keyword,0,$len) as $item){
				$this->addKeyword($item,$pid,$token,$module);
			}
		}
		return true;
	}

	function addKeyword($keyword,$pid,$token,$module){
		$data = array(
				"keyword"=>$keyword,
				"pid"=>$pid,
				"token"=>$token,
				"module"=>$module
			);
		$re = $this->add($data);
		if($re === false){
			Log::write($this->getDbError());//如果插入失败，则记录到日志
		}
		return true;
	}
	/**
	 * 删除指定的关键词记录
	 * @param  integer $pid    文档主键id
	 * @param  integer $token  商户token
	 * @param  string  $module 指定的模型
	 * @param  string  $keyword 指定的关键词
	 * @return [type]          [description]
	 */
	function deleteRecord($pid=0,$token=0,$module="",$keyword=""){
		if(!$pid || !$token){
			return false;
		}
		$map = array(
			"token"=>$token,
			"pid"=>$pid
			);
		if(!empty($module)){
			$map['module'] = $module;
		}
		if(!empty($keyword)){
			$map['keyword'] = $keyword;
		}

		$this->where($map)->delete();
	}
}