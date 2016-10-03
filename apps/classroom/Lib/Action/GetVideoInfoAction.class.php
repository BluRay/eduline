<?php 
class GetVideoInfoAction extends CommonAction {
	public function index(){
			file_put_contents(SITE_PATH.'/_runtime/test3.txt', var_export($_REQUEST, true));
			require_once SITE_PATH.'/api/cc/spark_config.php';
			require_once SITE_PATH.'/api/cc/class/spark_function.php';
			require_once SITE_PATH.'/api/cc/storage.php';
			require_once SITE_PATH.'/api/cc/cls_mysql.php';
			$data['video_id'] = $_GET['video_id'];
			$data['video_duration'] = $_GET['duration'];
			$data['ctime'] = $_GET['time'];
			$data['video_img'] = $_GET['image'];
			$data['hash'] = $_GET['hash'];
			$qs_hash = spark_function::get_info_hash($info, $_GET['time'], $spark_config['key']);
			$data['qs_hash'] = $qs_hash;
			file_put_contents(SITE_PATH.'/_runtime/test123.txt', var_export($data, true));
	$content = <<<OT
<?xml version="1.0" encoding="UTF-8"?> 
<result>$result</result>
OT;
header('Content-Type:text/xml');
	echo $content;
	}
}
