<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
require_once dirname(__FILE__) . '/spark_config.php';
require_once dirname(__FILE__) . '/class/spark_function.php';
$time = time();
$info = array();
$info['videoid'] = $_GET['videoid'];
$key = $spark_config['key'];
$url = $spark_config['api_deletevideo'] . '?' . spark_function::get_hashed_query_string($info, $time, $key);
$result_xml = spark_function::url_get_xml($url);
$result = spark_function::parse_videos_xml($result_xml);

if ($result[""][0] == 'OK') {
	$conn = @mysql_connect("localhost","gaojiao","xsw2XSW@");
	if (!$conn){
    die("连接数据库失败：" . mysql_error());
	}
	mysql_select_db("gaojiao", $conn);
	mysql_query("set names 'utf8'");
	$sql = "delete from ts_zy_video_cc where video_id='".$info['videoid']."'";
	mysql_query($sql,$conn);

	echo json_encode(array('status'=>'1'));
} else {
	echo json_encode(array('status'=>'0','info'=>'后台正在处理或者视频已经被删除'));
}
?>
 
