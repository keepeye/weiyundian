<?php
class ImageModel extends Model{
    protected $_auto = array ( 
    	array("userid","get_userid",self::MODEL_BOTH,"callback"),
        array('uploadtime','time',self::MODEL_BOTH,'function'), // 对create_time字段在更新的时候写入当前时间戳
    );
    
    /**
     * 自动表单处理
     * @access public
     * @param array $data 创建数据
     * @param string $type 创建类型
     * @return mixed
     */
    private function autoOperation(&$data,$type) {
    	if(!empty($this->_auto)){
    		$_auto   =   $this->_auto;
    	}
    	// 自动填充
    	if(isset($_auto)) {
    		foreach ($_auto as $auto){
    			// 填充因子定义格式
    			// array('field','填充内容','填充条件','附加规则',[额外参数])
    			if(empty($auto[2])) $auto[2] = self::MODEL_INSERT; // 默认为新增的时候自动填充
    			if( $type == $auto[2] || $auto[2] == self::MODEL_BOTH) {
    				switch(trim($auto[3])) {
    					case 'function':    //  使用函数进行填充 字段的值作为参数
    					case 'callback': // 使用回调方法
    						$args = isset($auto[4])?(array)$auto[4]:array();
    						if(isset($data[$auto[0]])) {
    							array_unshift($args,$data[$auto[0]]);
    						}
    						if('function'==$auto[3]) {
    							$data[$auto[0]]  = call_user_func_array($auto[1], $args);
    						}else{
    							$data[$auto[0]]  =  call_user_func_array(array(&$this,$auto[1]), $args);
    						}
    						break;
    					case 'field':    // 用其它字段的值进行填充
    						$data[$auto[0]] = $data[$auto[1]];
    						break;
    					case 'string':
    					default: // 默认作为字符串填充
    						$data[$auto[0]] = $auto[1];
    				}
    				if(false === $data[$auto[0]] )   unset($data[$auto[0]]);
    			}
    		}
    	}
    	return $data;
    }
    
    public function add($data='',$options=array(),$replace=false){
    	$data=$this->autoOperation($data,1);
    	parent::add($data);
    }
    
    public function get_userid(){
    	return session('uid');
    }
}

?>