<?php
//获取应用配置
function event_getConfig($key=NULL){
	$config = model('Xdata')->lget('event');
	$config['limitpage']    || $config['limitpage'] =10;
	$config['canCreate']===0 || $config['canCreat']=1;
    ($config['credit'] > 0   || '0' === $config['credit']) || $config['credit']=100;
    $config['credit_type']  || $config['credit_type'] ='experience';
	($config['limittime']   || $config['limittime']==='0') || $config['limittime']=10;//换算为秒

	if($key){
		return $config[$key];
	}else{
		return $config;
	}
}



//根据存储路径，获取图片真实URL
function event_get_photo_url($savepath) {
	return DATA_PATH . '/uploads/'.$savepath;
}

/**
 * getEventShort 
 * 去除标签，截取blog的长度
 * @param mixed $content 
 * @param mixed $length 
 * @access public
 * @return void
 */
function getEventShort($content,$length = 40) {
	$content	=	stripslashes($content);
	$content	=	strip_tags($content);
	$content	=	getShort($content,$length);
	return $content;
}
