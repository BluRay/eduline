<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
require_once dirname(__FILE__) . '/spark_config.php';
require_once dirname(__FILE__) . '/class/spark_function.php';
$time = time();
$info = array(); 
$info['videoid'] = $_GET['videoid'];
if($_GET['title']) {
	$info['title'] = $_GET['title'];
}
if($_GET['tag']) {
	$info['tag'] = trim($_GET['tag']);
}
if($_GET['description']) {
	$info['description'] = $_GET['description'];
}
if($_GET['categoryid']) {
	$info['categoryid'] = $_GET['categoryid'];
}
if($_GET['imageindex']) {
	$info['imageindex'] = $_GET['imageindex'];
}
$key = $spark_config['key'];
$url = $spark_config['api_editvideo'] . '?' . spark_function::get_hashed_query_string($info, $time, $key);
$result_xml = spark_function::url_get_xml($url);
$result = spark_function::parse_videos_xml($result_xml);
if ($result['video']['id']) {
	$conn = @mysql_connect("localhost","root","");
	if (!$conn){
    die("连接数据库失败：" . mysql_error());
	}
	mysql_select_db("thinksns", $conn);
	mysql_query("set names 'utf8'");
	$sql = "select imgurl from ts_g_video where videoid= '".$info['videoid']."'";
	$img_old = mysql_query($sql,$conn);
	$img_old = explode($info['videoid'],mysql_fetch_array($img_old)[0])[0];
	$img_new = $img_old.$info['videoid'].'-0/'.$info['imageindex'].'.jpg';
	$sql = "update ts_g_video set imgurl='".$img_new."' where videoid='".$info['videoid']."'";
	mysql_query($sql,$conn);
	echo "<div style='color:red;'>编辑成功!</div><br/><br/>";
} else {
	echo "编辑失败!";
}