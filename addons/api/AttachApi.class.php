<?php
/**
 * 附件api
 * utime : 2016-03-06
 */
class AttachApi extends Api {
	/*
	 * 附件上传
	 * */
	public function upload(){
        $this->data;
         //附件所属类型
		$data['attach_type'] = t($_REQUEST['attach_type']);
		//上传方式
        $data['upload_type'] = $_REQUEST['upload_type']?t($_REQUEST['upload_type']):'file';
         //是否启用缩略图
        $thumb  = intval($_REQUEST['thumb']);
        //缩略图宽度
        $width  = intval($_REQUEST['width']);
        //图片高度
        $height = intval($_REQUEST['height']);
       //裁剪
        $cut    = intval($_REQUEST['cut']);
        $option['attach_type'] = $data['attach_type'];
        $info = model('Attach')->upload($data, $option,$thumb);
		//判断上传状态
    	if($info['status']){
    		$data = $info['info'][0];
            if($thumb==1){
                $data['src'] = getImageUrl($data['save_path'].$data['save_name'],$width,$height,$cut);
            }else{
                $data['src'] = getImageUrl($data['save_path'].$data['save_name']);
            }
    		$data['extension']  = strtolower($data['extension']);
    		$data['uid']        = $this->mid;
    		$data['row_id']     = intval($data['row_id']);
    		$data['attach_id']  = 0;
    		$id=M('Attach')->add($data);
    		$this->exitJson($data,0,"上传成功");
    	}else{
    		$this->exitJson( array() ,40028,$info['info']);
    	}
	}
	
	/**
	 * 下载接口
	 */
	public function down() {

		$aid	= intval($_GET['attach_id']);
		$attach	= model('Attach')->getAttachById($aid);
		if(!$attach){
			$this->exitJson( array() ,40023,L('PUBLIC_ATTACH_IS array() '));
		}
        $filename = $attach['save_path'].$attach['save_name'];
        $realname = auto_charset ( $attach['name'], "UTF-8", 'GBK//IGNORE');
		//下载函数
		tsload(ADDON_PATH.'/library/Http.class.php');
        //从云端下载
        $cloud = model('CloudAttach');
        if($cloud->isOpen()){
            $url = $cloud->getFileUrl($filename);
            redirect($url);
        //从本地下载
        }else{
    		if(is_file(UPLOAD_PATH.'/'.$filename)) {
    	       //清除缓存	
    			ob_clean();
    			Http::download(UPLOAD_PATH.'/'.$filename, $realname);
    		}else{
    			$this->exitJson( array() ,40025,L('PUBLIC_ATTACH_IS array() '));
    		}
        }	
	}
}
