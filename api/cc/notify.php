<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
ob_start();
require_once 'spark_config.php';
require_once 'class/spark_function.php';
require_once 'storage.php';
require_once 'cls_mysql.php';
// 注意：回调接口需判断数据来源是否有效


$info = array ();
//判断回调视频的状态，对回调参数进行验证
if ($_GET['status'] == 'OK') {
	$info = array (
		'videoid' => $_GET['videoid'],
		'status' => $_GET['status'],
		'duration' => $_GET['duration'],
		'image' => $_GET['image'],
		
	);
} else {
	$info = array (
		'videoid' => $_GET['videoid'],
		'status' => $_GET['status'],
	);
}
$qs_hash = spark_function::get_info_hash($info, $_GET['time'], $spark_config['key']);
$info['qs_hash'] = $qs_hash;
$info['hash'] = $_GET['hash'];
$result = 'FAIL';
if ($qs_hash == $_GET['hash']) {	// 通过对hash值的判断，来确定数据来源的有效性
	
	$data['video_id'] = $_GET['videoid'];
	$data['video_img'] = $_GET['image'];
	$data['video_duration'] = $_GET['duration'];
	$data['ctime'] = $_GET['time'];
	$db = new cls_mysql("42.121.113.32",'gaojiao','xsw2XSW@', 'gaojiao', 'UTF8');
	if($db->autoExecute('ts_zy_video_cc',$data)){
		$result = 'OK';
	}
}
ob_clean();
$content = <<<OT
<?xml version="1.0" encoding="UTF-8"?>
<result>$result</result>
OT;
header('Content-Type:text/xml');
echo $content;
