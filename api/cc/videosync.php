<?php 
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
require_once dirname(__FILE__) . '/spark_config.php';
require_once dirname(__FILE__) . '/storage.php';
require_once dirname(__FILE__) . '/class/spark_function.php';
?><!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $spark_config['charset']?>">
<title>API PHP demo 同步视频</title>
</head>
<body><?php
/**
 * 功能：视频信息数据获取接口示例
 * 版本：1.3
 * 日期：2012-1-18
 * 作者：Eachcan, Kelystor
 */

$videos = array();
$storage = new storage();
$videoid_from = $_GET["videoidFrom"];
$videoid_to = $_GET['videoidTo'];
$page_size = 30;
$page = 1;

function sync($page, $page_size, $videoid_from, $videoid_to) {
	global $spark_config, $videos;
	$info['userid'] = $spark_config['user_id'];
	$info['videoid_from'] = $videoid_from;
	$info['videoid_to'] = $videoid_to;
	$info['num_per_page'] = $page_size;
	$info['page'] = $page;
	
	$time = time();
	$key = $spark_config['key'];
	
	$url = $spark_config['api_videos'] . '?' . spark_function::get_hashed_query_string($info, $time, $key);
	echo "第 $page 页视频操作 <br />triggerURL: <a href='$url' target='blank'>$url</a>";
	
	$result_xml = spark_function::url_get_xml($url);
	
	echo " <br />.................... OK\n<br />";
	
	$result = spark_function::parse_videos_xml($result_xml);
	if (!isset($result['videos'])) {
		$temp['videos'] = current($result);
		unset($result);
		$result = $temp;
	}
	$all_page = ceil($result['videos']['total'] / $page_size);
	$all_page = max($all_page, 1);
	
	$charset = charset::instance('utf8', $spark_config['charset']);
	
	if (!isset($result['videos']['video'][0])) {
		$result['videos']['video'] = array($result['videos']['video']);
	}
	foreach (array_keys($result['videos']['video']) as $key) {
		$result['videos']['video'][$key]['title'] = $charset->Convert($result['videos']['video'][$key]['title']);
		$result['videos']['video'][$key]['desp'] = $charset->Convert($result['videos']['video'][$key]['desp']);
		$result['videos']['video'][$key]['tags'] = $charset->Convert($result['videos']['video'][$key]['tags']);
		$result['videos']['video'][$key]['status'] = 'OK';
	}
	$videos = array_merge($videos, $result['videos']['video']);
	
	if ($page < $all_page) {
		$page++;
		sync($page, $page_size, $videoid_from, $videoid_to);
	}
}

sync($page, $page_size, $videoid_from, $videoid_to);
$storage->save_videos($videos);
?><a href='videolist.php'>返回视频同步列表</a> 
</body>
</html>
