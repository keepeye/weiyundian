<?php
class ImageAction extends Action{
	private $allow_max=2024;
	private $allow_exts=array('jpeg','jpg','png');
	private $save_path="./uploads/";
	protected function _initialize(){
    	$this->upload_init();
    }

    private function upload_init()
    {
        $allow_max  = C('up_size');
        $allow_exts = explode(',', C('up_exts'));
        $save_path=C('up_path');
        $allow_max && $this->max_size = $allow_max * 1024;
        $allow_exts && $this->allow_exts = $allow_exts;
        $save_path && $this->save_path=$save_path;
    }

    public function upload()
    {
    	import("@.ORG.UploadFile");
        $upload = new UploadFile();
        $upload->maxSize = $this->max_size;
        $upload->allowExts = $this->allow_exts;
        $upload->savePath = $this->save_path;
        $upload->autoSub=true;
        $upload->subType='date';
        $upload->dateFormat ="Y/m/d";
        $thumb=$this->_get("thumb");
		if($thumb){
			$thumb=explode(",",$thumb);
			$width=intval($thumb[0]);
			$height=intval($thumb[1]);
			$upload->thumb = true;
			$upload->thumbMaxWidth = $width;
			$upload->thumbMaxHeight = $height;
			
		}
		
        if ($result = $upload->uploadOne($_FILES["imgFile"])) {
        	$model = D('Image');
        	$model->add($result[0]);
        	$this->ajaxReturn(array(
                'error' => 0,
            	'msg'=>"upload succed!",
                "url"=>$this->get_urls($result[0])
            ));
        } else {
            $this->ajaxReturn(array(
                'error' => 1,
                'msg' => $upload->getErrorMsg()
            ));
        }
    }
    
    private function get_urls($info){
    	return substr($info["savepath"], 1)."/".($this->_get("thumb") ? 'thumb_':'').$info["savename"]."?hash=".$info["hash"];
    }
}
